<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleWidgets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleWidgets|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleWidgets|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleWidgets[]    findAll()
 * @method GlobaleWidgets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleWidgetsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleWidgets::class);
    }

    // /**
    //  * @return GlobaleWidgets[] Returns an array of GlobaleWidgets objects
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
    public function findOneBySomeField($value): ?GlobaleWidgets
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
