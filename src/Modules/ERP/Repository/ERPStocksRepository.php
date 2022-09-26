<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStocks[]    findAll()
 * @method ERPStocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStocks::class);
    }

    public function findByProduct($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.product_id = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function stocksByStores($product_id){
      $query="SELECT a.code Store, IFNULL (SUM(s.quantity),0) Quantity
              FROM erpstores a LEFT JOIN erpstore_locations u ON a.id=u.store_id
              LEFT JOIN erpstocks s ON u.id=s.storelocation_id
              LEFT JOIN erpproducts_variants pv on pv.id=s.productvariant_id AND pv.product_id= :product
              WHERE a.active=TRUE and a.deleted=0
              GROUP BY a.name";

      $params=['product' => $product_id];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }

    public function stocksByStore($product, $store){
      $query="SELECT a.code Store, IFNULL (SUM(s.quantity),0) Quantity
              FROM erpstores a
              LEFT JOIN erpstore_locations u ON a.id=u.store_id
              LEFT JOIN erpstocks s ON u.id=s.storelocation_id
              LEFT JOIN erpproducts_variants pv on pv.id=s.productvariant_id AND pv.product_id= :product
              WHERE a.active=TRUE and a.deleted=0 AND a.id=:store
              GROUP BY a.name";
      $params=['product' => $product->getId(), 'store' =>$store];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function stockUpdate($product_id, $store){
      $query="SELECT id, quantity FROM erpstocks s LEFT JOIN erpproducts_variants pv on pv.id=s.productvariant_id AND pv.product_id= :product and pv.variant_id is null WHERE s.deleted=0 AND s.storelocation_id IN
                  (SELECT id FROM erpstore_locations WHERE store_id IN
                    (SELECT id FROM erpstores WHERE CODE= :store))
              ORDER BY quantity DESC
              LIMIT 1";
      $params=['product' => $product_id, 'store' => $store];

      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function setZeroStocks($product_id, $store_id, $stock_id, $productvariant_id){
      $query="UPDATE erpstocks
              SET quantity=0
              WHERE productvariant_id= :productvariant AND deleted=0 AND storelocation_id IN
                  (SELECT id FROM erpstore_locations WHERE store_id IN
                    (SELECT id FROM erpstores WHERE id=:store_id)) AND id!=:stock";
      $params=['product' => $product_id, 'store_id' => $store_id, 'stock'=>$stock_id, 'productvariant'=>$productvariant_id];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params);
    }


    public function stockVariantUpdate($variant_id, $store){
      $query="SELECT id, quantity FROM erpstocks WHERE productvariant_id= :variant AND deleted=0 AND storelocation_id IN
                  (SELECT id FROM erpstore_locations WHERE store_id IN
                    (SELECT id FROM erpstores WHERE CODE= :store))
              ORDER BY quantity DESC
              LIMIT 1";
      $params=['variant' => $variant_id, 'store' => $store];

      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function findInventoryStocks($company, $store=0, $location=0, $category=0){
      $query="SELECT stk.id as id,pr.code as product_code, pr.name as product_name,strl.name as location,stk.quantity as quantity,stk.lastinventorydate as lastinventorydate FROM erpstocks stk
                LEFT JOIN erpproducts_variants pv
                ON pv.id=stk.productvariant_id
                LEFT JOIN erpproducts pr
                ON pr.id=pv.product_id
                LEFT JOIN erpcategories ct
                ON ct.id=pr.category_id
                LEFT JOIN erpstore_locations strl
                ON strl.id=stk.storelocation_id
                LEFT JOIN erpstores st
                ON st.id=strl.store_id
                WHERE st.company_id=:company AND stk.deleted=0 AND stk.active=1 ";
      if($store!=0) $query.=" AND st.id=".$store;
      if($location!=0) $query.=" AND strl.id=".$location;
     if($category!=0) $query.=" AND pr.category_id=".$category;
      $query.=" ORDER BY pr.name ASC";
      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function findInventoryStockByLocation($company, $location=0){
      $query="SELECT stk.id as id,pr.code as product_code, pr.name as product_name,strl.name as location,stk.quantity as quantity,stk.lastinventorydate as lastinventorydate FROM erpstocks stk
                LEFT JOIN erpproducts_variants pv
                ON pv.id=stk.productvariant_id
                LEFT JOIN erpproducts pr
                ON pr.id=pv.product_id
                LEFT JOIN erpcategories ct
                ON ct.id=pr.category_id
                LEFT JOIN erpstore_locations strl
                ON strl.id=stk.storelocation_id
                LEFT JOIN erpstores st
                ON st.id=strl.store_id
                WHERE st.company_id=:company AND stk.deleted=0 AND stk.active=1 ";
      if($location!=0) $query.=" AND strl.name='".$location."'";
      $query.=" ORDER BY pr.name ASC";
      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function findStockByProductStore($product, $store){
      $query='SELECT SUM(stk.quantity) as quantity, SUM(stk.pendingreceive) as pendingreceive
      FROM erpstocks stk
      LEFT JOIN erpproducts_variants pv
      ON pv.id=stk.productvariant_id
      LEFT JOIN erpstore_locations stl
      ON stl.id=stk.storelocation_id
      WHERE pv.product_id='.$product.' AND pv.variant_id is null AND stl.store_id='.$store.' AND stl.active=1 AND stk.deleted=0';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetch();
    }

    public function getStocksByProduct($product, $variant, $store){
      if($variant==null){
        $query='SELECT stk.quantity as quantity, stl.name as store_location, str.name as store
        FROM erpstocks stk
        LEFT JOIN erpproducts_variants pv
        ON pv.id=stk.productvariant_id
        LEFT JOIN erpstore_locations stl
        ON stl.id=stk.storelocation_id
        LEFT JOIN erpstores str
        ON str.id=stl.store_id
        WHERE pv.product_id='.$product.' AND pv.variant_id is null AND stl.store_id='.$store.' AND stk.active=1 AND stk.deleted=0
        AND stl.active=1 AND stl.deleted=0  AND str.active=1 AND str.deleted=0';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      }
      else{
        $query='SELECT stk.quantity as quantity, stl.name as store_location, str.name as store
        FROM erpstocks stk
        LEFT JOIN erpproducts_variants pv
        ON pv.id=stk.productvariant_id
        LEFT JOIN erpstore_locations stl
        ON stl.id=stk.storelocation_id
        LEFT JOIN erpstores str
        ON str.id=stl.store_id
        WHERE pv.product_id='.$product.' AND pv.variant_id='.$variant.' AND stl.store_id='.$store.' AND stk.active=1 AND stk.deleted=0
        AND stl.active=1 AND stl.deleted=0  AND str.active=1 AND str.deleted=0';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      }
    }

    public function getAllStocksByProduct($product, $variant){
      if($variant==null){
        $query='SELECT SUM(stk.quantity) AS quantity
        FROM erpstocks stk
        LEFT JOIN erpproducts_variants pv
        ON pv.id=stk.productvariant_id
        WHERE pv.product_id='.$product.' AND pv.variant_id is null AND stk.active=1 AND stk.deleted=0';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);
      }
      else{
        $query='SELECT SUM(stk.quantity) as quantity
        FROM erpstocks stk
        LEFT JOIN erpproducts_variants pv
        ON pv.id=stk.productvariant_id
        WHERE pv.product_id='.$product.' AND pv.variant_id='.$variant.'  AND stk.active=1 AND stk.deleted=0';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);
      }
    }

    public function findStockByProductVariantStore($product, $variant, $store){
      $query='SELECT SUM(stk.quantity) as TOTAL, sum(stk.pendingreceive) as pendingreceive
      FROM erpstocks stk
      LEFT JOIN erpproducts_variants pv
      ON pv.id=stk.productvariant_id
      LEFT JOIN erpstore_locations stl
      ON stl.id=stk.storelocation_id
      WHERE pv.product_id='.$product.' AND pv.variant_id='.$variant.' AND stl.store_id='.$store.' AND stk.active=1 AND stk.deleted=0';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetch();
    }

    public function findLocationsByStoreProduct($store,$product,$variant)
    {
      if($variant==null)
      {
      $query='SELECT sl.id, sl.name
        FROM erpstore_locations sl
        LEFT JOIN erpstocks s
        ON s.storelocation_id=sl.id
        LEFT JOIN erpproducts_variants pv
        ON pv.id=stk.productvariant_id
        WHERE sl.store_id='.$store.' AND sl.active=1 AND sl.deleted=0 AND pv.product_id='.$product.' AND pv.variant_id is null';
        return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      }
      else{
        $query='SELECT sl.id, sl.name
          FROM erpstore_locations sl
          LEFT JOIN erpstocks s
          ON s.storelocation_id=sl.id
          LEFT JOIN erpproducts_variants pv
          ON pv.id=s.productvariant_id
          WHERE sl.store_id='.$store.' AND sl.active=1 AND sl.deleted=0 AND pv.product_id='.$product.' AND pv.variant_id='.$variant;
          return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      }
    }

    public function getStock($product_id, $variant_id, $store_id){
      $query="SELECT sum(stk.quantity) AS 'stock',
                     sum(stk.minstock) AS 'minstock',
                     sum(stk.pendingreceive) AS 'stockpedingreceive',
                     sum(stk.pendingserve) AS 'stockpedingserve',
                     sum(stk.quantity)+sum(stk.pendingreceive)-sum(stk.pendingserve) as 'stockvirtual'
      FROM erpstocks stk
      LEFT JOIN erpproducts_variants pv ON pv.id=stk.productvariant_id
      LEFT JOIN erpstore_locations stl ON stl.id=stk.storelocation_id
      LEFT JOIN erpstores str ON str.id=stl.store_id
      WHERE pv.product_id=$product_id AND
            pv.variant_id".($variant_id==null?" is null":"=".$variant_id)." AND
            stl.store_id=$store_id AND
            stk.active=1 AND stk.deleted=0 AND
            stl.active=1 AND stl.deleted=0 AND
            str.active=1 AND str.deleted=0
      GROUP BY pv.product_id";
      $result = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      if ($result==null){
        $result = [];
        $result['stock'] = 0;
        $result['minstock'] = 0;
        $result['stockpedingreceive'] = 0;
        $result['stockpedingserve'] = 0;
        $result['stockvirtual'] = 0;
      }
      else{
        $result = $result[0];
      }
      $query="SELECT sum(stk.quantity) AS 'stockt',
                     sum(stk.pendingreceive) AS 'stockpedingreceivet',
                     sum(stk.pendingserve) AS 'stockpedingservet',
                     sum(stk.quantity)+sum(stk.pendingreceive)-sum(stk.pendingserve) as 'stockvirtualt'
      FROM erpstocks stk
      LEFT JOIN erpproducts_variants pv ON pv.id=stk.productvariant_id
      LEFT JOIN erpstore_locations stl ON stl.id=stk.storelocation_id
      LEFT JOIN erpstores str ON str.id=stl.store_id
      WHERE pv.product_id=$product_id AND
            pv.variant_id".($variant_id==null?" is null":"=".$variant_id)." AND
            stk.active=1 AND stk.deleted=0 AND
            stl.active=1 AND stl.deleted=0 AND
            str.active=1 AND str.deleted=0
      GROUP BY pv.product_id";
      $resultt = $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      if ($resultt!=null){
        $resultt = $resultt[0];
        $result['stockt']=$resultt['stockt'];
        $result['stockpedingreceivet']=$resultt['stockpedingreceivet'];
        $result['stockpedingservet']=$resultt['stockpedingservet'];
        $result['stockvirtualt']=$resultt['stockvirtualt'];
      }else{
        $result['stockt'] = 0;
        $result['stockpedingreceivet'] = 0;
        $result['stockpedingservet'] = 0;
        $result['stockvirtualt'] = 0;
      }

      return $result;
    }

    public function getMinimum($store){
      $query='SELECT pv.product_id, vv.name variant_name, p.grouped, (s.quantity+s.pendingreceive) quantity
              FROM erpstocks s
              LEFT JOIN erpproducts_variants pv
              ON pv.id=s.productvariant_id
              LEFT JOIN erpvariants vv
              ON vv.id=pv.variant_id
              LEFT JOIN erpproducts p
              ON p.id=pv.product_id
              WHERE (s.quantity+s.pendingreceive) <=  s.minstock
              AND s.storelocation_id IN (SELECT l.id FROM erpstore_locations l WHERE l.active=1 AND l.deleted=0 AND l.store_id =:store)
              AND i.active=1 AND i.deleted=0
              AND s.active=1 AND s.deleted=0';
      $params=['store' => $store->getId()];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
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
}
