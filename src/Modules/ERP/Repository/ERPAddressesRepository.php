<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPAddresses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPAddresses|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPAddresses|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPAddresses[]    findAll()
 * @method ERPAddresses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPAddressesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPAddresses::class);
    }

    // /**
    //  * @return ERPAddresses[] Returns an array of ERPAddresses objects
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
    public function findOneBySomeField($value): ?ERPAddresses
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
