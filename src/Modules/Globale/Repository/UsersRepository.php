<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function getShareables($user, $query=""){
      return $this->createQueryBuilder('u')
          ->andWhere('u.company = :company')
          ->andWhere('u.active = :active')
          ->andWhere('u.deleted = :deleted')
          ->andWhere('u.id <> :user')
          ->andWhere('u.id <> :user')
          ->andWhere('u.name LIKE :query OR u.firstname LIKE :query OR u.email LIKE :query')
          ->setParameter('company', $user->getCompany())
          ->setParameter('active', true)
          ->setParameter('deleted', false)
          ->setParameter('user', $user->getId())
          ->setParameter('query', '%'.$query.'%')
          ->orderBy('u.id', 'ASC')
          ->getQuery()
          ->getResult()
      ;
    }

    // /**
    //  * @return Users[] Returns an array of Users objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
