<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerSpecialConditions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerSpecialConditions|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerSpecialConditions|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerSpecialConditions[]    findAll()
 * @method ERPCustomerSpecialConditions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerSpecialConditionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerSpecialConditions::class);
    }


    public function checkDefaultGroup($customer,$customergroup){

      $query="SELECT * FROM erpcustomer_commercial_terms c WHERE c.customer_id=:CUST AND c.customergroup_id=:GRP AND c.active=1 AND c.deleted=0";
      $params=['CUST' => $customer->getId(),
               'GRP' => $customergroup->getId()
               ];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
      return $result;

    }

    public function checkRepeated($customer, $supplier,$category,$customergroup,$company){

      if($category!=NULL AND $supplier!=NULL AND $customergroup!=NULL)
      {
        $query="SELECT * FROM erpincrements e WHERE e.id=:CUST AND e.category_id=:CAT AND e.supplier_id=:SUP AND e.customergroup_id=:GRP AND e.active=1 AND e.deleted=0";
        $params=['CUST' => $customer->getId(),
                 'CAT' => $category->getId(),
                 'SUP' => $supplier->getId(),
                 'GRP' => $customergroup->getId(),
                 'COMP' => $company->getId()
                 ];

      }
      else if($category!=NULL AND $customergroup!=NULL)
      {

        $query="SELECT * FROM erpincrements e WHERE e.id=:CUST AND e.category_id=:CAT AND e.customergroup_id=:GRP AND e.supplier_id IS NULL AND e.active=1 AND e.deleted=0";
        $params=['CUST' => $customer->getId(),
                 'CAT' => $category->getId(),
                 'GRP' => $customergroup->getId(),
                 'COMP' => $company->getId()
                 ];

      }

      else if($supplier!=NULL AND $customergroup!=NULL)
      {

        $query="SELECT * FROM erpincrements e WHERE e.id=:CUST AND e.supplier_id=:SUP AND e.customergroup_id=:GRP AND e.category_id IS NULL AND e.active=1 AND e.deleted=0";
        $params=['CUST' => $customer->getId(),
                 'SUP' => $supplier->getId(),
                 'GRP' => $customergroup->getId(),
                 'COMP' => $company->getId()
                 ];

      }

      else
      {
        $query="SELECT * FROM erpincrements e WHERE e.id=:CUST AND e.customergroup_id=:GRP AND e.category_id=NULL AND e.supplier_id IS NULL AND e.active=1 AND e.deleted=0";
        $params=[ 'CUST' => $customer->getId(),
                  'GRP' => $customergroup->getId(),
                  'COMP' => $company->getId()
                 ];
      }



     $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
     return $result;

   }

   public function pricesByCustomer($customer){
     $query="SELECT c.name as Category, s.name as Supplier, g.name as CustomerGroup
             FROM erpcustomers_prices p
             LEFT JOIN erpcategories c ON c.id=p.category_id
             LEFT JOIN erpsuppliers s ON s.id=p.supplier_id
             LEFT JOIN erpcustomer_groups g ON g.id=p.customergroup_id
             WHERE p.customer_id=:CUST AND p.active=TRUE and p.deleted=0";
     $params=['CUST' => $customer->getId()];
     return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();

   }

}
