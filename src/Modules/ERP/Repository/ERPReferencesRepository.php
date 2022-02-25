<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPReferences;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPReferences|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPReferences|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPReferences[]    findAll()
 * @method ERPReferences[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPReferencesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPReferences::class);
    }


    public function ProductsBySupplier($id_supplier){
        $query="SELECT product_id FROM erpreferences e WHERE e.supplier_id=:SUP AND e.active=1 AND e.deleted=0";
         $params=['SUP' => $id_supplier];
         $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
         return $result;

     }

     public function getSuppliersByProduct($product){
          $query="SELECT supplier_id FROM erpreferences e WHERE e.product_id=:PROD AND e.active=1 AND e.deleted=0";
          $params=['PROD' => $product->getId()];
          $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
          return $result;

      }

      public function totalReferences(){
        $query='SELECT count(id) as total from erpreferences where active!=2';
        $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
        return $result[0]["total"];
      }

      public function referencesLimit($start, $page){
        $query='SELECT id FROM erpreferences
                WHERE active=!2
                ORDER BY name
                LIMIT '.$start.','.$page.'';
        $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
        return $result;
      }

      public function findProduct($supplier,$reference){
        $query='SELECT product_id FROM erpreferences
                WHERE active=1 and deleted=0 and supplier_id=:supplier and name like :reference ';
        $params=['supplier' => $supplier, 'reference'=>$reference];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
        return $result;

      }

      public function getReferenceByProductSupplier($supplier,$product){
        $query='SELECT name FROM erpreferences
                WHERE active=1 and deleted=0 and supplier_id=:supplier and product_id=:product ';
        $params=['supplier' => $supplier, 'product'=>$product];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
        return $result;
      }

    // /**
    //  * @return ERPReferences[] Returns an array of ERPReferences objects
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
    public function findOneBySomeField($value): ?ERPReferences
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
