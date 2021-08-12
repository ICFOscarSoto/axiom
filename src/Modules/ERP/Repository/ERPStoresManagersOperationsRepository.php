<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersOperations|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersOperations|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersOperations[]    findAll()
 * @method ERPStoresManagersOperations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersOperationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersOperations::class);
    }

    public function getOperationsProducts($user, $array_ids)
    {
      $query="SELECT p.id, p.code, l.variant_id, p.name, SUM(l.quantity) qty, IFNULL(p.minimumquantityofsale,1) minimumquantityofsale
              	FROM erpstores_managers_operations_lines l
              	LEFT JOIN erpproducts p ON p.id = l.product_id
                LEFT JOIN erpstores_managers_operations o ON o.id = l.operation_id
              	WHERE l.active = 1 AND l.deleted= 0 AND o.active = 1 AND o.deleted= 0
                AND o.id IN ($array_ids)
              	GROUP BY p.code, l.variant_id
                HAVING qty>0";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    // /**
    //  * @return ERPStoresManagersOperations[] Returns an array of ERPStoresManagersOperations objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersOperations
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getOperationsByConsumer($manager, $start, $end, $store)
    {

    if($start) $date_start=$start->format("Y-m-d");
    else{
      $date_start=new \Datetime();
      $date_start->setTimestamp(0);
      $date_start=$date_start->format("Y-m-d");

    }


    if($end)  $date_end=$end->format("Y-m-d");
    else {
      $date_end=new \Datetime();
      $date_end=$date_end->format("Y-m-d");
    }

    if($store==null){
        $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0) total
        FROM erpstores_managers_operations o
        LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
        LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpproduct_prices p ON p.id=l.product_id
        WHERE o.active=1 AND o.manager_id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END
        GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname LIMIT 10";
        $params=[
                 'MANAGER' => $manager,
                 'START' => $date_start,
                 'END' => $date_end
                 ];

    }
    else{

      $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0) total
      FROM erpstores_managers_operations o
      LEFT JOIN erpstores_managers m ON m.id=o.manager_id
      LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
      LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
      EFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
      LEFT JOIN erpproduct_prices p ON p.id=l.product_id
      WHERE o.active=1 AND o.manager_id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END AND o.store_id=:STORE
      GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname LIMIT 10";
      $params=[

               'MANAGER' => $manager,
               'START' => $date_start,
               'END' => $date_end,
               'STORE' => $store
               ];


    }

    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
    return $result;

  }



  public function getDailyOperations($manager, $start, $end, $store)
  {
    if($start) $date_start=$start->format("Y-m-d");
    else $date_start=null;

    if($end)  $date_end=$end->format("Y-m-d");
    else $date_end=null;

      if($store==null){
          $query="SELECT COUNT(id) total, DATE_FORMAT(date,'%d-%m-%Y') date
          FROM erpstores_managers_operations
          WHERE active=1 AND manager_id=:MANAGER AND DATE(DATE) >= :START AND DATE(DATE)<= :END
          GROUP BY(DATE(DATE))";
          $params=[ 'MANAGER' => $manager,
                    'START' => $date_start,
                    'END' => $date_end
                   ];

      }

      else{

        $query="SELECT COUNT(id) total,  DATE_FORMAT(date,'%d-%m-%Y') date
        FROM erpstores_managers_operations
        WHERE active=1 AND manager_id=:MANAGER AND DATE(DATE) >= :START AND DATE(DATE)<=:END AND store_id=:STORE
        GROUP BY(DATE(DATE))";
        $params=[ 'MANAGER' => $manager,
                  'START' => $date_start,
                  'END' => $date_end,
                  'STORE' => $store
                 ];
      }

      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;

  }

  public function getDetailedOperations($manager, $start, $end, $store)
  {
      $date_start=$start->format("Y-m-d");
      $date_end=$end->format("Y-m-d");

      if($store==null){
          $query="SELECT l.code, l.name, l.quantity, CONCAT(u.name) agente, CONCAT(c.name, c.lastname) consumidor, s.name almacen, o.date fecha
                  FROM erpstores_managers_operations_lines l
                  LEFT JOIN erpstores_managers_operations o
                  ON o.id=l.operation_id
                  LEFT JOIN globale_users u
                  ON u.id=o.agent_id
                  LEFT JOIN erpstores_managers_consumers c
                  ON c.id=o.consumer_id
                  LEFT JOIN erpstores s
                  ON s.id=o.store_id
                  WHERE o.active=1 AND o.manager_id=:MANAGER AND DATE(o.date) >= :START AND DATE(o.date)<= :END
                  ";
          $params=[ 'MANAGER' => $manager,
                    'START' => $date_start,
                    'END' => $date_end
                   ];

      }

      else{

        $query="SELECT l.code, l.name, l.quantity, CONCAT(u.name) agente, CONCAT(c.name, c.lastname) consumidor, s.name almacen, o.date fecha
                FROM erpstores_managers_operations_lines l
                LEFT JOIN erpstores_managers_operations o
                ON o.id=l.operation_id
                LEFT JOIN globale_users u
                ON u.id=o.agent_id
                LEFT JOIN erpstores_managers_consumers c
                ON c.id=o.consumer_id
                LEFT JOIN erpstores s
                ON s.id=o.store_id
                WHERE o.active=1 AND o.manager_id=:MANAGER AND DATE(o.date) >= :START AND DATE(o.date)<= :END AND o.store_id=:STORE
                ";
        $params=[ 'MANAGER' => $manager,
                  'START' => $date_start,
                  'END' => $date_end,
                  'STORE' => $store
                 ];
      }

      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;

  }

}
