<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProducts[]    findAll()
 * @method ERPProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProducts::class);
    }

    // /**
    //  * @return ERPProducts[] Returns an array of ERPProducts objects
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
    public function findOneBySomeField($value): ?ERPProducts
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function searchProduct($search){
      $tokens=explode('*',$search);
      $string='';
      foreach($tokens as $token){
        $string.=" AND (id='".$token."' OR name LIKE '%".$token."%' OR code LIKE '%".$token."%')";
      }
      //$query="SELECT id, code, name from erpproducts where active=1 and deleted=0".$string;
      $query="SELECT id, code, name, active from erpproducts where deleted=0".$string;
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    public function productsBySupplierCategory($supplier,$category){
      if($category!=0) $query="SELECT id from erpproducts
      where supplier_id=:supplier AND category_id=:category";
      else $query="SELECT id from erpproducts
      where supplier_id=:supplier";
      $params=['supplier' => $supplier,
              'category' => $category];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }

    public function getVariants($product){
        $query='SELECT pv.id as id,v.name as type, vv.name as name
        FROM erpproducts_variants pv
        LEFT JOIN erpvariants_values vv
        ON pv.variantvalue_id=vv.id
        LEFT JOIN erpvariants v
        ON pv.variantname_id=v.id
        WHERE pv.product_id=:product AND pv.active=1 AND pv.deleted=0 ORDER BY name ASC';
        $params=['product' => $product];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getVariantValues($product){
        $query='SELECT vv.id as id, vv.name as name
        FROM erpproducts_variants pv
        LEFT JOIN erpvariants_values vv
        ON pv.variantvalue_id=vv.id
        LEFT JOIN erpvariants v
        ON pv.variantname_id=v.id
        WHERE pv.product_id=:product AND pv.active=1 AND pv.deleted=0 ORDER BY name ASC';
        $params=['product' => $product];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    /* Nos devuelve la cantidad de productos con categoría */

    public function totalProductsCategory(){
      $query='SELECT count(id) as total from erpproducts where category_id is not null and supplier_id is not null and netprice=0';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result[0]["total"];
    }

    public function totalProducts(){
      $query='SELECT count(id) as total from erpproducts where active!=2';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result[0]["total"];
    }

    /* Nos devuelve un número limitado de productos, para ello se le indica el índice desde el que empieza $start, y la cantidad que queremos devolver $page  */

    public function productsLimitActive($start, $page){
      $query='SELECT id FROM erpproducts
              WHERE active=1
              ORDER BY code
              LIMIT '.$start.','.$page.'';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }

    public function productsLimit($start, $page){
      $query='SELECT id FROM erpproducts
              WHERE active!=2
              ORDER BY code
              LIMIT '.$start.','.$page.'';
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }


    public function latestMovements($product, $limit=200){
      $query='Select id,code,date,type,quantity,name
              FROM
              (SELECT o.id, o.code, o.date, 0 as type, quantity, customername name FROM erpsales_orders o
              	LEFT JOIN erpsales_orders_lines l ON l.salesorder_id=o.id
              	WHERE o.status=1 AND o.shipmentdate IS NOT NULL AND o.active=1 AND o.deleted=0 AND l.active=1 AND l.deleted=0 AND l.product_id=:product
              UNION
               SELECT o.id, o.code, o.date, 1 as type, quantity, suppliername name FROM erppurchases_orders o
              	LEFT JOIN erppurchases_orders_lines l ON l.purchasesorder_id=o.id
              	WHERE o.active=1 AND o.deleted=0 AND l.active=1 AND l.deleted=0 AND l.product_id=:product
              ) movements ORDER BY DATE DESC LIMIT '.$limit;
      $params=['product' => $product->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getWebProductBySupplier($supplier)
    {
      $query="SELECT id from erpproducts
      where supplier_id=:supplier AND checkweb=1";
      $params=['supplier' => $supplier->getId()];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;



    }

    public function getPendigServe($product)
    {
      $query="SELECT SUM(l.quantity)
          FROM erpsales_orders_lines l
          LEFT JOIN erpsales_orders h
          ON l.salesorder_id=h.id
          WHERE l.product_id=:product AND h.status=1 AND h.shipmentdate IS NULL AND h.active=1 AND h.deleted=0";
      $params=['product' => $product];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
      return $result;

    }

    public function getProductsToNavision()
    {
      //$date_aux = new \DateTime('2020-11-13');
    //  $date=$date_aux->format("Y-m-d");
      $query="SELECT id FROM erpproducts WHERE dateadd>'2020-11-13'";
    //  $params=['DATE' => $date];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;

    }


    public function getSuppliers($product){
        $query='SELECT s.id as id, s.name as name
        FROM erpsuppliers s
        LEFT JOIN erpproduct_prices pp
        ON pp.supplier_id=s.id
        WHERE pp.product_id='.$product.' AND pp.active=1 AND pp.deleted=0
        GROUP BY s.id,s.name
        ORDER BY name ASC';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }

    public function deleteRelations($product){
      $params=['product' => $product];
      $query="UPDATE erpreferences
              SET active=0, deleted=1
              WHERE product_id= :product";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params);
      $query2="UPDATE erpean13
              SET active=0, deleted=1
              WHERE product_id= :product";
      $result2=$this->getEntityManager()->getConnection()->executeQuery($query2, $params);
      return $result2;
    }

    public function getProductsByManager($manager){
      $query="SELECT product_id
              FROM erpstores_managers_products
              WHERE manager_id=:manager and active=1 and deleted!=0";
      $params=['manager' => $manager];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

/*
dejamos pendiente esta consulta porque falta por añadir en los pedidos de compra algun campo que indique que el material
de ese pedido ya se ha recibido

    public function getPendigReceive($product)
    {
      $query="SELECT SUM(l.quantity)
        FROM erppurchases_orders_lines l
        LEFT JOIN erppurchases_orders p
        ON p.id=l.purchasesorder_id
        WHERE l.product_id=:product AND p.`status`=1";
      $params=['product' => $product];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
      return $result;

    }

*/




}
