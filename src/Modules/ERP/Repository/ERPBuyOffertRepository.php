<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyOffert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyOffert|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyOffert|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyOffert[]    findAll()
 * @method ERPBuyOffert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyOffertRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyOffert::class);
    }

    // /**
    //  * @return ERPBuyOffert[] Returns an array of ERPBuyOffert objects
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
    public function findOneBySomeField($value): ?ERPBuyOffert
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
