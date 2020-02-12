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

    public function stocksByStores($product){
      $query="SELECT a.name Store, IFNULL (SUM(s.quantity),0) Quantity
              FROM erpstores a LEFT JOIN erpstore_locations u ON a.id=u.store_id LEFT JOIN erpstocks s ON u.id=s.storelocation_id AND s.product_id= :product
              WHERE a.active=TRUE and a.deleted=0
              GROUP BY a.name";

      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

    }

    public function findInventoryStocks($company, $store=0, $category=0){
      $query="SELECT stk.id as id,pr.name as product,strl.name as location,stk.quantity as quantity, stk.pendingreceive as pendingreceive, stk.pendingserve as pendingserve,stk.lastinventorydate as lastinventorydate FROM erpstocks stk
                LEFT JOIN erpproducts pr
                ON pr.id=stk.product_id
                LEFT JOIN erpcategories ct
                ON ct.id=pr.category_id
                LEFT JOIN erpstore_locations strl
                ON strl.id=stk.storelocation_id
                LEFT JOIN erpstores st
                ON st.id=strl.store_id
                WHERE st.company_id=:company AND stk.deleted=0 AND stk.active=1 ";
      if($store!=0) $query.=" AND st.id=".$store;
     if($category!=0) $query.=" AND pr.category_id=".$category;
      $query.=" ORDER BY pr.name ASC";
      $params=['company' => $company->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
}
