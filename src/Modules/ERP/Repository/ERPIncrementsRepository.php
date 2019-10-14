<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPIncrements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPIncrements|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPIncrements|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPIncrements[]    findAll()
 * @method ERPIncrements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPIncrementsRepository extends ServiceEntityRepository
{
  public function __construct(RegistryInterface $registry)
  {
        parent::__construct($registry, ERPIncrements::class);
  }

  public function checkSupplierOnCategory($supplier,$category,$company){

    if($category!=NULL AND $supplier!=NULL)
      {
       $query="SELECT * FROM erpproducts e WHERE e.category_id=:CAT AND e.active=1 AND e.deleted=0 AND (e.supplier_id=:SUP OR e.id IN (SELECT r.product_id FROM erpreferences r WHERE r.supplier_id=:SUP))";
       $params=['CAT' => $category->getId(),
                'SUP' => $supplier->getId(),
                'COMP' => $company->getId()
                ];
       $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
       return $result;

     }

     else return true;

   }

   public function checkRepeated($id,$supplier,$category,$customergroup,$company){

     if($category!=NULL AND $supplier!=NULL AND $customergroup!=NULL AND $id==NULL)
     {
       $query="SELECT * FROM erpincrements e WHERE e.category_id=:CAT AND e.supplier_id=:SUP AND e.customergroup_id=:CUST AND e.active=1 AND e.deleted=0";
       $params=['CAT' => $category->getId(),
                'SUP' => $supplier->getId(),
                'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];

     }
     else if($category!=NULL AND $customergroup!=NULL AND $id==NULL)
     {

       $query="SELECT * FROM erpincrements e WHERE e.category_id=:CAT AND e.customergroup_id=:CUST AND e.supplier_id IS NULL AND e.active=1 AND e.deleted=0";
       $params=['CAT' => $category->getId(),
                'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];

     }

     else if($supplier!=NULL AND $customergroup!=NULL AND $id==NULL)
     {

       $query="SELECT * FROM erpincrements e WHERE e.supplier_id=:SUP AND e.customergroup_id=:CUST AND e.category_id IS NULL AND e.active=1 AND e.deleted=0";
       $params=[ 'SUP' => $supplier->getId(),
                'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];

     }

     else if($id==NULL)
     {
       $query="SELECT * FROM erpincrements e WHERE e.customergroup_id=:CUST AND e.category_id=NULL AND e.supplier_id IS NULL AND e.active=1 AND e.deleted=0";
       $params=[ 'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
        return $result;
     }
     else return false;

  }

  public function getMaxIncrement($supplier,$category,$customergroup){

      if($supplier!=NULL AND $category!=NULL){

        $query="SELECT max(i.increment) as increment FROM erpincrements i WHERE i.supplier_id=:SUP AND i.category_id=:CAT AND i.customergroup_id=:GRP AND i.active=1 AND i.deleted=0";
        $params=[ 'SUP' => $supplier->getId(),
                 'CAT' => $category->getId(),
                 'GRP' => $customergroup->getId()
                 ];

        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
        return $result['increment']*1;

      }

      else if($supplier!=NULL){
        $query="SELECT max(i.increment) as increment FROM erpincrements i WHERE i.supplier_id=:SUP AND i.category_id IS NULL  AND i.customergroup_id=:GRP AND i.active=1 AND i.deleted=0";
        $params=[ 'SUP' => $supplier->getId(),
                  'GRP' => $customergroup->getId()
                 ];

        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
        return $result['increment']*1;

      }

      else if($category!=NULL){

        $query="SELECT max(i.increment) as increment FROM erpincrements i WHERE i.supplier_id IS NULL AND i.category_id=:CAT  AND i.customergroup_id=:GRP AND i.active=1 AND i.deleted=0";
        $params=['CAT' => $category->getId(),
                'GRP' => $customergroup->getId()
                 ];

        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
        return $result['increment']*1;

      }

      else return false;

  }

    public function getIncrementByGroup($supplier,$category,$customergroup){

      $result=0;
      if($supplier!=NULL AND $category!=NULL){

        $query="SELECT i.increment as increment FROM erpincrements i WHERE i.supplier_id=:SUP AND i.category_id=:CAT AND i.customergroup_id=:GRP AND i.active=1 AND i.deleted=0";
        $params=[ 'SUP' => $supplier->getId(),
                 'CAT' => $category->getId(),
                 'GRP' => $customergroup->getId()
                 ];

        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();

        }

       if($supplier!=NULL AND $result==NULL){
        // dump("Result 1:".$result['increment']*1);
        $query="SELECT i.increment as increment FROM erpincrements i WHERE i.supplier_id=:SUP AND i.category_id IS NULL AND i.customergroup_id=:GRP AND i.active=1 AND i.deleted=0";
        $params=[ 'SUP' => $supplier->getId(),
                  'GRP' => $customergroup->getId()
                 ];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();

      }

      if($category!=NULL AND $result==NULL){
      //dump("Result 2:".$result['increment']*1);
        $query="SELECT i.increment as increment FROM erpincrements i WHERE i.supplier_id IS NULL AND i.category_id=:CAT AND i.customergroup_id=:GRP AND i.active=1 AND i.deleted=0";
        $params=['CAT' => $category->getId(),
                'GRP' => $customergroup->getId()
                 ];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();

      }

      if($result!=NULL)
      {
    //    dump("Vamos a devolver: ".$result['increment']*1);
        return $result['increment']*1;
      }

      else return NULL;


    }

    // /**
    //  * @return ERPIncrements[] Returns an array of ERPIncrements objects
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
    public function findOneBySomeField($value): ?ERPIncrements
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
