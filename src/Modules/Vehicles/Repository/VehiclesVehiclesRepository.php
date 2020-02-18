<?php

namespace App\Modules\Vehicles\Repository;

use App\Modules\Vehicles\Entity\VehiclesVehicles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehiclesVehicles|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehiclesVehicles|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehiclesVehicles[]    findAll()
 * @method VehiclesVehicles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclesVehiclesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehiclesVehicles::class);
    }

    // /**
    //  * @return VehiclesVehicles[] Returns an array of VehiclesVehicles objects
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
    public function findOneBySomeField($value): ?VehiclesVehicles
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
