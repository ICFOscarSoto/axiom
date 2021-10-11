<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPBuyOrdersLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyOrdersLines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyOrdersLines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyOrdersLines[]    findAll()
 * @method ERPBuyOrdersLines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyOrdersLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyOrdersLines::class);
    }

    // /**
    //  * @return ERPBuyOrdersLines[] Returns an array of ERPBuyOrdersLines objects
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
    public function findOneBySomeField($value): ?ERPBuyOrdersLines
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
