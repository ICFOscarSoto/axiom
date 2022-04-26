<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStockHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStockHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStockHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStockHistory[]    findAll()
 * @method ERPStockHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStockHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStockHistory::class);
    }

    // /**
    //  * @return ERPStockHistory[] Returns an array of ERPStockHistory objects
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
    public function findOneBySomeField($value): ?ERPStockHistory
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findHistory($product){
      $query="SELECT h.id as id, pr.code as product_code,vv.name as variant_name, pr.name as product_name, strl.name as location,
                str.name as store, CONCAT(u.name,' ',u.lastname) as user,
                h.previousqty as prevqty, h.newqty as newqty, h.dateadd as dateadd, h.comment AS comment, t.name AS type, h.num_operation AS numOperation, h.quantity as quantity
                FROM erpstock_history h
                LEFT JOIN erpproducts pr
                ON pr.id=h.product_id
                LEFT JOIN erpproducts_variants pv
                ON pv.id=h.productvariant_id
                LEFT JOIN erpvariants_values vv
                ON pv.variantvalue_id=vv.id
                LEFT JOIN erpstore_locations strl
                ON strl.id=h.location_id
                LEFT JOIN erpstores str
                ON str.id=h.store_id
                LEFT JOIN globale_users u
                ON u.id=h.user_id
                LEFT JOIN erptypes_movements t
                ON t.id=h.type_id
                WHERE h.product_id=:product AND h.deleted=0 AND h.active=1 ";
      $query.=" ORDER BY h.dateadd DESC LIMIT 50";
      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function findAllHistory($product){
      $query="SELECT h.id as id, pr.code as product_code, pr.name as product_name, strl.name as location,
                str.name as store, CONCAT(u.name,' ',u.lastname) as user,
                h.previousqty as prevqty, h.newqty as newqty, h.dateadd as dateadd, h.comment AS comment, t.name AS type, h.num_operation AS numOperation, h.quantity as quantity
                FROM erpstock_history h
                LEFT JOIN erpproducts pr
                ON pr.id=h.product_id
                LEFT JOIN erpstore_locations strl
                ON strl.id=h.location_id
                LEFT JOIN erpstores str
                ON str.id=h.store_id
                LEFT JOIN globale_users u
                ON u.id=h.user_id
                LEFT JOIN erptypes_movements t
                ON t.id=h.type_id
                WHERE h.product_id=:product AND h.deleted=0 AND h.active=1 ";
      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


}
