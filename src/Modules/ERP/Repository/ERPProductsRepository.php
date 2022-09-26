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
      where id in (select p.product_id from erpproducts p left join erpproducts_variants pv on pv.product_id=p.id left join erpproducts_suppliers ps on ps.productvariant_id=pv.id and pv.variant_id is null where ps.supplier_id=:supplier) AND p.category_id=:category";
      else $query="SELECT id from erpproducts
      where supplier_id=:supplier";
      $params=['supplier' => $supplier,
              'category' => $category];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;

    }

    public function getVariants($product){
        $query='SELECT v.id as id, vt.name as type, v.name as name
        FROM erpproducts_variants pv
        LEFT JOIN erpvariants v ON v.id=pv.variant_id
        LEFT JOIN erpvariants_types vt ON vt.id=v.varianttype_id
        WHERE pv.product_id=:product AND pv.variant_id is not null AND pv.active=1 AND pv.deleted=0 ORDER BY name ASC';
        $params=['product' => $product];
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getvariant($product){
        $query='SELECT v.id as id, v.name as name
        FROM erpproducts_variants pv
        LEFT JOIN erpvariants v ON v.id=pv.variant_id
        LEFT JOIN erpvariants_types vt ON vt.id=v.varianttype_id
        WHERE pv.product_id=:product AND pv.variant_id is not null AND pv.active=1 AND pv.deleted=0 ORDER BY name ASC';
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

    public function productsLimitCategory($start, $page){
      $query='SELECT id FROM erpproducts
              where category_id is not null and supplier_id is not null and netprice=0
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
              WHERE manager_id=:manager and active=1 and deleted=0";
      $params=['manager' => $manager];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    /*
    * Obtiene los productos de un proveedor
    */
    public function getProductsBySupplierMasive($supplier_id){
      $query="SELECT distinct(concat(p.id,'-',if (vv.id is null,0,vv.id))) as ids,
                     p.id as id,
                     if (vv.id is null,0,vv.id) as variantid,
                     c.path_name as category,
                     p.code as productcode,
                     p.name as productname,
                     if (vv.id is null,'Sin variante',concat(v.name, ' - ',vv.name)) as variantname,
                     0 as quantity,
                     if (pv.purchasepacking is null, 1, if (pv.purchasepacking='', 1, pv.purchasepacking)) as 'packing',
                     if (ps.multiplicity is null, 1, if (ps.multiplicity='', 1, ps.multiplicity)) as 'multiplicity',
                     if (ps.minimumquantityofbuy is null, 1, if (ps.minimumquantityofbuy='', 1, ps.minimumquantityofbuy)) as 'minimumquantityofbuy',
                     if (ps.purchaseunit is null, 1, if (ps.purchaseunit='',1, ps.purchaseunit)) as 'purchaseunit'
              FROM erpproducts_suppliers ps LEFT JOIN
                   erpproducts_variants pv on pv.id=ps.productvariant_id LEFT JOIN
                   erpproducts p on p.id=pv.product_id LEFT JOIN
                   erpcategories c on c.id=p.category_id LEFT JOIN
                   erpvariants vv on vv.id=pv.variant_id LEFT JOIN
                   erpvariants_types v on v.id=vv.varianttype_id
              WHERE ps.supplier_id=:supplier_id and
                    p.active=1 and p.deleted=0 and
                    ps.active=1 and ps.deleted=0 and
                    p.code is not null and p.code <> '' and
                    (vv.id is not null or (vv.id is null and (select count(*) from erpproducts_variants pv2 left join erpproducts_suppliers ps2 on pv2.id=ps2.productvariant_id where pv2.product_id=p.id and pv2.variant_id is not null and ps2.supplier_id=ps.supplier_id)=0))
              GROUP BY id, variantid
              ORDER BY category ASC, productname ASC, variantname ASC";
      $params=["supplier_id" => $supplier_id];
      $result = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      return $result;
    }

    /*
    * Obtiene los productos de un proveedor acotado por la busqueda solicitada
    */
    public function getProductsBySupplier($supplier_id, $q){
      $result = null;
      if ($q!=null && $q!=''){
        $q = urldecode($q);
        $filter = '';
        if (substr($q,0,1)=="["){
          $jsonq = json_decode($q,true);
          if (count($jsonq)>0){
            $filter = "and (";
            for($i=0; $i<count($jsonq); $i++){
              $filter .= "p.id = ".$jsonq[$i];
              if ($i<(count($jsonq)-1))
                $filter .= ' or ';
            }
            $filter .= ")";
          }
        }else{
          $q = str_replace(' ','%',$q);
          $filter = "and (p.code like '%$q%' or p.name like '%$q%')";
        }
        $query="SELECT distinct(concat(p.id,'~',p.code)) as id,
                       p.code as name,
                       p.name as title
                FROM erpproducts_suppliers ps LEFT JOIN
                     erpproducts_variants pv on pv.id=ps.productvariant_id LEFT JOIN
                     erpproducts p on p.id=pv.product_id
                WHERE ps.supplier_id=:supplier_id and
                      pv.variant_id is null and
                      pv.active=1 and pv.deleted=0 and
                      p.active=1 and p.deleted=0
                      $filter
                UNION
                SELECT '0~Artículo...' as id, 'Artículo...' as name, '' as title FROM erpproducts
                ORDER BY title ASC";
        $params=["supplier_id" => $supplier_id];
        $result = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      }
      return $result;
    }

    /*
    * Obtiene el producto que tiene un proveedor y su coste en función de la cantidad
    */
    public function getProductBySupplier($supplier, $product, $quantity){
      $result = null;
      if ($product!='|'){
        $aproduct = explode('~',$product);
        if (count($aproduct)>1)
          $product = $aproduct[0];
        $query="SELECT concat(p.id,'~',p.code) as 'product_id',
                       p.name as 'productname',
                       if (psp.pvp is null, 0, if (psp.pvp='', 0, psp.pvp)) as 'pvp',
                       if (sd.discount1 is null, 0, if (sd.discount1='', 0, sd.discount1)) as 'discount1',
                       if (sd.discount2 is null, 0, if (sd.discount2='', 0, sd.discount2)) as 'discount2',
                       if (sd.discount3 is null, 0, if (sd.discount3='', 0, sd.discount3)) as 'discount3',
                       if (sd.discount4 is null, 0, if (sd.discount4='', 0, sd.discount4)) as 'discount4',
                       if (sd.discount is null, 0, if (sd.discount='', 0, sd.discount)) as 'discountequivalent',
                       if (t.tax is null, 0, if (t.tax='', 0, t.tax)) as 'taxperc',
                       if (pv.weight is null, 0, if (pv.weight='', 0, pv.weight)) as 'weight',
                       if (pv.purchasepacking is null, 1, if (pv.purchasepacking='', 1, pv.purchasepacking)) as 'packing',
                       if (ps.multiplicity is null, 1, if (ps.multiplicity='', 1, ps.multiplicity)) as 'multiplicity',
                       if (ps.minimumquantityofbuy is null, 1, if (ps.minimumquantityofbuy='', 1, ps.minimumquantityofbuy)) as 'minimumquantityofbuy',
                       if (ps.purchaseunit is null, 1, if (ps.purchaseunit='',1, ps.purchaseunit)) as 'purchaseunit',
                       if (m.name is null, '', m.name) as 'purchasemeasure'
                FROM erpproducts_suppliers_prices psp LEFT JOIN
                     erpproducts_suppliers ps on ps.id=psp.productsupplier_id LEFT JOIN
                     erpproducts_variants pv on pv.id=ps.productvariant_id LEFT JOIN
                     erpproducts p on p.id=pv.product_id LEFT JOIN
                     erpmeasurement_units m on m.id=p.purchasemeasure_id LEFT JOIN
                     globale_taxes t on t.id=p.taxes_id LEFT JOIN
                     erpproducts_suppliers_discounts sd on sd.supplier_id=ps.supplier_id and sd.category_id=p.category_id and sd.quantity<=:quantity and
                      sd.active=1 and sd.deleted=0 and
                      (sd.start is null or sd.start<=now()) and
                       (sd.end is null or sd.end>=now())
                WHERE ps.supplier_id=:supplier and
                      p.id=:product and
                      psp.quantity<=:quantity and
                      pv.variant_id is null and
                      p.active=1 and p.deleted=0 and
                      psp.active=1 and psp.deleted=0 and
                      (psp.start is null or psp.start<=now()) and
                      (psp.end is null or psp.end>=now())
                ORDER BY p.name ASC, psp.quantity DESC, sd.quantity DESC";
        $params=["supplier" => $supplier, "product" => $product, "quantity"=>$quantity];
        $result = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
      }
      if ($result == null || count($result)==0){
        $result =
        [[
            "product_id" => "0~Artículo...",
            "productname" => "",
            "pvp" => "0",
            "discount1" => "0",
            "discount2" => "0",
            "discount3" => "0",
            "discount4" => "0",
            "discountequivalent" => "0",
            "taxperc" => "0",
            "weight" => "0",
            "packing" => "1",
            "multiplicity" => "1",
            "minimumquantityofbuy" => "1",
            "purchaseunit" => "1",
            "purchasemeasure" => "",
            "stock" => "0",
            "minstock" => "0",
            "stockpedingreceive" => "0",
            "stockpedingserve" => "0",
            "stockvirtual" => "0",
            "stockt" => "0",
            "stockpedingreceivet" => "0",
            "stockpedingservet" => "0",
            "stockvirtualt" => "0",
            "quantitycomment" => ""
        ]];
      }else{
          // Cálculo de mínimo de cantidad max(minimumquantityofbuy,multiplicity,packing,purchaseunit) > quantity sino quantity
          $quantity             = intval($quantity);
          $minimumquantityofbuy = intval($result[0]['minimumquantityofbuy']);
          $multiplicity         = intval($result[0]['multiplicity']);
          $packing              = intval($result[0]['packing']);
          $purchaseunit         = intval($result[0]['purchaseunit']);
          $changequantity       = false;
          $quantitycomment      = '';
          if ($minimumquantityofbuy>$quantity){
            $quantity = $minimumquantityofbuy;
            $changequantity = true;
          }
          if ($multiplicity>$quantity){
            $quantity = $multiplicity;
            $changequantity = true;
          }
          if ($packing>$quantity){
            $quantity = $packing;
            $changequantity = true;
          }
          if ($purchaseunit>$quantity){
            $quantity = $purchaseunit;
            $changequantity = true;
          }
          if ($changequantity){
            $result[0]['quantity'] = $quantity;
          }
          // Se muestra el tooltip con los mínimos de compra
          if ($minimumquantityofbuy>1){
            $quantitycomment .= "Mínimo de compra: $minimumquantityofbuy\n";
            $result[0]['quantity__min'] = $minimumquantityofbuy;
          }
          if ($multiplicity>1){
            $quantitycomment .= "Multiplicidad: $multiplicity\n";
            $result[0]['quantity__multiplicity'] = $multiplicity;
          }
          if ($packing>1){
            $quantitycomment .= "Packing: $packing\n";
          }
          if ($purchaseunit>1){
            $quantitycomment .= "Unidad de compra: $purchaseunit\n";
          }
          // Dtos por Cantidad
          $query="SELECT distinct(sd.id) as 'id',
                         if (sd.discount is null, 0, if (sd.discount='', 0, sd.discount)) as 'discountequivalent',
                         if (sd.quantity is null, 1, if (sd.quantity='', 1, sd.quantity)) as 'quantity'
                  FROM erpproducts_suppliers_prices psp LEFT JOIN
                       erpproducts_suppliers ps on ps.id=psp.productsupplier_id LEFT JOIN
                       erpproducts_variants pv on pv.id=ps.productvariant_id LEFT JOIN
                       erpproducts p on p.id=pv.product_id LEFT JOIN
                       erpproducts_suppliers_discounts sd on sd.supplier_id=ps.supplier_id and sd.category_id=p.category_id and sd.quantity>1 and
                        sd.active=1 and sd.deleted=0 and
                        (sd.start is null or sd.start<=now()) and
                         (sd.end is null or sd.end>=now())
                  WHERE ps.supplier_id=:supplier and
                        p.id=:product and
                        p.active=1 and p.deleted=0 and
                        pv.variant_id is null and
                        psp.active=1 and psp.deleted=0 and
                        (psp.start is null or psp.start<=now()) and
                        (psp.end is null or psp.end>=now())
                  ORDER BY p.name ASC, psp.quantity DESC, sd.quantity DESC";
          $params=["supplier" => $supplier, "product" => $product];
          $resultdto = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
          if ($resultdto!=null && count($resultdto)>0){
            $quantitycomment .= "Dtos por cantidad:\n";
            for($i=0; $i<count($resultdto); $i++){
              $quantitycomment .= ">= ".$resultdto[$i]['quantity']." -> Dto equivalente: ".$resultdto[$i]['discountequivalent']."%\n";
            }
          }
          $result[0]['quantity__comment'] = $quantitycomment;
      }
      return $result;
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
