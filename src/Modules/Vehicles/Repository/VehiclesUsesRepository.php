<?php

namespace App\Modules\Vehicles\Repository;

use App\Modules\Vehicles\Entity\VehiclesUses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehiclesUses|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehiclesUses|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehiclesUses[]    findAll()
 * @method VehiclesUses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclesUsesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehiclesUses::class);
    }

    // /**
    //  * @return VehiclesUses[] Returns an array of VehiclesUses objects
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
    public function findOneBySomeField($value): ?VehiclesUses
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
