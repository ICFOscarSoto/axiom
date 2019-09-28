<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSalesBudgets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSalesBudgets|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSalesBudgets|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSalesBudgets[]    findAll()
 * @method ERPSalesBudgets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSalesBudgetsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesBudgets::class);
    }

    // /**
    //  * @return ERPSalesBudgets[] Returns an array of ERPSalesBudgets objects
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
    public function findOneBySomeField($value): ?ERPSalesBudgets
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
