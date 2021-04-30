<?php

namespace App\Repository\Modules\HR\Entity;

use App\Modules\HR\Entity\HRMeetingsSummoneds;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRMeetingsSummoneds|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRMeetingsSummoneds|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRMeetingsSummoneds[]    findAll()
 * @method HRMeetingsSummoneds[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRMeetingsSummonedsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRMeetingsSummoneds::class);
    }

    // /**
    //  * @return HRMeetingsSummoneds[] Returns an array of HRMeetingsSummoneds objects
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
    public function findOneBySomeField($value): ?HRMeetingsSummoneds
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
