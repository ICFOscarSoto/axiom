<?php

namespace App\Repository\Modules\AERP\Entity;

use App\Modules\AERP\Entity\AERPCustomerGroupsPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPCustomerGroupsPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPCustomerGroupsPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPCustomerGroupsPrices[]    findAll()
 * @method AERPCustomerGroupsPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPCustomerGroupsPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPCustomerGroupsPrices::class);
    }

    // /**
    //  * @return AERPCustomerGroupsPrices[] Returns an array of AERPCustomerGroupsPrices objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AERPCustomerGroupsPrices
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
