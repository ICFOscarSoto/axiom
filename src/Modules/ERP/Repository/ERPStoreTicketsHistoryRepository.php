<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoreTicketsHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoreTicketsHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoreTicketsHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoreTicketsHistory[]    findAll()
 * @method ERPStoreTicketsHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoreTicketsHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoreTicketsHistory::class);
    }

    // /**
    //  * @return ERPStoreTicketsHistory[] Returns an array of ERPStoreTicketsHistory objects
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
    public function findOneBySomeField($value): ?ERPStoreTicketsHistory
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
