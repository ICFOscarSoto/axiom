<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRShifts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRShifts|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRShifts|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRShifts[]    findAll()
 * @method HRShifts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRShiftsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRShifts::class);
    }

    // /**
    //  * @return HRShifts[] Returns an array of HRShifts objects
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
    public function findOneBySomeField($value): ?HRShifts
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
