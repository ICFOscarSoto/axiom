<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPCustomerActivities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCustomerActivities|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCustomerActivities|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCustomerActivities[]    findAll()
 * @method ERPCustomerActivities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCustomerActivitiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCustomerActivities::class);
    }

    // /**
    //  * @return ERPCustomerActivities[] Returns an array of ERPCustomerActivities objects
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
    public function findOneBySomeField($value): ?ERPCustomerActivities
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
