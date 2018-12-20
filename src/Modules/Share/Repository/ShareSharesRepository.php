<?php

namespace App\Modules\Share\Repository;

use App\Modules\Share\Entity\ShareShares;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShareShares|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareShares|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareShares[]    findAll()
 * @method ShareShares[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareSharesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShareShares::class);
    }

    // /**
    //  * @return ShareShares[] Returns an array of ShareShares objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShareShares
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
