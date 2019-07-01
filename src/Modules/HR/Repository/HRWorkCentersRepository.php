<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRWorkCenters;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorkCenter|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorkCenter|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorkCenter[]    findAll()
 * @method HRWorkCenter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkCentersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRWorkCenters::class);
    }

    // /**
    //  * @return HRWorkCenter[] Returns an array of HRWorkCenter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HRWorkCenter
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
