<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPTransfersLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPTransfersLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPTransfersLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPTransfersLine[]    findAll()
 * @method ERPTransfersLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPTransfersLineRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPTransfersLine::class);
    }

    // /**
    //  * @return ERPTransfersLine[] Returns an array of ERPTransfersLine objects
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
    public function findOneBySomeField($value): ?ERPTransfersLine
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
