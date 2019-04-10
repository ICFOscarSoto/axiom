<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRSchedules;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRSchedules|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRSchedules|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRSchedules[]    findAll()
 * @method HRSchedules[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRSchedulesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRSchedules::class);
    }

    // /**
    //  * @return HRSchedules[] Returns an array of HRSchedules objects
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
    public function findOneBySomeField($value): ?HRSchedules
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
