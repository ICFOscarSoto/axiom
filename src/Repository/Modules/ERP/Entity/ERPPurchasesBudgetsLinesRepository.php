<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPPurchasesBudgetsLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPurchasesBudgetsLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPurchasesBudgetsLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPurchasesBudgetsLines[]    findAll()
 * @method ERPPurchasesBudgetsLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPurchasesBudgetsLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPurchasesBudgetsLines::class);
    }

    // /**
    //  * @return ERPPurchasesBudgetsLines[] Returns an array of ERPPurchasesBudgetsLines objects
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
    public function findOneBySomeField($value): ?ERPPurchasesBudgetsLines
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
