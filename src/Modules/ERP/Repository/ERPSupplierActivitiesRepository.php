<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSupplierActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSupplierActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSupplierActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSupplierActivities[]    findAll()
 * @method ERPSupplierActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSupplierActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSupplierActivities::class);
    }

    // /**
    //  * @return ERPSupplierActivities[] Returns an array of ERPSupplierActivities objects
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
    public function findOneBySomeField($value): ?ERPSupplierActivities
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
