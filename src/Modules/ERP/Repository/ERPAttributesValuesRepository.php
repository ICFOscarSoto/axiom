<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPAttributesValues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPAttributesValues|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPAttributesValues|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPAttributesValues[]    findAll()
 * @method ERPAttributesValues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPAttributesValuesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPAttributesValues::class);
    }

    // /**
    //  * @return ERPAttributesValues[] Returns an array of ERPAttributesValues objects
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
    public function findOneBySomeField($value): ?ERPAttributesValues
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
