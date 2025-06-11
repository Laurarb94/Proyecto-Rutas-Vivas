<?php

namespace App\Controller;

use App\Entity\CategoryActivity;
use App\Entity\User;
use App\Entity\PersonalActivities;
use App\Entity\UserFollow;
use App\Form\UserProfileType;
use App\Form\ChangePasswordType;
use App\Repository\CategoryActivityRepository;
use App\Repository\UserFollowRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;



final class UserProfileController extends AbstractController
{

    private $em;

    public function  __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    

    #[Route('/user/view_profile', name: 'app_user_view__profile')]
    public function profile(EntityManagerInterface $em, CategoryActivityRepository $categoryReposiotry): Response
    {
        $user = $this->getUser();

        //verificar que el usuario esé autenticado
        $this->denyAccessUnlessGranted('ROLE_USER');

        if(!$user instanceof \App\Entity\User) throw new \LogicException('El usuario no es válido');

        if (!$user->isProfileCompleted()) {
            $link = $this->generateUrl('edit_profile');
            $this->addFlash('warning', "Tu perfil está incompleto. <a href='$link'>Haz click aquí para completarlo</a> ");
        }
        
        $allActivities = $em->getRepository(PersonalActivities::class)->findBy(['user' => $user]);

        //Filtras si la activdad está o no está completada. fn es una función de filtrado, que toma el $a que represneta cada actividad del array
        $completedActivities = array_filter($allActivities, fn($a) => $a->isCompleted());
        $pendingActivities = array_filter($allActivities, fn($a) => !$a->isCompleted());

        $categories = $categoryReposiotry->findAll(); //obtener todas las categoróias para mostrarlas en el peril  

        //Haces un array que vincule cada categoría con un icono para mostrarla en la vista y que cada categoría tenga un icono diferente!
        $categoryIcons = [
            'aventura' => 'fa-tree',
            'bicicross' => 'fa-bicycle',
            'carreras' => 'fa-running',
            'ciclismo' => 'fa-bicycle',
            'deportes_acuaticos' => 'fa-water',
            'deportes_aereos' => 'fa-plane',
            'deportes_urbanos' => 'fa-city',
            'equitacion' => 'fa-horse',
            'escalada' => 'fa-hiking',
            'escapadas_activas' => 'fa-campground',
            'esqui_y_nieve' => 'fa-snowflake',
            'montanismo' => 'fa-mountain',
            'natacion' => 'fa-swimmer',
            'nauticos_y_navegacion' => 'fa-ship',
            'patinaje' => 'fa-skating',
            'puenting' => 'fa-road',
            'senderismo' => 'fa-walking',
            'skate' => 'fa-skating',
            'tiro_y_precision' => 'fa-bullseye',
        ];        

        //dump($categories); die;
        
        return $this->render('user_profile/view_profile.html.twig', [
            'user' => $user,
            'completedActivities' => $completedActivities,
            'pendingActivities' => $pendingActivities,
            'categories' => $categories,
            'categoryIcons' => $categoryIcons,
        ]);
    }

    #[Route('/user/edit/profile', name: 'edit_profile')]
    public function edit_profile(Request $request, EntityManagerInterface $em): Response
    {
        //primero obtener al usuario actual
        $user = $this->getUser();

        if (!$user instanceof User) throw $this->createNotFoundException('El usuario no está autenticado correctamente');

        // crear y manejar el form con los datos del user
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        //manejar foto si sube una nueva sin clerk
        $file = $form->get('photo')->getData();
        if ($file) {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('kernel.project_dir') . '/public/uploads/profile_photos', $fileName);
            $user->setPhoto($fileName);
        }
        
        $locality = $user->getLocality(); 
            if (!$locality) { 
                $user->setLocality(null);
            }

        //persistir cambios en la base de datos
        $em->flush();

        $this->addFlash('success', 'Tus datos se han actualizado correctamente');
        return $this->redirectToRoute('edit_profile');
    }

    // Renderiza la vista del formulario
    return $this->render('user_profile/edit_profile.html.twig', [
        'form' => $form->createView(),
    ]);
    }

    //Método para cambiar la contraseña sin clerk:
    #[Route('/user/edit/password', name: 'edit_password')]
    public function editPassword(Request $request, EntityManagerInterface $em, userPasswordHasherInterface $userPassword){

        $user = $this->getUser();

        if(!$user instanceof User){
            throw $this->createNotFoundException('El usuario no está autenticado correctamente');
        }

        //Crear el formulario que cambia la contraseña
        $formPassword = $this->createForm(ChangePasswordType::class, $user);
        $formPassword->handleRequest($request);

        if($formPassword->isSubmitted() && $formPassword->isValid()){
            //Valida la contraseña actual:
            $currentPassword = $formPassword->get('currentPassword')->getData();
            $newPassword = $formPassword->get('newPassword')->getData();
            $confirmPassword = $formPassword->get('confirmPassword')->getData();

            if(!$userPassword->isPasswordValid($user, $currentPassword)){
                $this->addFlash('error', 'La contraseña actual no es válida');
            }elseif($newPassword !== $confirmPassword){
                $this->addFlash('error', 'Las contraseñas no coinciden');
            }else{
                $newPasswordHash = $userPassword->hashPassword($user, $newPassword);
                $user->setPassword($newPasswordHash); //aquí actualizas la contraseña
                $this->em->flush();
                $this->addFlash('success', 'Tu contraseña se ha actualizado correctamente');

                return $this->redirectToRoute('app_user_view__profile');
            }
        }

        return $this->render('user_profile/edit_password.html.twig', ['formPassword'  => $formPassword->createView()]);

    } //cierre método cambio contraseña 

    //-----------Método para acompañante
    #[Route('/user/search', name: 'user_search', methods: ['GET'])]
    public function searchUser(Request $request, UserRepository $userRepository): JsonResponse
    {
        $query = $request->query->get('query'); // Lo que el usuario ha escrito
        $users = $userRepository->findBySearchQuery($query); // Método que hay que agregar en el repositorio de usuarios

        $userResults = [];
        foreach ($users as $user) {
            $userResults[] = [
                'id' => $user->getId(),
                'userName' => $user->getUserName(),
                'email' => $user->getEmail(),
            ];
        }

        return new JsonResponse($userResults);
    }

    //---------------Mostrar iconos con las categorías en el perfil del usuario---------------------------
    #[Route('/personalActivity/categoria/{id}', name: 'show_subcategories')]
    public function showSubcagtegories(CategoryActivity $category): Response
    {
        return $this->render('PersonalActivity/subcategories.html.twig', [
            'category' => $category,
            'subcategories' => $category->getSubcategories()
        ]);
    }

    //---------------------------Ver el perfil de otros usuarios es privado o público-----------------------
    #[Route('/user/profile/{id}', name: 'user_view_other_profile')]
    public function viewProfile(User $user, UserFollowRepository $followRepo, EntityManagerInterface $em, CategoryActivityRepository $categoryReposiotry): Response
    {
        
        $currentUser = $this->getUser();

        $follow = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $user
        ]);
        
        //con esto eliges qué se puede ver de cada perfil para pasarlo a la vista, si es privado sólo foto si es públic más info.
        $canSeeAll = !$user->isPrivate() 
            || $user === $currentUser 
            || ($follow && $follow->getStatus() === UserFollow::STATUS_ACCEPTED);
        
        //Pasas las actividades para que aparezcan en el perfil
        $completedActivities = [];
        $categories = [];
        $categoryIcons = [];

        if($canSeeAll){
            $allActivities = $em->getRepository(PersonalActivities::class)->findBy(['user' => $user]);
            $completedActivities = array_filter($allActivities, fn($a) => $a->isCompleted());
            $categories = $categoryReposiotry->findAll();

            $categoryIcons = [
                'aventura' => 'fa-tree',
                'bicicross' => 'fa-bicycle',
                'carreras' => 'fa-running',
                'ciclismo' => 'fa-bicycle',
                'deportes_acuaticos' => 'fa-water',
                'deportes_aereos' => 'fa-plane',
                'deportes_urbanos' => 'fa-city',
                'equitacion' => 'fa-horse',
                'escalada' => 'fa-hiking',
                'escapadas_activas' => 'fa-campground',
                'esqui_y_nieve' => 'fa-snowflake',
                'montanismo' => 'fa-mountain',
                'natacion' => 'fa-swimmer',
                'nauticos_y_navegacion' => 'fa-ship',
                'patinaje' => 'fa-skating',
                'puenting' => 'fa-road',
                'senderismo' => 'fa-walking',
                'skate' => 'fa-skating',
                'tiro_y_precision' => 'fa-bullseye',
            ];        
        }
        
        return $this->render('user_profile/view_other_profile.html.twig', [
            'user' => $user,
            'canSeeAll' => $canSeeAll,
            'follow' => $follow,
            'completedActivities' => $completedActivities,
            'categories' => $categories,
            'categoryIcons' => $categoryIcons,
        ]);

    }


} //fin controller



