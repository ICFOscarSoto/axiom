<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSalesBudgetsLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSalesBudgetsLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSalesBudgetsLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSalesBudgetsLines[]    findAll()
 * @method ERPSalesBudgetsLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSalesBudgetsLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesBudgetsLines::class);
    }

    // /**
    //  * @return ERPSalesBudgetsLines[] Returns an array of ERPSalesBudgetsLines objects
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
    public function findOneBySomeField($value): ?ERPSalesBudgetsLines
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
