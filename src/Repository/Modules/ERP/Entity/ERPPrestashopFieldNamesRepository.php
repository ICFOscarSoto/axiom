<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPPrestashopFieldNames;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPrestashopFieldNames|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPrestashopFieldNames|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPrestashopFieldNames[]    findAll()
 * @method ERPPrestashopFieldNames[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPrestashopFieldNamesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPrestashopFieldNames::class);
    }

    // /**
    //  * @return ERPPrestashopFieldNames[] Returns an array of ERPPrestashopFieldNames objects
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
    public function findOneBySomeField($value): ?ERPPrestashopFieldNames
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
