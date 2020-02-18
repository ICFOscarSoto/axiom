<?php

namespace App\Modules\Vehicles\Repository;

use App\Modules\Vehicles\Entity\VehiclesTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VehiclesTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method VehiclesTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method VehiclesTypes[]    findAll()
 * @method VehiclesTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiclesTypesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VehiclesTypes::class);
    }

    // /**
    //  * @return VehiclesTypes[] Returns an array of VehiclesTypes objects
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
    public function findOneBySomeField($value): ?VehiclesTypes
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
