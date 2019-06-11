<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPReferences;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPReferences|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPReferences|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPReferences[]    findAll()
 * @method ERPReferences[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPReferencesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPReferences::class);
    }

    // /**
    //  * @return ERPReferences[] Returns an array of ERPReferences objects
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
    public function findOneBySomeField($value): ?ERPReferences
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
