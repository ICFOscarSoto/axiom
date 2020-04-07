<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPTransfersHeader;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPTransfersHeader|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPTransfersHeader|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPTransfersHeader[]    findAll()
 * @method ERPTransfersHeader[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPTransfersHeaderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPTransfersHeader::class);
    }

    // /**
    //  * @return ERPTransfersHeader[] Returns an array of ERPTransfersHeader objects
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
    public function findOneBySomeField($value): ?ERPTransfersHeader
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
