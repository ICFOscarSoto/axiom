<?php

namespace App\Modules\Carrier\Repository;

use App\Modules\Carrier\Entity\CarrierStateZones;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CarrierStateZones|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarrierStateZones|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarrierStateZones[]    findAll()
 * @method CarrierStateZones[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarrierStateZonesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CarrierStateZones::class);
    }

    // /**
    //  * @return CarrierStateZones[] Returns an array of CarrierStateZones objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CarrierStateZones
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
