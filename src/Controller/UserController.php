<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFollow;
use App\Repository\UserFollowRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController{
    #[Route('/principal', name: 'app_principal')]
    public function principal(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/users/explorer', name: 'user_explorer')]
    public function exploreUsers(UserRepository $userRepo, UserFollowRepository $followRepo): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User) throw $this->createAccessDeniedException();

        //No mostrarte a ti mismo
        $users = $userRepo->findAllExcept(($currentUser));

        $follows = $followRepo->findBy(['follower' => $currentUser]);
        
        $acceptedFollowedIds = array_map(
            fn(UserFollow $f) => $f->getFollowed()->getId(),
            array_filter($follows, fn($f) => $f->getStatus() === UserFollow::STATUS_ACCEPTED)
        );
    
        $pendingFollowedIds = array_map(
            fn(UserFollow $f) => $f->getFollowed()->getId(),
            array_filter($follows, fn($f) => $f->getStatus() === UserFollow::STATUS_PENDING)
        );

        //buscar todas las solicitudes que el usuario A ha recibido pero no ha aceptado
        $pendingReceived = $followRepo->findBy([
            'followed' => $currentUser,
            'status' => UserFollow::STATUS_PENDING
        ]);

        //Extraer los ids de los usuarios que han enviado esas solicitudes al usuario A. Para que el buscar usuarios
        //si ya ha silicitado seguirte salga "te ha enviado una solicitud de seguimiento"
        $pendingReceivedFollowerIds = array_map(
            fn(UserFollow $f) => $f->getFollower()->getId(),
            $pendingReceived
        );
    
        $followerIds = array_map(
            fn(UserFollow $f) => $f->getFollowed()->getId(),
            $currentUser->getFollows()->toArray()
        );

        
        //Filtrar para que no te enseñe los usuarios que son amigos
        $excludedIds = array_merge(
            [$currentUser->getId()],
            $acceptedFollowedIds,
            $pendingFollowedIds,
            $pendingReceivedFollowerIds
        );

        $users = array_filter($users, function (User $u) use ($excludedIds){
            return !in_array($u->getId(), $excludedIds);
        });



        return $this->render('user/explorer.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser,
            'followerIds' => $followerIds,
            'acceptedFollowedIds' => $acceptedFollowedIds,
            'pendingFollowedIds' => $pendingFollowedIds,
            'pendingReceivedFollowerIds' => $pendingReceivedFollowerIds,
            'excludedIds' => $excludedIds,
        ]);
    }

    //Método para que el usuario pueda ver a "amigos"
    #[Route('/users/friends', name: 'user_friends')]
    public function viewAcceptedFriends(UserFollowRepository $followRepo): Response
    {
        $currentUser = $this->getUser();

        if(!$currentUser instanceof User) throw $this->createAccessDeniedException();

        //Buscar relcaciones que ha iniciado el usuario actual y han sido aceptadas:
        $acceptedFollows = $followRepo->findBy([
            'follower' => $currentUser,
            'status' => UserFollow::STATUS_ACCEPTED
        ]);

        /*
        //Extrarer usuarios que el usuario actual está siguiendo y han sido aceptadps
        $friends = array_map(
            fn(UserFollow $f) => $f->getFollowed(),
            $acceptedFollows
        );
        */

        $friendsData = [];

        foreach($acceptedFollows as $follow){
            $otherUser = $follow->getFollowed();

            //Ver si el otro usuario también te sigue
            $reverseFollow = $followRepo->findOneBy([
                'follower' => $otherUser, 
                'followed' => $currentUser,
                'status' => UserFollow::STATUS_ACCEPTED
            ]);

            $friendsData[] = [
                'user' => $otherUser,
                'isMutual' => $reverseFollow !== null
            ];
        }

        return $this->render('user/friends.html.twig', [
            'friends' => $friendsData,
        ]);
    }



    
}
