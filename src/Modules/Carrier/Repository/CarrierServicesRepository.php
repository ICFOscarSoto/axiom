<?php

namespace App\Modules\Carrier\Repository;

use App\Modules\Carrier\Entity\CarrierServices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CarrierServices|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarrierServices|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarrierServices[]    findAll()
 * @method CarrierServices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarrierServicesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CarrierServices::class);
    }

    // /**
    //  * @return CarrierServices[] Returns an array of CarrierServices objects
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
    public function findOneBySomeField($value): ?CarrierServices
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
