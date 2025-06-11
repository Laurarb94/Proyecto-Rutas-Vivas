<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserFollow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    //Búsqueda de acompañante a la actividad
    public function findBySearchQuery(string $query)
    {
        return $this->createQueryBuilder('u')
          ->where('u.userName LIKE :query OR u.email LIKE :query')
          ->setParameter('query', '%' . $query . '%')
          ->getQuery()
            ->getResult();
    }

    //Búsqueda de todos los usuarios para ver sus perfiles y seguirse
    public function findAllExcept(User $user): array
    {
        return $this->createQueryBuilder('u')
           ->where('u != :me')
           ->setParameter('me', $user)
           ->getQuery()
           ->getResult();
    }

    public function findVisibleUsersFor(User $currentUser): array
    {
        $qb = $this->createQueryBuilder('u');

        $qb->leftJoin(UserFollow::class, 'f', 'WITH', 'f.followed = u AND f.follower = :currentUser AND f.status = :accepted')
           ->where('u != :currentUser') //Para que el usuario actual no salga en la lista de usuarios
           ->andWhere('u.isPrivate = false OR f.id IS NOT NULL')
           ->setParameter('currentUser', $currentUser)
           ->setParameter('accepted', UserFollow::STATUS_ACCEPTED);

        return $qb->getQuery()->getResult();
    }







//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
