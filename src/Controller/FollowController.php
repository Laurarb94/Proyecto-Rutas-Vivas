<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFollow;
use App\Entity\UserNotification;
use App\Repository\UserFollowRepository;
use App\Repository\UserNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FollowController extends AbstractController
{
    #[Route('/user/follow/{id}', name: 'user_follow')]
    public function follow(User $targetUser, EntityManagerInterface $em, UserFollowRepository $followRepo): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User || $currentUser === $targetUser){
            throw $this->createAccessDeniedException();
        }

        $existing = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $targetUser
        ]);

        if($existing){
            $this->addFlash('warning', 'Ya has enviado una solicitud y/o sigues a este usuario');
            return $this->redirectToRoute('app_user_view__profile');
        }

        $follow = new UserFollow();
        $follow->setFollower($currentUser);
        $follow->setFollowed($targetUser);
        $follow->setStatus($targetUser->isPrivate() ? UserFollow::STATUS_PENDING : UserFollow::STATUS_ACCEPTED);

        $em->persist($follow);

        if($targetUser->isPrivate()){
            $notif = new UserNotification();
            $notif->setUser($targetUser); //receptor de la notificación
            $notif->setInitiator($currentUser); //el que envía la notificación
            $notif->setMessage("{$currentUser->getUserName()} quiere seguirte.");
            $notif->setIsRead(false);
            $notif->setCreatedAt(new \DateTimeImmutable());
            $notif->setRelatedFollow($follow);

            $em->persist($notif);
        }

        $em->flush();

        $message = $targetUser->isPrivate()
           ? 'Solicitud de seguimiento enviada.'
           : 'Ahora sigues a este usuario';

        $this->addFlash('success', $message);

        return $this->redirectToRoute('user_explorer');
    }

    //Método para que le llegue la notificación al usuario
    #[Route('/user/notifications', name: 'user_notification')]
    public function notifications(UserNotificationRepository $notifRepo): Response
    {
        $user = $this->getUser();
       
        $notifications = $notifRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render ('notifications/list.html.twig', [
            'notifications' => $notifications,
            'user' => $user,
        ]);
    }

    //Método para aceptar la solicitud de seguimiento
    #[Route('/user/follow/accept/{id}', name: 'user_follow_accept')]
    public function acceptFollow(UserFollow $follow, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User || $follow->getFollowed() !== $currentUser){
            throw $this->createAccessDeniedException();
        }

        //verificar si ya se aceptó
        if($follow->getStatus() === UserFollow::STATUS_ACCEPTED){
            $this->addFlash('warning', 'Ya has aceptado esta solicitud.');
            return $this->redirectToRoute('user_notification');
        }

        $follow->setStatus(UserFollow::STATUS_ACCEPTED);

        //Marcar la notificación original como leída
        foreach($currentUser->getNotifications() as $notif){
            if($notif->getRelatedFollow() === $follow){
                $notif->setIsRead(true);
            }
        }

        //Nueva notificación para el que envió la solicitud
        $acceptedNotif = new UserNotification();
        $acceptedNotif->setUser($follow->getFollower()); // el que recibe la confirmación
        $acceptedNotif->setInitiator($currentUser); //el que la acepta
        $acceptedNotif->setMessage("{$currentUser->getUserName()} ha aceptado tu solicitud de seguimiento.");
        $acceptedNotif->setIsRead(false);
        $acceptedNotif->setCreatedAt(new \DateTimeImmutable());

        $em->persist($acceptedNotif);
        $em->flush();

        //Comprobar si el que acepota ya sigue al otro usuaroi
        $alreadyFollowing = $em->getRepository(UserFollow::class)->findOneBy([
            'follower' => $currentUser,
            'followed' => $follow->getFollower()
        ]);

        //Si no lo sigue, crear nueva notificación para seguir también
        if(!$alreadyFollowing){
            $notif = new UserNotification();
            $notif->setUser($currentUser); //el que aceptó la solicitud
            $notif->setInitiator($follow->getFollower()); //el que solicitó 
            $notif->setMessage("Has aceptado la solicitud de {$follow->getFollower()->getUserName()}. ¿Quieres seguirle también?");
            $notif->setIsRead(false);
            $notif->setCreatedAt(new \DateTimeImmutable());
        }

        $em->persist($notif);
        $em->flush();

        $this->addFlash('success', 'Has aceptado la solicitud');

        return $this->redirectToRoute('user_notification');
    }

    //Metodo para seguir de vuelta
    #[Route('/user/follow/reciprocate/{id}', name: 'user_follow_reciprocate')]
    public function reciprocateFollow(UserNotification $notification, EntityManagerInterface $em, UserFollowRepository $followRepo): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User || $notification->getUser() !== $currentUser){
            throw $this->createAccessDeniedException();
        }

        //Obtener al usuario que envía la solicitud original (usuario que quiere seguirte)
        $targetUser = $notification->getInitiator();
        if(!$targetUser){
            $this->addFlash('warning', 'No se puede procesar esta solicitud.');
            return $this->redirectToRoute('user_notification');
        }

        //Verificar si ya le sigue para evitar duplicados
        $existing = $followRepo->findOneBy([
            'follower' => $currentUser,
            'followed' => $targetUser
        ]);

        if($existing) {
            $this->addFlash('info', 'Ya sigues a este usuario.');
            return $this->redirectToRoute('user_notification');
        }

        //Crear relación de seguimiento (de usu B a usu A)
        $follow = new UserFollow();
        $follow->setFollower($currentUser); //usu B (el que acepta)
        $follow->setFollowed($targetUser); //usu A (el que pide ser seguido primero)

        //Verificas que A -targetUser- es privado. Si lo es se deja en estado pendiente
        $isPrivate = $targetUser->isPrivate();
        $follow->setStatus($isPrivate ? UserFollow::STATUS_PENDING : UserFollow::STATUS_ACCEPTED);

        $em->persist($follow);

        //Marcar la notificación original como leída
        $notification->setIsRead(true);

        //Si A es privado, le envías una notificación de solicitud de vuelta
        //A quiere seguir a B. B acepta, pero para que B pueda seguir a A tienes que notificarle a A otra vez porque es privado!
        if($isPrivate){
            $notifToA = new UserNotification();
            $notifToA->setUser($targetUser); //A recibe la solicityd
            $notifToA->setInitiator($currentUser); //B quiere seguri a A
            $notifToA->setMessage("{$currentUser->getUserName()} quiere seguirte. ");
            $notifToA->setIsRead(false);
            $notifToA->setCreatedAt(new \DateTimeImmutable());
            $notifToA->setRelatedFollow($follow);

            $em->persist($notifToA);
        }else{
            //Si A tiene perfil públcio, solo recibe notificación de seguimiento, ya no es necesario que acepte a B porque es público
            $notif = new UserNotification();
            $notif->setUser($targetUser); 
            $notif->setInitiator($currentUser); 
            $notif->setMessage("{$currentUser->getUserName()} ha aceptado tu solicitud y también quiere seguirte.");
            $notif->setIsRead(false);
            $notif->setCreatedAt(new \DateTimeImmutable());
            
            $em->persist($notif);
        }
   
        $em->flush();

        if(!$isPrivate) $this->addFlash('success', 'Ahora sigues a este usuario');

        return $this->redirectToRoute('user_notification');
    }


    //Método para rechazar la silicitud
    #[Route('/user/follow/reject/{id}', name: 'user_follow_reject')]
    public function rejectFollow(UserFollow $follow, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User || $follow->getFollowed() !== $currentUser){
            throw $this->createAccessDeniedException();
        }

        //Eliminar la notificación relacionada si existe
        $notifications = $em->getRepository(UserNotification::class)->findBy(['relatedFollow' => $follow]);
        foreach($notifications as $notif){
            $em->remove($notif);
        }
        
        $em->remove($follow);
        $em->flush();

        $this->addFlash('info', 'Solicitud rechazada');

        return $this->redirectToRoute('user_notification');
    }

    //Método para marcar la notificación como leída
    #[Route('/user/notification/read/{id}', name: 'notification_read')]
    public function markAsRead(UserNotification $notification, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if(!$user instanceof User || $notification->getUser() !== $user) throw $this->createAccessDeniedException();

        $notification->setIsRead(true);

        $em->persist($notification);
        $em->flush();

        return $this->json(['success' => true]);
    }

    //Método para usuarios que han enviado solicitud y aún no la han aprobado
    #[Route('/users/follow/requests/sent', name: 'user_sent_requests')]
    public function viewSentFollowRequests(UserFollowRepository $followRepo): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User) throw $this->createAccessDeniedException();

        //Buscar solicitudes pendientes enviadas por el usuario
        $pendingFollows = $followRepo->findBy([
            'follower' => $currentUser,
            'status' => UserFollow::STATUS_PENDING
        ]);

        //extrar los usuarios a los que se envió la solicitud
        $sentRequests = array_map(
            fn(UserFollow $f) => $f->getFollowed(),
            $pendingFollows
        );

        return $this->render('user/sent_requests.html.twig', [
            'sentRequests' => $sentRequests,
        ]);
    }



    



} //cierre controller


