<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRClocks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRClocks|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRClocks|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRClocks[]    findAll()
 * @method HRClocks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRClocksRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRClocks::class);
    }

    // /**
    //  * @return HRClocks[] Returns an array of HRClocks objects
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
    public function findOneBySomeField($value): ?HRClocks
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
