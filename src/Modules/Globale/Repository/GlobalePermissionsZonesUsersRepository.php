<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobalePermissionsZonesUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalePermissionsZonesUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalePermissionsZonesUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalePermissionsZonesUsers[]    findAll()
 * @method GlobalePermissionsZonesUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalePermissionsZonesUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalePermissionsZonesUsers::class);
    }

    // /**
    //  * @return GlobalePermissionsZonesUsers[] Returns an array of GlobalePermissionsZonesUsers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobalePermissionsZonesUsers
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
