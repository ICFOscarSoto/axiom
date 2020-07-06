<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerOrdersData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerOrdersData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerOrdersData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerOrdersData[]    findAll()
 * @method ERPCustomerOrdersData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerOrdersDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerOrdersData::class);
    }


    public function ordersdataByCustomer($customer){

      $query="SELECT *
              FROM erpcustomer_orders_data c
              WHERE c.customer_id=:CST AND c.active=TRUE and c.deleted=0";
      $params=['CST'  =>  $customer->getId()];
      $result=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
      return $result;

    }

    // /**
    //  * @return ERPCustomerOrdersData[] Returns an array of ERPCustomerOrdersData objects
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
    public function findOneBySomeField($value): ?ERPCustomerOrdersData
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
