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
      dump($id_supplier);
        $query="SELECT product_id FROM erpreferences e WHERE e.supplier_id=:SUP AND e.active=1 AND e.deleted=0";
         $params=['SUP' => $id_supplier];
         $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();       
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
