<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRJobs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRJobs|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRJobs|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRJobs[]    findAll()
 * @method HRJobs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRJobsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRJobs::class);
    }

    // /**
    //  * @return HRJobs[] Returns an array of HRJobs objects
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
    public function findOneBySomeField($value): ?HRJobs
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
