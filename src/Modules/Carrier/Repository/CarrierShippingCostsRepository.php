<?php

namespace App\Modules\Carrier\Repository;

use App\Modules\Carrier\Entity\CarrierShippingCosts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShippingCosts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingCosts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingCosts[]    findAll()
 * @method ShippingCosts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CarrierShippingCostsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CarrierShippingCosts::class);
    }

    // /**
    //  * @return ShippingCosts[] Returns an array of ShippingCosts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShippingCosts
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
