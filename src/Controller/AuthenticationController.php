<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\NewPasswordType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


final class AuthenticationController extends AbstractController{

    /*
    //Login con clerk
    #[Route('/login', name: 'app_login')]
    public function logIn(ParameterBagInterface $params): Response
    {
        $clearkKey = $params->get('clerk_publishable_key');

        return $this->render('authentication/clerk_login.html.twig', [
            'clerk_key' => $clearkKey,
        ]);
    }

    //Cambio de contraseña con clerk
    #[Route('/account', name: 'user_account')]
    public function account(ParameterBagInterface $params): Response
    {
        $clearkKey = $params->get('clerk_publishable_key');

        return $this->render('authentication/account.html.twig', [
           'clerk_key' => $clearkKey,
        ]);
    }
        */


    //Login sin clerk
    #[Route('/login', name: 'app_login')]
    public function logIn(AuthenticationUtils $authenticationUtils): Response
    {
        // Te da el último error de login lo ha habido
        $error = $authenticationUtils->getLastAuthenticationError();

        // devuelve el último nombre de usuario que se ha intentado usar
        $lastUsername = $authenticationUtils->getLastUsername();

        // Mensaje personalizado para el error
        $errorMessage = null;
        if ($error) {
            $errorMessage = 'Usuario o contraseña incorrectos. Por favor, inténtalo de nuevo.';
        }

        return $this->render('authentication/login.html.twig', [
            'last_username' => $lastUsername,
            'error_message' => $errorMessage,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logOut(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    //CAMBIO DE CONTRASEÑA SIN CLERK:
    //Método para solicitar el cambio de contraseña
    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                // Si el usuario no existe, puedes mostrar un mensaje (pero no revelar si el correo es incorrecto)
                $this->addFlash('error', 'Si este correo está registrado, te enviaremos un enlace de recuperación.');
                return $this->redirectToRoute('forgot_password');
            }

            // Generar un token único y guardar en la base de datos
            $resetToken = Uuid::v4()->toRfc4122();
            $user->setResetToken($resetToken);
            $user->setTokenExpiration(new \DateTime('+1 hour')); // Establecer la expiración a 1 hora (ajusta el tiempo según sea necesario)

            $em->flush();

            //Variable del .env, que viene de service.yalm
            $mailerForm = $this->getParameter('mailer_from');

            // Enviar el correo de restablecimiento
            $email = (new Email())
                ->from($mailerForm)
                ->to($user->getEmail())
                ->subject('Restablecer contraseña')
                ->html(
                    '<p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
                    <p><a href="' . $this->generateUrl('reset_password',
                    ['token' => $user->getResetToken()], UrlGeneratorInterface::ABSOLUTE_URL) . '">Restablecer contraseña</a></p>'
                );

            try {
                $mailer->send($email);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Hubo un problema al enviar el correo de restablecimiento.');
            }

            $this->addFlash('success', 'Te hemos enviado un enlace para restablecer tu contraseña.');
            return $this->redirectToRoute('forgot_password');
        }

        return $this->render('authentication/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //SIN CLERK
    //Método para que el usuario pueda ingresar la nueva contraseña en el mail que envía el método anterio
    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(string $token, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken'=>$token]);

        if (!$user || $user->getTokenExpiration() < new \DateTime()) {
            $this->addFlash('error', 'El enlace de recuperación ha expirado o no es válido.');
            return $this->redirectToRoute('app_login');
        }

        //crear el formulario para la nueva contraseña:
        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setTokenExpiration(null);

            $em->flush();

            $this->addFlash('success', 'Tu contraseña se ha restablecido con éxito');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('authentication/reset_password.html.twig', [
            'form'=>$form->createView(),
        ]);

    }
    


}//Fin controller
