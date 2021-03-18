<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPSalesTicketsHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSalesTicketsHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSalesTicketsHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSalesTicketsHistory[]    findAll()
 * @method ERPSalesTicketsHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSalesTicketsHistoryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesTicketsHistory::class);
    }

    // /**
    //  * @return ERPSalesTicketsHistory[] Returns an array of ERPSalesTicketsHistory objects
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
    public function findOneBySomeField($value): ?ERPSalesTicketsHistory
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
