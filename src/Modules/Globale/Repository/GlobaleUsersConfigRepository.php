<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleUsersConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleUsersConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleUsersConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleUsersConfig[]    findAll()
 * @method GlobaleUsersConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUsersConfigRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUsersConfig::class);
    }

    // /**
    //  * @return GlobaleUsersConfig[] Returns an array of GlobaleUsersConfig objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobaleUsersConfig
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
