<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyOrders[]    findAll()
 * @method ERPBuyOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyOrdersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyOrders::class);
    }

    // /**
    //  * @return ERPBuyOrders[] Returns an array of ERPBuyOrders objects
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
    public function findOneBySomeField($value): ?ERPBuyOrders
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getLastID(){
      $query='SELECT max(id)
      FROM erpbuy_orders';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);

    }
}
