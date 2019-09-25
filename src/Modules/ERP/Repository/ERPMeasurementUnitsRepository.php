<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPMeasurementUnits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPMeasurementUnits|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPMeasurementUnits|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPMeasurementUnits[]    findAll()
 * @method ERPMeasurementUnits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPMeasurementUnitsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPMeasurementUnits::class);
    }

    // /**
    //  * @return ERPMeasurementUnits[] Returns an array of ERPMeasurementUnits objects
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
    public function findOneBySomeField($value): ?ERPMeasurementUnits
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
