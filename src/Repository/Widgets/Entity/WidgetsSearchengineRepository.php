<?php

namespace App\Repository\Widgets\Entity;

use App\Widgets\Entity\WidgetsSearchengine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WidgetsSearchengine|null find($id, $lockMode = null, $lockVersion = null)
 * @method WidgetsSearchengine|null findOneBy(array $criteria, array $orderBy = null)
 * @method WidgetsSearchengine[]    findAll()
 * @method WidgetsSearchengine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WidgetsSearchengineRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WidgetsSearchengine::class);
    }

    // /**
    //  * @return WidgetsSearchengine[] Returns an array of WidgetsSearchengine objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WidgetsSearchengine
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
