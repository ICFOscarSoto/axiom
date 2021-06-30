<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersOperationsLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersOperationsLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersOperationsLines[]    findAll()
 * @method ERPStoresManagersOperationsLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersOperationsLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersOperationsLines::class);
    }

    // /**
    //  * @return ERPStoresManagersOperationsLines[] Returns an array of ERPStoresManagersOperationsLines objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersOperationsLines
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getProductsByConsumer($manager, $start, $end, $store){
    $date_start=$start->format("Y-m-d");
    $date_end=$end->format("Y-m-d");
    if($store==null){

    $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, SUM(l.quantity) total
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
    LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
    WHERE o.active=1 AND o.DATE >= :START AND o.DATE<=:END
    GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
    $params=['START' => $date_start,
             'END' => $date_end
             ];
    }
    else{

      $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, SUM(l.quantity) total
      FROM erpstores_managers_operations_lines l
      LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
      LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
      WHERE o.active=1 AND o.DATE >= :START AND o.DATE<=:END AND o.store_id=:STORE
      GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
      $params=['START' => $date_start,
               'END' => $date_end,
               'STORE' => $store,
               ];

    }
    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
    return $result;

      }



  public function getBestProducts($manager, $start, $end, $store){
  $date_start=$start->format("Y-m-d");
  $date_end=$end->format("Y-m-d");

  if($store==null){
    $query="SELECT l.product_id, l.code, l.name, SUM(l.quantity) total
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
    LEFT JOIN erpstores_managers m ON m.id=o.manager_id
    WHERE o.active=1 AND m.id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END
    GROUP BY(l.code)  ORDER BY total DESC";
    $params=['MANAGER' => $manager,
             'START' => $date_start,
             'END' => $date_end
             ];
  }
  else{

    $query="SELECT l.product_id, l.code, l.name, SUM(l.quantity) total
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
    LEFT JOIN erpstores_managers m ON m.id=o.manager_id
    WHERE o.active=1 AND m.id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END AND o.store_id=:STORE
    GROUP BY(l.code)  ORDER BY total DESC";
    $params=['MANAGER' => $manager,
             'START' => $date_start,
             'END' => $date_end,
             'STORE' => $store
             ];


  }
  $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
  return $result;

}
}
