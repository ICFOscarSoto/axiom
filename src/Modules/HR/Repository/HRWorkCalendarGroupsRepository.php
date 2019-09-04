<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRWorkCalendarGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRWorkCalendarGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRWorkCalendarGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRWorkCalendarGroups[]    findAll()
 * @method HRWorkCalendarGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRWorkCalendarGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRWorkCalendarGroups::class);
    }

    // /**
    //  * @return HRWorkCalendarGroups[] Returns an array of HRWorkCalendarGroups objects
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
    public function findOneBySomeField($value): ?HRWorkCalendarGroups
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
