<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRWorkers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorker|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorker|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorker[]    findAll()
 * @method HRWorker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRWorkers::class);
    }

    // /**
    //  * @return HRWorker[] Returns an array of HRWorker objects
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
    public function findOneBySomeField($value): ?HRWorker
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
