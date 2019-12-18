<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPPlazaVouchers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPlazaVouchers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPlazaVouchers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPlazaVouchers[]    findAll()
 * @method ERPPlazaVouchers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPlazaVouchersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPlazaVouchers::class);
    }

    // /**
    //  * @return ERPPlazaVouchers[] Returns an array of ERPPlazaVouchers objects
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
    public function findOneBySomeField($value): ?ERPPlazaVouchers
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
