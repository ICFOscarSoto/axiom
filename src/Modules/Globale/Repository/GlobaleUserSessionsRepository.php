<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleUserSessions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleUserSessions|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleUserSessions|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleUserSessions[]    findAll()
 * @method GlobaleUserSessions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUserSessionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUserSessions::class);
    }

    // /**
    //  * @return GlobaleUserSessions[] Returns an array of GlobaleUserSessions objects
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
    public function findOneBySomeField($value): ?GlobaleUserSessions
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
