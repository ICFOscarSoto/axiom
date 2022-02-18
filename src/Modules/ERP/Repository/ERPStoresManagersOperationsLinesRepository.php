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
/*
    public function getProductsByConsumer($manager, $start, $end, $store){
    if($start) $date_start=$start->format("Y-m-d");
    else $date_start=null;

    if($end)  $date_end=$end->format("Y-m-d");
    else $date_end=null;

    if($store==null){

    $query="SELECT c.id, c.NAME, IFNULL(c.lastname,'') lastname, SUM(l.quantity) total
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
    LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
    WHERE o.active=1 AND o.DATE >= :START AND o.DATE<=:END
    GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname LIMIT 20";
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
      GROUP BY(o.consumer_id) ORDER BY c.NAME, c.lastname LIMIT 20";
      $params=['START' => $date_start,
               'END' => $date_end,
               'STORE' => $store,
               ];

    }
    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
    return $result;

    }

*/

  public function getBestProducts($manager, $start, $end, $store){
    if($start) $date_start=$start->format("Y-m-d 00:00:00");
    else{
      $date_start=new \Datetime();
      $date_start->setTimestamp(0);
      $date_start=$date_start->format("Y-m-d 00:00:00");

    }


    if($end)  $date_end=$end->format("Y-m-d 23:59:00");
    else {
      $date_end=new \Datetime();
      $date_end=$date_end->format("Y-m-d 23:59:00");
    }

  if($store==null){
    $query="SELECT l.product_id, l.code, l.name, IFNULL(ROUND(SUM(of.price*l.quantity),2),0) total
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
    LEFT JOIN erpstores_managers m ON m.id=o.manager_id
    LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id

    WHERE o.active=1 AND m.id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END
    GROUP BY(l.code)  ORDER BY total DESC LIMIT 10";
    $params=['MANAGER' => $manager,
             'START' => $date_start,
             'END' => $date_end
             ];
  }
  else{

    $query="SELECT l.product_id, l.code, l.name, IFNULL(ROUND(SUM(of.price*l.quantity),2),0) total
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON l.operation_id=o.id
    LEFT JOIN erpstores_managers m ON m.id=o.manager_id
    LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id

    WHERE o.active=1 AND m.id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END AND o.store_id=:STORE
    GROUP BY(l.code)  ORDER BY total DESC LIMIT 10";
    $params=['MANAGER' => $manager,
             'START' => $date_start,
             'END' => $date_end,
             'STORE' => $store
             ];


  }
  $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
  return $result;

}


public function getOperationsByProduct($manager, $start, $end, $store){

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
    $query="SELECT l.product_id,l.code,l.name,
    IFNULL(ROUND(SUM(of.price*l.quantity),2),0)
    FROM erpstores_managers_operations_lines l
    LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
    LEFT JOIN erpstores_managers m ON m.id=o.manager_id
    LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id

    LEFT JOIN erpproducts pr ON pr.id=p.product_id
    WHERE o.active=1 AND o.manager_id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END
    GROUP BY (l.product_id)";

    $params=[
             'MANAGER' => $manager,
             'START' => $date_start,
             'END' => $date_end
             ];
  }
  else{

      $query="SELECT l.product_id,l.code,l.name,
      IFNULL(ROUND(SUM(of.price*l.quantity),2),0)
      FROM erpstores_managers_operations_lines l
      LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
      LEFT JOIN erpstores_managers m ON m.id=o.manager_id
      LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id

      LEFT JOIN erpproducts pr ON pr.id=p.product_id
      WHERE o.active=1 AND o.manager_id=:MANAGER AND o.DATE >= :START AND o.DATE<=:END AND o.store_id=:STORE
      GROUP BY (l.product_id)";

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


public function getFullOperationsByProduct($manager, $start, $end, $store)
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
          $sql_meses=$sql_meses."IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
               FROM erpstores_managers_operations_lines lx
               LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
               LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
               LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
               LEFT JOIN erpstores sx ON sx.id=ox.store_id
               WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$months_count->format("Y-m-d")."' AND ox.DATE<='".$months_count_next->format("Y-m-d")."' AND lx.product_id=l.product_id
               GROUP BY (lx.product_id)),0) '".$months_count->format("m")."',";

           $months_count->modify('+1 month');
           $months_count_next->modify('+1 month');
           $cont_meses++;
      }
      //Años anteriores
      $sql_años="";
      while($cont_años<3){

        $sql_años=$sql_años."IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
             FROM erpstores_managers_operations_lines lx
             LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
             LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
             LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
             LEFT JOIN erpstores sx ON sx.id=ox.store_id
             WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$lastyear->format("Y-m-d")."' AND ox.DATE<='".$thisyear->format("Y-m-d")."' AND lx.product_id=l.product_id
             GROUP BY (lx.product_id)),0) '".$lastyear->format("Y")."',";

            $thisyear->modify('-1 year');
            $lastyear->modify('-1 year');
            $cont_años++;

      }

        $query="SELECT l.code Código,l.name Nombre,
          IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
  						 FROM erpstores_managers_operations_lines lx
  						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
  						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
  						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
  						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
  						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >=:START AND ox.DATE<=:END AND lx.product_id=l.product_id
  						 GROUP BY (lx.product_id)),0) 'Rango Fechas',
          IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
       						 FROM erpstores_managers_operations_lines lx
       						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
       						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
       						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
       						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
       						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$thismonth->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id
       						 GROUP BY (lx.product_id)),0) 'Mes actual',".$sql_meses."
          IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
           						 FROM erpstores_managers_operations_lines lx
           						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
           						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
           						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
           						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
           						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.DATE >= '".$thisyear->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id
           						 GROUP BY (lx.product_id)),0) '".date("Y")."',".$sql_años."
         IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
               				 FROM erpstores_managers_operations_lines lx
               				 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
               				 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
               				 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
               				 LEFT JOIN erpstores sx ON sx.id=ox.store_id
               				 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND lx.product_id=l.product_id
               				GROUP BY (lx.product_id)),0) Total
          FROM erpstores_managers_operations_lines l
          LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
          LEFT JOIN erpstores_managers m ON m.id=o.manager_id
          LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
          LEFT JOIN erpproducts pr ON pr.id=l.product_id
          WHERE o.active=1 AND o.manager_id=:MANAGER
          GROUP BY (l.product_id)";
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
                $sql_meses=$sql_meses."IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
                     FROM erpstores_managers_operations_lines lx
                     LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                     LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                     LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                     LEFT JOIN erpstores sx ON sx.id=ox.store_id
                     WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.store_id=:STORE AND ox.DATE >= '".$months_count->format("Y-m-d")."' AND ox.DATE<='".$months_count_next->format("Y-m-d")."' AND lx.product_id=l.product_id
                     GROUP BY (lx.product_id)),0) '".$months_count->format("m")."',";

                 $months_count->modify('+1 month');
                 $months_count_next->modify('+1 month');
                 $cont_meses++;
            }
            //Años anteriores
            $sql_años="";
            while($cont_años<3){

              $sql_años=$sql_años."IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
                   FROM erpstores_managers_operations_lines lx
                   LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                   LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                   LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                   LEFT JOIN erpstores sx ON sx.id=ox.store_id
                   WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.store_id=:STORE AND ox.DATE >= '".$lastyear->format("Y-m-d")."' AND ox.DATE<='".$thisyear->format("Y-m-d")."' AND lx.product_id=l.product_id
                   GROUP BY (lx.product_id)),0) '".$lastyear->format("Y")."',";

                  $thisyear->modify('-1 year');
                  $lastyear->modify('-1 year');
                  $cont_años++;

            }

              $query="SELECT l.code Código,l.name Nombre,
                IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
        						 FROM erpstores_managers_operations_lines lx
        						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
        						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
        						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
        						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
        						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.store_id=:STORE AND ox.DATE >=:START AND ox.DATE<=:END AND lx.product_id=l.product_id
        						 GROUP BY (lx.product_id)),0) 'Rango Fechas',
                IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
             						 FROM erpstores_managers_operations_lines lx
             						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
             						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
             						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
             						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
             						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.store_id=:STORE AND ox.DATE >= '".$thismonth->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id
             						 GROUP BY (lx.product_id)),0) 'Mes actual',".$sql_meses."
                IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
                 						 FROM erpstores_managers_operations_lines lx
                 						 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                 						 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                 						 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                 						 LEFT JOIN erpstores sx ON sx.id=ox.store_id
                 						 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.store_id=:STORE AND ox.DATE >= '".$thisyear->format("Y-m-d")."' AND ox.DATE<='".$today->format("Y-m-d")."' AND lx.product_id=l.product_id
                 						 GROUP BY (lx.product_id)),0) '".date("Y")."',".$sql_años."
               IFNULL((SELECT IFNULL(REPLACE(ROUND(SUM(ofx.price*lx.quantity),2),'.',','),0)
                     				 FROM erpstores_managers_operations_lines lx
                     				 LEFT JOIN erpstores_managers_operations ox ON ox.id=lx.operation_id
                     				 LEFT JOIN erpstores_managers mx ON mx.id=ox.manager_id
                     				 LEFT JOIN erpoffer_prices ofx ON ofx.product_id=lx.product_id AND ofx.customer_id=mx.customer_id
                     				 LEFT JOIN erpstores sx ON sx.id=ox.store_id
                     				 WHERE ox.active=1 AND ox.manager_id=:MANAGER AND ox.store_id=:STORE AND lx.product_id=l.product_id
                     				GROUP BY (lx.product_id)),0) Total
                FROM erpstores_managers_operations_lines l
                LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
                LEFT JOIN erpstores_managers m ON m.id=o.manager_id
                LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
                LEFT JOIN erpproducts pr ON pr.id=l.product_id
                WHERE o.active=1 AND o.manager_id=:MANAGER AND o.store_id=:STORE
                GROUP BY (l.product_id)";

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

    public function getOperationsByConsumerDetailed($consumer,$manager,$datefrom,$dateto,$store){

      if($datefrom) $date_start=$datefrom->format("Y-m-d 00:00:00");
      else{
        $date_start=new \Datetime();
        $date_start->setTimestamp(0);
        $date_start=$date_start->format("Y-m-d 00:00:00");

      }

      if($dateto) $date_end=$dateto->format("Y-m-d 23:59:59");
      else {
        $date_end=new \Datetime();
        $date_end=$date_end->format("Y-m-d 23:59:59");
      }

      if($store){
        $query="SELECT DISTINCT o.date Fecha,l.code Código,l.name Nombre,s.name Almacén,l.quantity Cantidad,
        REPLACE(of.price,'.',',') Precio,IFNULL(REPLACE(ROUND(of.price*l.quantity,2),'.',','),0) Total
        FROM erpstores_managers_operations_lines l
        LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpstores s ON s.id=o.store_id
        WHERE o.active=1 AND o.consumer_id=:CONSUMER AND o.DATE >=:START AND o.DATE<=:END AND o.store_id=:STORE
        ";
        $params=[
                 'CONSUMER' => $consumer,
                 'START' => $date_start,
                 'END' => $date_end,
                 'STORE' => $store
                 ];

      }
      else{
        $query="SELECT DISTINCT o.date Fecha,l.code Código,l.name Nombre,s.name Almacén,l.quantity Cantidad,
        REPLACE(of.price,'.',',') Precio,IFNULL(REPLACE(ROUND(of.price*l.quantity,2),'.',','),0) Total
        FROM erpstores_managers_operations_lines l
        LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpstores s ON s.id=o.store_id
        WHERE o.active=1 AND o.consumer_id=:CONSUMER AND o.DATE >=:START AND o.DATE<=:END
        ";

        $params=[
                 'CONSUMER' => $consumer,
                 'START' => $date_start,
                 'END' => $date_end
                 ];

      }


      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;


    }


    public function getOperationsByProductDetailed($product,$manager,$datefrom,$dateto,$store){

      if($datefrom) $date_start=$datefrom->format("Y-m-d 00:00:00");
      else{
        $date_start=new \Datetime();
        $date_start->setTimestamp(0);
        $date_start=$date_start->format("Y-m-d 00:00:00");

      }

      if($dateto) $date_end=$dateto->format("Y-m-d 23:59:59");
      else {
        $date_end=new \Datetime();
        $date_end=$date_end->format("Y-m-d 23:59:59");
      }

      if($store){
        $query="SELECT DISTINCT .o.date Fecha,l.code Código,l.name Nombre,concat(c.name,' ',c.lastname) Trabajador, s.name Almacén, l.quantity Cantidad, REPLACE(of.price,'.',',') Precio, IFNULL(REPLACE(ROUND(of.price*l.quantity,2),'.',','),0) Total
        FROM erpstores_managers_operations_lines l
        LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
        LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpstores s ON s.id=o.store_id
        WHERE o.active=1 AND l.product_id=:PRODUCT AND o.DATE >=:START AND o.DATE<=:END AND o.store_id=:STORE";

        $params=[
                 'PRODUCT' => $product,
                 'START' => $date_start,
                 'END' => $date_end,
                 'STORE' => $store
                 ];

      }
      else{
        $query="SELECT DISTINCT o.date Fecha,l.code Código,l.name Nombre, concat(c.name,' ',c.lastname) Trabajador, s.name Almacén, l.quantity Cantidad, REPLACE(of.price,'.',',') Precio, IFNULL(REPLACE(ROUND(of.price*l.quantity,2),'.',','),0) Total
        FROM erpstores_managers_operations_lines l
        LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
        LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
        LEFT JOIN erpstores_managers m ON m.id=o.manager_id
        LEFT JOIN erpoffer_prices of ON of.product_id=l.product_id AND of.customer_id=m.customer_id
        LEFT JOIN erpstores s ON s.id=o.store_id
        WHERE o.active=1 AND l.product_id=:PRODUCT AND o.DATE >=:START AND o.DATE<=:END
        ";

        $params=[
                 'PRODUCT' => $product,
                 'START' => $date_start,
                 'END' => $date_end
                 ];

      }


      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;


    }



}
