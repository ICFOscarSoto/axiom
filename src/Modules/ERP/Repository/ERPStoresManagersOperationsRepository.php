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


      if($start) $date_start=$start->format("Y-m-d 00:00:00");
      else{
        $date_start=new \Datetime();
        $date_start->setTimestamp(0);
        $date_start=$date_start->format("Y-m-d 00:00:00");

      }

      if($end) $date_end=$end->format("Y-m-d 23:59:59");
      else {
        $date_end=new \Datetime();
        $date_end=$date_end->format("Y-m-d 23:59:59");
      }


      if($store==null){

          $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname,
          IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0) total
          FROM erpstores_managers_operations o
          LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
          LEFT JOIN erpstores_managers m ON m.id=o.manager_id
          LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
          LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
          LEFT JOIN erpproduct_prices p ON p.product_id=l.product_id
          WHERE o.active=1 AND o.manager_id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END
          GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
          $params=[
                   'MANAGER' => $manager,
                   'START' => $date_start,
                   'END' => $date_end
                   ];

      }
      //
      else{

          $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname,
          IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0) total
          FROM erpstores_managers_operations o
          LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
          LEFT JOIN erpstores_managers m ON m.id=o.manager_id
          LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
          LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
          LEFT JOIN erpproduct_prices p ON p.product_id=l.product_id
          WHERE o.active=1 AND o.manager_id=:MANAGER AND o.store_id=:STORE AND o.DATE >= :START AND o.DATE<=:END
          GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
          $params=[
                   'MANAGER' => $manager,
                   'STORE' => $store,
                   'START' => $date_start,
                   'END' => $date_end
                   ];
      }

      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;

    }

    public function getFullOperationsByConsumer($manager, $start, $end, $store)
    {

    $cont_meses=1;
    $cont_años=1;
    $today=new \Datetime('NOW');
    $thismonth=new \Datetime('first day of this month');
    $lastmonth=new \Datetime('first day of this month');
    $lastmonth->modify('-1 month');
    $months_count=new \Datetime('first day of january this year');
    $months_count_next=new \Datetime('first day of january this year');
    $months_count_next->modify('+1 month');
    $thisyear = new \Datetime('first day of january this year');
    $lastyear = new \Datetime('first day of january this year');
    $lastyear->modify('-1 year');


    if($start) $date_start=$start->format("Y-m-d 00:00:00");
    else{
      $date_start=new \Datetime();
      $date_start->setTimestamp(0);
      $date_start=$date_start->format("Y-m-d 00:00:00");

    }


    if($end) $date_end=$end->format("Y-m-d 23:59:59");
    else {
      $date_end=new \Datetime();
      $date_end=$date_end->format("Y-m-d 23:59:59");
    }




    if($store==null){

      $sql_meses="";
      while($lastmonth->format("Y-m-d")>=$months_count->format("Y-m-d")){
          $sql_meses=$sql_meses."(IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
               FROM erpstores_managers_operations_lines lx
               LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
               LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
               LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
               LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
               LEFT JOIN erpstores sx ON sx.id=ox.store_id
               WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$months_count->format("Y-m-d")."' AND ox.DATE<='".$months_count_next->format("Y-m-d")."' AND lx.product_id=l.product_id
               GROUP BY (lx.product_id)),0)) '".$months_count->format("m")."',";

           $months_count->modify('+1 month');
           $months_count_next->modify('+1 month');
           $cont_meses++;
      }
      //Años anteriores
      $sql_años="";
      while($cont_años<3){

        $sql_años=$sql_años."IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
             FROM erpstores_managers_operations_lines lx
             LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
             LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
             LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
             LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
             LEFT JOIN erpstores sx ON sx.id=ox.store_id
             WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$lastyear->format("Y-m-d")."' AND ox.DATE<='".$thisyear->format("Y-m-d")."' AND lx.product_id=l.product_id
             GROUP BY (lx.product_id)),0) '".$lastyear->format("Y")."',";

            $thisyear->modify('-1 year');
            $lastyear->modify('-1 year');
            $cont_años++;

      }

        $query="SELECT c.NAME, IFNULL(c.lastname,'') lastname,
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
						 FROM erpstores_managers_operations_lines lx
						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
						 LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= :START AND ox.DATE<=:END AND lx.product_id=l.product_id
						 GROUP BY (lx.product_id)),0)) Suma,
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
     						 FROM erpstores_managers_operations_lines lx
     						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
     						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
     						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
     						 LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
     						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
     						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$thismonth->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id
     						 GROUP BY (lx.product_id)),0)) 'Mes actual',".$sql_meses."
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
              						 FROM erpstores_managers_operations_lines lx
              						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
              						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
              						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
              						 LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
              						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
              						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$thisyear->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id
              						 GROUP BY (lx.product_id)),0)) '".date("Y")."',".$sql_años."
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
     						 FROM erpstores_managers_operations_lines lx
     						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
     						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
     						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
     						 LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
     						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
     						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND lx.product_id=l.product_id
     						 GROUP BY (lx.product_id)),0)) Total
        FROM erpstores_managers_operations o
        LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
        LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpproduct_prices p ON p.product_id=l.product_id
        WHERE o.active=1 AND o.manager_id=:MANAGER
        GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
        $params=[
                 'MANAGER' => $manager,
                 'START' => $date_start,
                 'END' => $date_end
                 ];

    }
    //
    else{

      $sql_meses="";
      while($lastmonth->format("Y-m-d")>=$months_count->format("Y-m-d")){
          $sql_meses=$sql_meses."(IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
               FROM erpstores_managers_operations_lines lx
               LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
               LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
               LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
               LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
               LEFT JOIN erpstores sx ON sx.id=ox.store_id
               WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$months_count->format("Y-m-d")."' AND ox.DATE<='".$months_count_next->format("Y-m-d")."' AND lx.product_id=l.product_id AND ox.store_id=:STORE
               GROUP BY (lx.product_id)),0)) '".$months_count->format("m")."',";

           $months_count->modify('+1 month');
           $months_count_next->modify('+1 month');
           $cont_meses++;
      }
      //Años anteriores
      $sql_años="";
      while($cont_años<3){

        $sql_años=$sql_años."IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
             FROM erpstores_managers_operations_lines lx
             LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
             LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
             LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
             LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
             LEFT JOIN erpstores sx ON sx.id=ox.store_id
             WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$lastyear->format("Y-m-d")."' AND ox.DATE<='".$thisyear->format("Y-m-d")."' AND lx.product_id=l.product_id AND ox.store_id=:STORE
             GROUP BY (lx.product_id)),0) '".$lastyear->format("Y")."',";

            $thisyear->modify('-1 year');
            $lastyear->modify('-1 year');
            $cont_años++;

      }

        $query="SELECT c.NAME, IFNULL(c.lastname,'') lastname,
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
             FROM erpstores_managers_operations_lines lx
             LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
             LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
             LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
             LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
             LEFT JOIN erpstores sx ON sx.id=ox.store_id
             WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= :START AND ox.DATE<=:END AND lx.product_id=l.product_id AND ox.store_id=:STORE
             GROUP BY (lx.product_id)),0)) Suma,
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
                 FROM erpstores_managers_operations_lines lx
                 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                 LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
                 LEFT JOIN erpstores sx ON sx.id=ox.store_id
                 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$thismonth->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id AND ox.store_id=:STORE
                 GROUP BY (lx.product_id)),0)) 'Mes actual',".$sql_meses."
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
                           FROM erpstores_managers_operations_lines lx
                           LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                           LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                           LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                           LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
                           LEFT JOIN erpstores sx ON sx.id=ox.store_id
                           WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$thisyear->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id AND ox.store_id=:STORE
                           GROUP BY (lx.product_id)),0)) '".date("Y")."',".$sql_años."
        (IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(IFNULL(ofx.price,px.price)*lx.quantity),2),'.',','),0)
                 FROM erpstores_managers_operations_lines lx
                 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                 LEFT JOIN erpproduct_prices px ON px.product_id=lx.product_id
                 LEFT JOIN erpstores sx ON sx.id=ox.store_id
                 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND lx.product_id=l.product_id
                 GROUP BY (lx.product_id)),0)) Total
        FROM erpstores_managers_operations o
        LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
        LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpproduct_prices p ON p.product_id=l.product_id
        WHERE o.active=1 AND o.manager_id=:MANAGER AND o.store_id=:STORE
        GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname";
        $params=[
                 'MANAGER' => $manager,
                 'STORE' => $store,
                 'START' => $date_start,
                 'END' => $date_end
                 ];



    }

    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
    return $result;

  }



  public function getDailyOperations($manager, $start, $end, $store)
  {
    if($start) $date_start=$start->format("Y-m-d 00:00:00");
    else $date_start=null;

    if($end)  $date_end=$end->format("Y-m-d 23:59:59");
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

}
