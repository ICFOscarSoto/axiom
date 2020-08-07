<?php

namespace App\Repository\Modules\Globale\Entity;

use App\Modules\Globale\Entity\GlobaleWorkstations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleWorkstations|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleWorkstations|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleWorkstations[]    findAll()
 * @method GlobaleWorkstations[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleWorkstationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleWorkstations::class);
    }

    // /**
    //  * @return GlobaleWorkstations[] Returns an array of GlobaleWorkstations objects
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
    public function findOneBySomeField($value): ?GlobaleWorkstations
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
