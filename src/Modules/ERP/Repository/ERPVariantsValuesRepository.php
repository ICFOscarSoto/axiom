<?php

namespace App\Modules\ERP\Repository;

use App\ERP\Entity\Modules\ERPVariantsValues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPVariantsValues|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPVariantsValues|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPVariantsValues[]    findAll()
 * @method ERPVariantsValues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPVariantsValuesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPVariantsValues::class);
    }

    // /**
    //  * @return ERPVariantsValues[] Returns an array of ERPVariantsValues objects
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
    public function findOneBySomeField($value): ?ERPVariantsValues
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
