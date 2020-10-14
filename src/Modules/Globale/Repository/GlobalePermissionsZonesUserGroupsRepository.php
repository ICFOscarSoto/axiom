<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobalePermissionsZonesUserGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalePermissionsZonesUserGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalePermissionsZonesUserGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalePermissionsZonesUserGroups[]    findAll()
 * @method GlobalePermissionsZonesUserGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalePermissionsZonesUserGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalePermissionsZonesUserGroups::class);
    }

    // /**
    //  * @return GlobalePermissionsZonesUserGroups[] Returns an array of GlobalePermissionsZonesUserGroups objects
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
    public function findOneBySomeField($value): ?GlobalePermissionsZonesUserGroups
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
