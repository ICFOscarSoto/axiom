<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoreTicketsReasons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoreTicketsReasons|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoreTicketsReasons|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoreTicketsReasons[]    findAll()
 * @method ERPStoreTicketsReasons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoreTicketsReasonsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoreTicketsReasons::class);
    }

    // /**
    //  * @return ERPStoreTicketsReasons[] Returns an array of ERPStoreTicketsReasons objects
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
    public function findOneBySomeField($value): ?ERPStoreTicketsReasons
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
