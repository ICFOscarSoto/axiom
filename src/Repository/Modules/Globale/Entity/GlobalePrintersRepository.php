<?php

namespace App\Repository\Modules\Globale\Entity;

use App\Modules\Globale\Entity\GlobalePrinters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalePrinters|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalePrinters|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalePrinters[]    findAll()
 * @method GlobalePrinters[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalePrintersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalePrinters::class);
    }

    // /**
    //  * @return GlobalePrinters[] Returns an array of GlobalePrinters objects
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
    public function findOneBySomeField($value): ?GlobalePrinters
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
