<?php

namespace App\Controller;

use App\Repository\UserNotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationPartialController extends AbstractController
{
    #[Route('/user/partials/notification-icon', name: 'user_notification_icon')]
    public function notificationIcon(UserNotificationRepository $repo): Response
    {
        $user = $this->getUser();

        $notifications = $repo->findBy(['user' => $user]);
    return $this->render('partials/_notification_icon.html.twig', [
        'notifications' => $notifications,
    ]);
    }
}

//Acuérdate!! Creas este controlador porque quieres que la campanita de notificaciones salga en el menú del usuario, que está en base_usuario. 
//Como lo primero que se carga es base_usuario no tendrá la variable unreadCount y dará error en todos lados, así que: creas el controlador y el partial
//en html y ya puedes pasar esa variable sin que lance errores
