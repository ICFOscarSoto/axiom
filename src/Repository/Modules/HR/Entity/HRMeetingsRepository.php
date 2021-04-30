<?php

namespace App\Repository\Modules\HR\Entity;

use App\Modules\HR\Entity\HRMeetings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRMeetings|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRMeetings|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRMeetings[]    findAll()
 * @method HRMeetings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRMeetingsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRMeetings::class);
    }

    // /**
    //  * @return HRMeetings[] Returns an array of HRMeetings objects
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
    public function findOneBySomeField($value): ?HRMeetings
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
