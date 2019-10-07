<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPAttributeNames;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPAttributeNames|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPAttributeNames|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPAttributeNames[]    findAll()
 * @method ERPAttributeNames[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPAttributeNamesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPAttributeNames::class);
    }

    // /**
    //  * @return ERPAttributeNames[] Returns an array of ERPAttributeNames objects
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
    public function findOneBySomeField($value): ?ERPAttributeNames
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
