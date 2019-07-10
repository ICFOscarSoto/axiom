<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleAgents;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleAgents|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleAgents|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleAgents[]    findAll()
 * @method GlobaleAgents[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleAgentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleAgents::class);
    }

    // /**
    //  * @return GlobaleAgents[] Returns an array of GlobaleAgents objects
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
    public function findOneBySomeField($value): ?GlobaleAgents
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
