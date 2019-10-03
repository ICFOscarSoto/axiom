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
   
   public function checkRepeated($supplier,$category,$customergroup,$company){
     
     if($category!=NULL AND $supplier!=NULL AND $customergroup!=NULL)
     {
       $query="SELECT * FROM erpincrements e WHERE e.category_id=:CAT AND e.supplier_id=:SUP AND e.customergroup_id=:CUST AND e.active=1 AND e.deleted=0";
       $params=['CAT' => $category->getId(),
                'SUP' => $supplier->getId(),
                'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];

     }
     else if($category!=NULL AND $customergroup!=NULL)
     {
     
       $query="SELECT * FROM erpincrements e WHERE e.category_id=:CAT AND e.customergroup_id=:CUST AND e.supplier_id IS NULL AND e.active=1 AND e.deleted=0";
       $params=['CAT' => $category->getId(),
                'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];
     
     }
     
     else if($supplier!=NULL AND $customergroup!=NULL)
     {
     
       $query="SELECT * FROM erpincrements e WHERE e.supplier_id=:SUP AND e.customergroup_id=:CUST AND e.category_id IS NULL AND e.active=1 AND e.deleted=0";
       $params=[ 'SUP' => $supplier->getId(),
                'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];
     
     }
     
     else 
     {
       $query="SELECT * FROM erpincrements e WHERE e.customergroup_id=:CUST AND e.category_id=NULL AND e.supplier_id IS NULL AND e.active=1 AND e.deleted=0";
       $params=[ 'CUST' => $customergroup->getId(),
                'COMP' => $company->getId()
                ];
     }
    
    
             
    $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
    return $result;

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
