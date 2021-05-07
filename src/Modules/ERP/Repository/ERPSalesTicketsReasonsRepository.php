<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSalesTicketsReasons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSalesTicketsReasons|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSalesTicketsReasons|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSalesTicketsReasons[]    findAll()
 * @method ERPSalesTicketsReasons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSalesTicketsReasonsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesTicketsReasons::class);
    }

    // /**
    //  * @return ERPSalesTicketsReasons[] Returns an array of ERPSalesTicketsReasons objects
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
    public function findOneBySomeField($value): ?ERPSalesTicketsReasons
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
