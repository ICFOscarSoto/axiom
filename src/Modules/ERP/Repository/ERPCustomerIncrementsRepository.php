<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerIncrements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerIncrements|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerIncrements|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerIncrements[]    findAll()
 * @method ERPCustomerIncrements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerIncrementsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerIncrements::class);
    }

    // /**
    //  * @return ERPCustomerIncrements[] Returns an array of ERPCustomerIncrements objects
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
    public function findOneBySomeField($value): ?ERPCustomerIncrements
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getIncrementByCustomer($supplier,$category,$customer)
    {

      $result=0;
      if($supplier!=NULL AND $category!=NULL)
      {
        $query="SELECT i.increment as increment FROM erpcustomer_increments i WHERE i.supplier_id=:SUP AND i.category_id=:CAT AND i.customer_id=:CST AND i.active=1 AND i.deleted=0";
        $params=[ 'SUP' => $supplier->getId(),
                 'CAT' => $category->getId(),
                 'CST' => $customer->getId()
                 ];

        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
        }

       if($supplier!=NULL AND $result==NULL){
        $query="SELECT i.increment as increment FROM erpcustomer_increments i WHERE i.supplier_id=:SUP AND i.category_id IS NULL AND i.customer_id=:CST AND i.active=1 AND i.deleted=0";
        $params=[ 'SUP' => $supplier->getId(),
                  'CST' => $customer->getId()
                 ];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
      }

      if($category!=NULL AND $result==NULL){
        $query="SELECT i.increment as increment FROM erpcustomer_increments i WHERE i.supplier_id IS NULL AND i.category_id=:CAT AND i.customer_id=:CST AND i.active=1 AND i.deleted=0";
        $params=['CAT' => $category->getId(),
                'CST' => $customer->getId()
                 ];
        $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();

      }

      if($result!=NULL)
      {
        return $result['increment']*1;
      }

      else return NULL;
    }

    public function getIncrementIdByCustomer($supplier,$category,$customer)
    {
      $query="SELECT i.id as id FROM erpcustomer_increments i WHERE i.supplier_id=:SUP AND i.category_id=:CAT AND i.customer_id=:CST AND i.active=1 AND i.deleted=0";
      $params=[ 'SUP' => $supplier->getId(),
               'CAT' => $category->getId(),
               'CST' => $customer->getId()
               ];

    return $this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();


    }
}
