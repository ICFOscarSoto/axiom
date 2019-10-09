<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerGroups[]    findAll()
 * @method ERPCustomerGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerGroups::class);
    }
    
    public function getMaxIncrement()
    {
      $query="SELECT max(c.rate) as increment FROM erpcustomer_groups c WHERE c.active=1 AND c.deleted=0";               
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetch();
      return $result['increment'];
    
    }
    
    
    public function checkRepeated($id,$name){
      
        if($id==NULL)
        {
          $query="SELECT * FROM erpcustomer_groups c WHERE c.name=:NAME AND c.active=1 AND c.deleted=0";
          $params=['NAME' => $name];
          $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetch();
          return $result;
        }
        else return false;
     
   }

    // /**
    //  * @return ERPCustomerGroups[] Returns an array of ERPCustomerGroups objects
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
    public function findOneBySomeField($value): ?ERPCustomerGroups
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
