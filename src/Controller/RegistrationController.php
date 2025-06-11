<?php

namespace App\Controller;

use App\Entity\Localities;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Repository\LocalitiesRepository;
use App\Repository\PostalCodeRepository;
use Doctrine\ORM\EntityManagerInterface;

use Proxies\__CG__\App\Entity\Provinces;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

final class RegistrationController extends AbstractController{

    private $params;
    private LocalitiesRepository $localitiesRepository;
    private PostalCodeRepository $postalCodeRepository;

    public function __construct(ParameterBagInterface $params, private Security $security, private LoggerInterface $logger, LocalitiesRepository $localitiesRepository, PostalCodeRepository $postalCodeRepository)
    {
        $this->params = $params;
        $this->logger = $logger;
        $this->localitiesRepository = $localitiesRepository;
        $this->postalCodeRepository = $postalCodeRepository;
    }


//Métodos de registro SIN CLERK

    #[Route('/registration', name: 'app_registration')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ParameterBagInterface $params, MailerInterface $mailer): Response
    {

        $user = new User();

        $adminEmails = $params->get('admin_emails');
        $vipEmails = $params->get('vip_emails');

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form->get('plainPassword')->getData();

            //Encriptar la contraseña
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            //Manejar foto si el usuario la sube
            $file = $form->get('photo')->getData();
            if($file){
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/profile_photos', $fileName);
                $user->setPhoto($fileName);
            }

            //Asignar roles
            if(in_array($user->getEmail(), $adminEmails)){
                $user->setRoles(['ROLE_ADMIN']);
            }elseif(in_array($user->getEmail(), $vipEmails)){
                $user->setRoles(['ROLE_VIP']);
            }else{
                $user->setRoles(['ROLE_USER']);
            }

            //Asignar provincia y localidad
            $province = $form->get('province')->getData();
            if($province instanceof Provinces){
                $user->setProvince($province);
            }

            $locality = $form->get('locality')->getData();
            if($locality instanceof Localities){
                $user->setLocality($locality);
            }

            //Generar token único y guardar en bbdd
            $user->setActivationToken(Uuid::v4()->toRfc4122());

            //Persistir usuario
            $entityManager->persist($user);
            $entityManager->flush();

            //Variable del .env, que viene de service.yalm
            $mailerForm = $this->getParameter('mailer_from');

            //Correo de activación
            $email = (new Email())
                ->from($mailerForm)
                ->to($user->getEmail())
                ->subject('Activa tu cuenta')
                ->html('<h2>Bienvenido a rutas vivas!</h2>
                <p>Estás a un paso de activar tu cuenta.</p>
                <p>Por favor, para activar tu cuenta haz clic en: <a href="http://actividades.ayar.es/activate/' . $user->getActivationToken() . '">Activar cuenta</a></p>');

            // En local: <p>Por favor, para activar tu cuenta haz clic en: <a href="http://127.0.0.1:8000/activate/' . $user->getActivationToken() . '">Activar cuenta</a></p>');
            //En produccion: <p>Por favor, para activar tu cuenta haz clic en: <a href="http://actividades.ayar.es/activate/' . $user->getActivationToken() . '">Activar cuenta</a></p>');

            try {
                $mailer->send($email);
                //dump('Correo enviado'); die(); El correo se envía, aunque no llega. Pero se envía, el problema está en mailtrap
            } catch (\Exception $e) {
                $this->logger->error('Error al enviar el correo de activación: ' . $e->getMessage());
            }

            //Redirigir a página informativa
            return $this->redirectToRoute('app_waiting_activation', [
                'token' => $user->getActivationToken(),
            ]);

        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/waiting-activation/{token}', name: 'app_waiting_activation')]
    public function waitingActivation(string $token, UserRepository $userRepository): Response 
    {
        $user = $userRepository->findOneBy(['activationToken'=>$token]);

        if(!$user){
            $this->addFlash('error', 'Token de activación no válido');
            return $this->redirectToRoute('app_home');
        }

        /*
        //Local:
        return $this->render('registration/waiting_activation.html.twig', [
            'activationLink'=> 'http://127.0.0.1:8000/activate/' . $user->getActivationToken(),
        ]);
        */

        //Producción:
        return $this->render('registration/waiting_activation.html.twig', [
            'activationLink'=>'http://actividades.ayar.es/activate/'. $user->getActivationToken(),
            ]);
        
        
    }

    //Método importante!!! Cuando te registras, te lleva a la página de waiting-activation (método anterior), pero aquí tienes que activar el token!!
    #[Route('/activate/{token}', name: 'app_activate')]
    public function activate(string $token, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->findOneBy(['activationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token de activación no válido.');
            return $this->redirectToRoute('app_home');
        }

        // Marcar como activado
        $user->setIsVerified(true);
        $user->setActivationToken(null); // Para que no se reutilice el token
        $em->flush();

        $this->addFlash('success', 'Tu cuenta ha sido activada correctamente.');

        return $this->redirectToRoute('app_login');
    }

    //Endpoint que maneja la solicitud ajax para las localidades y códigos postales 
    #[Route('/get-localities/{provincesId}', name: 'get_localities_by_province', methods: ['GET'])]
    public function getLocalitiesByProvince($provincesId, EntityManagerInterface $em): JsonResponse
    {
        // Obtener la provincia por su ID
        $province = $em->getRepository(Provinces::class)->find($provincesId);
        
        if (!$province) {
            return new JsonResponse(['error' => 'Provincia no encontrada'], 404);
        }
        
        // Obtener las localidades relacionadas con la provincia
        $localities = $province->getLocalities();
        
        if ($localities->isEmpty()) {
            return new JsonResponse(['error' => 'No se han encontrado localidades para esta provincia'], 404);
        }
            
        // Preparar la respuesta para localidades
        $localitiesData = array_map(function($locality) {
            return [
                'id' => $locality->getId(),
                'name' => $locality->getName(),
            ];
        }, $localities->toArray());
        
        // Preparar la respuesta para los códigos postales
        return new JsonResponse(['localities' => $localitiesData]);
    }

    /*
    //REGISTRO CON CLERK. Clerk te registra, pero luego hay que incluir más datos!!! Esto no lo usas, estaba comentado
    #[Route('/registration/complete', name: 'app_complete_registration', methods: ['GET', 'POST'])]
    public function completeRegistration(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if(!$user instanceof \App\Entity\User) throw new \LogicException('El usuario no es válido');

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'is_clerk' => true
        ]);
        $this->logger->info('Método: ' . $request->getMethod());
        $this->logger->info('Request data: ' . json_encode($request->request->all()));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->logger->info('formulario enviado');
        } else {
            $this->logger->warning('Formulario NO enviado');
        }


        if($form->isSubmitted()) $this->logger->info('formulario enviado');

        if($form->isSubmitted() && $form->isValid()){
            $this->logger->info('Formulario válido');
            $photoFile = $form->get('photo')->getData();

            if($photoFile){
                $fileName = md5(uniqid()) . '.' . $photoFile->guessExtension();
                $photoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/profile_photos', $fileName);
                $user->setPhoto($fileName);
            }

            //Marcar perfil como completado
            $user->setIsProfileCompleted(true);

            $em->persist($user);
            $em->flush(); 
            
            $this->addFlash('success', 'Perfil actualizado correctamente');

            return $this->redirectToRoute('app_user_view__profile');
        }

        return $this->render('registration/complete.html.twig', [
            'registrationForm' => $form
        ]);
    }

    // Endpoint que devuelve si el usuario necesita completar o no su registro, para redirigir a completar o al perfil 
    #[Route('/api/check-user-status', name: 'api_check_user_status')]
    public function checkUserStatus(UserInterface $user): JsonResponse
    {
        $user = $this->getUser();
        if(!$user instanceof \App\Entity\User) throw new \LogicException('El usuario no es válido');

       // $needsCompletion = !$user->getPhone() || !$user->getSecondLastName();

        return $this->json([
            'needsCompletion' => !$user->isProfileCompleted()
        ]);
    }

    */






} //fin controller