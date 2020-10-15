<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPPurchasesOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPurchasesBudgets|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPurchasesBudgets|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPurchasesBudgets[]    findAll()
 * @method ERPPurchasesBudgets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPurchasesOrdersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPurchasesOrders::class);
    }

    // /**
    //  * @return ERPPurchasesBudgets[] Returns an array of ERPPurchasesBudgets objects
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
    public function findOneBySomeField($value): ?ERPPurchasesBudgets
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
