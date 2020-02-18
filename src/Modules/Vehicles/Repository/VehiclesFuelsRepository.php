<?php

namespace App\Modules\Vehicles\Repository;

use App\Modules\Vehicles\Entity\VehiclesFuels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehiclesFuels|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehiclesFuels|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehiclesFuels[]    findAll()
 * @method VehiclesFuels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclesFuelsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehiclesFuels::class);
    }

    // /**
    //  * @return VehiclesFuels[] Returns an array of VehiclesFuels objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VehiclesFuels
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
