<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPInventoryLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPInventoryLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPInventoryLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPInventoryLines[]    findAll()
 * @method ERPInventoryLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPInventoryLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPInventoryLines::class);
    }

    // /**
    //  * @return ERPInventoryLines[] Returns an array of ERPInventoryLines objects
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
    public function findOneBySomeField($value): ?ERPInventoryLines
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getLines($id){
      $query="SELECT p.name AS productname, p.code AS productcode, st.name AS location, SUM(il.quantityconfirmed) AS quantity,  il.stockold AS oldquantity,  concat(u.name,' ',u.lastname) AS name
              	FROM erpinventory_lines il
              	LEFT JOIN erpproducts_variants pv ON il.productvariant_id=pv.id
              	LEFT JOIN erpproducts p ON p.id=pv.product_id
              	LEFT JOIN erpstore_locations st ON  il.location_id=st.id
              	LEFT JOIN globale_users u ON il.author_id=u.id
              	WHERE il.inventory_id=:inventory and  il.active=1 and  il.deleted=0
              	GROUP BY il.productvariant_id, il.location_id ";
      $params=['inventory' => $id];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
}
