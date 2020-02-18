<?php

namespace App\Repository\Modules\Vehicles\Entity;

use App\Modules\Vehicles\Entity\VehiclesRefuelings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehiclesRefuelings|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehiclesRefuelings|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehiclesRefuelings[]    findAll()
 * @method VehiclesRefuelings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclesRefuelingsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehiclesRefuelings::class);
    }

    // /**
    //  * @return VehiclesRefuelings[] Returns an array of VehiclesRefuelings objects
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
    public function findOneBySomeField($value): ?VehiclesRefuelings
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
