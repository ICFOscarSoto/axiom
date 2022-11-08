<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;

/**
 * @method ERPInventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPInventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPInventory[]    findAll()
 * @method ERPInventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPInventoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPInventory::class);
    }

    // /**
    //  * @return ERPInventory[] Returns an array of ERPInventory objects
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
    public function findOneBySomeField($value): ?ERPInventory
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getInventoryByStore($store_id){
      $query='SELECT id
              FROM erpinventory
              WHERE store_id=:store_id AND dateend is null AND active=1 AND deleted=0';
      $params=['store_id' => $store_id];
      $id = $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $id;
    }

}
