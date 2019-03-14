<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPManufacturers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPManufacturers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPManufacturers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPManufacturers[]    findAll()
 * @method ERPManufacturers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPManufacturersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPManufacturers::class);
    }

    // /**
    //  * @return ERPManufacturers[] Returns an array of ERPManufacturers objects
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
    public function findOneBySomeField($value): ?ERPManufacturers
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
