<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomersPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomersPrices|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomersPrices|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomersPrices[]    findAll()
 * @method ERPCustomersPrices[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomersPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomersPrices::class);
    }

    // /**
    //  * @return ERPCustomersPrices[] Returns an array of ERPCustomersPrices objects
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
    public function findOneBySomeField($value): ?ERPCustomersPrices
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
