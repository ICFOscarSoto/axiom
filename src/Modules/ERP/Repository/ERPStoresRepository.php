<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStores|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStores|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStores[]    findAll()
 * @method ERPStores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStores::class);
    }

    // /**
    //  * @return ERPStores[] Returns an array of ERPStores objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ERPStores
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}