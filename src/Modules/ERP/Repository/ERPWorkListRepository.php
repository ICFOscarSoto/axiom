<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPWorkList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPWorkList|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPWorkList|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPWorkList[]    findAll()
 * @method ERPWorkList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPWorkListRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPWorkList::class);
    }

    // /**
    //  * @return ERPWorkList[] Returns an array of ERPWorkList objects
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
    public function findOneBySomeField($value): ?ERPWorkList
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
