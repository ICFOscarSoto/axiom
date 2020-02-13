<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStockHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStockHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStockHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStockHistory[]    findAll()
 * @method ERPStockHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStockHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStockHistory::class);
    }

    // /**
    //  * @return ERPStockHistory[] Returns an array of ERPStockHistory objects
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
    public function findOneBySomeField($value): ?ERPStockHistory
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
