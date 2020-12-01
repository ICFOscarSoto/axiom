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

    public function getPurchasesOrdersByDate()
    {
      //$date_aux = new \DateTime('2020-11-13');
    //  $date=$date_aux->format("Y-m-d");
      $query="SELECT id FROM erppurchases_orders WHERE dateadd>='2020-11-14' AND dateadd<'2020-11-27'";
    //  $params=['DATE' => $date];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;

    }
}
