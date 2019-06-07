<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRSickleaves;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRSickleaves|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRSickleaves|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRSickleaves[]    findAll()
 * @method HRSickleaves[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRSickleavesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRSickleaves::class);
    }

    // /**
    //  * @return HRSickleaves[] Returns an array of HRSickleaves objects
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
    public function findOneBySomeField($value): ?HRSickleaves
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
