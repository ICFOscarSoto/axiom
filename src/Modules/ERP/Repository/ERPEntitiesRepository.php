<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPEntities;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPEntities|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPEntities|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPEntities[]    findAll()
 * @method ERPEntities[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPEntitiesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPEntities::class);
    }

    // /**
    //  * @return ERPEntities[] Returns an array of ERPEntities objects
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
    public function findOneBySomeField($value): ?ERPEntities
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
