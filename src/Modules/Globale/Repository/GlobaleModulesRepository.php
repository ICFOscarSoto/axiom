<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleModules;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleModules|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleModules|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleModules[]    findAll()
 * @method GlobaleModules[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleModulesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleModules::class);
    }

    // /**
    //  * @return GlobaleModules[] Returns an array of GlobaleModules objects
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
    public function findOneBySomeField($value): ?GlobaleModules
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
