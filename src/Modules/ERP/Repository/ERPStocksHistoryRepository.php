<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStocksHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStocksHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStocksHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStocksHistory[]    findAll()
 * @method ERPStocksHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStocksHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStocksHistory::class);
    }

    // /**
    //  * @return ERPStocksHistory[] Returns an array of ERPStocksHistory objects
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
    public function findOneBySomeField($value): ?ERPStocksHistory
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
                FROM erpstocks_history h
                LEFT JOIN erpproducts_variants pv
                ON pv.id=h.productvariant_id
                LEFT JOIN erpproducts pr
                ON pr.id=pv.product_id
                LEFT JOIN erpvariants vv
                ON pv.variant_id=vv.id
                LEFT JOIN erpstore_locations strl
                ON strl.id=h.location_id
                LEFT JOIN erpstores str
                ON str.id=strl.store_id
                LEFT JOIN globale_users u
                ON u.id=h.user_id
                LEFT JOIN erptypes_movements t
                ON t.id=h.type_id
                WHERE pv.product_id=:product AND h.deleted=0 AND h.active=1 ";
      $query.=" ORDER BY h.dateadd DESC LIMIT 50";
      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function findAllHistory($product){
      $query="SELECT h.id as id, pr.code as product_code, pr.name as product_name, strl.name as location,
                str.name as store, CONCAT(u.name,' ',u.lastname) as user,
                h.previousqty as prevqty, h.newqty as newqty, h.dateadd as dateadd, h.comment AS comment, t.name AS type, h.num_operation AS numOperation, h.quantity as quantity
                FROM erpstocks_history h
                LEFT JOIN erpproducts_variants pv
                ON pv.id=h.productvariant_id
                LEFT JOIN erpproducts pr
                ON pr.id=pv.product_id
                LEFT JOIN erpstore_locations strl
                ON strl.id=h.location_id
                LEFT JOIN erpstores str
                ON str.id=strl.store_id
                LEFT JOIN globale_users u
                ON u.id=h.user_id
                LEFT JOIN erptypes_movements t
                ON t.id=h.type_id
                WHERE pv.product_id=:product AND h.deleted=0 AND h.active=1 ";
      $params=['product' => $product];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


}
