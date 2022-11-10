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

    public function getInventoryLinesGroup($inventory_id, $storelocation_id){
      $query="SELECT il.productvariant_id as productvariant_id, sum(il.quantityconfirmed) as quantityconfirmed, sum(if(il.stockold,il.stockold,0)) as stockold
              FROM erpinventory_lines il
              WHERE il.inventory_id=:inventory and il.location_id=:storelocation and il.active=1 and il.deleted=0
              GROUP BY il.productvariant_id
              ORDER BY productvariant_id";
      $params=['inventory' => $inventory_id, 'storelocation' => $storelocation_id];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
    }
}
