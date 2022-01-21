<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRClocksDiary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRClocksDiary|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRClocksDiary|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRClocksDiary[]    findAll()
 * @method HRClocksDiary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRClocksDiaryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRClocksDiary::class);
    }

    // /**
    //  * @return HRClocksDiary[] Returns an array of HRClocksDiary objects
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
    public function findOneBySomeField($value): ?HRClocksDiary
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
