<?php

namespace App\Modules\Carrier\Repository;

use App\Modules\Carrier\Entity\CarrierShippingConditions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CarrierShippingConditions|null find($id, $lockMode = null, $lockVersion = null)
 * @method CarrierShippingConditions|null findOneBy(array $criteria, array $orderBy = null)
 * @method CarrierShippingConditions[]    findAll()
 * @method CarrierShippingConditions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarrierShippingConditionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CarrierShippingConditions::class);
    }

    // /**
    //  * @return CarrierShippingConditions[] Returns an array of CarrierShippingConditions objects
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
    public function findOneBySomeField($value): ?CarrierShippingConditions
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
