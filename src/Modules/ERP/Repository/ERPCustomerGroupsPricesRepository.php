<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerGroupsPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerGroupsPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerGroupsPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerGroupsPrices[]    findAll()
 * @method ERPCustomerGroupsPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerGroupsPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerGroupsPrices::class);
    }

    // /**
    //  * @return ERPCustomerGroupsPrices[] Returns an array of ERPCustomerGroupsPrices objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ERPCustomerGroupsPrices
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
