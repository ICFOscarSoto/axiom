<?php

namespace App\Repository\Modules\Navision\Entity;

use App\Modules\Navision\Entity\NavisionSync;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method NavisionSync|null find($id, $lockMode = null, $lockVersion = null)
 * @method NavisionSync|null findOneBy(array $criteria, array $orderBy = null)
 * @method NavisionSync[]    findAll()
 * @method NavisionSync[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NavisionSyncRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, NavisionSync::class);
    }

    // /**
    //  * @return NavisionSync[] Returns an array of NavisionSync objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NavisionSync
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
