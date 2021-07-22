<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPInfoStocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPInfoStocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPInfoStocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPInfoStocks[]    findAll()
 * @method ERPInfoStocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPInfoStocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPInfoStocks::class);
    }


    public function getOperations($store, $date){
      $query='SELECT SUM(quantity) vendido, code, product_id
              FROM erpstores_managers_operations_lines
              WHERE CODE=CODE AND dateadd> CAST(:datestart AS DATETIME) AND operation_id IN
                  (SELECT id FROM erpstores_managers_operations
                  WHERE store_id IN (SELECT id FROM erpstores WHERE CODE=:store))
              GROUP BY CODE';
      $params=['store' => $store, 'datestart'=>$date];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
    }

    public function getMinimum($store){
      $query='SELECT s.product_id, s.quantity FROM erpstocks s
      WHERE s.quantity <= (SELECT i.minimum_quantity
                          FROM erpinfo_stocks i
                          WHERE i.product_id=s.product_id
                          AND store_id IN (SELECT id FROM erpstores WHERE CODE=:store))
      AND storelocation_id IN (SELECT id
                              FROM erpstore_locations
                              WHERE store_id IN (SELECT id FROM erpstores WHERE CODE=:store))';
      $params=['store' => $store];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
    }


    // /**
    //  * @return ERPInfoStocks[] Returns an array of ERPInfoStocks objects
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
    public function findOneBySomeField($value): ?ERPInfoStocks
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
