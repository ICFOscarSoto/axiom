<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleQuotationGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleQuotationGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleQuotationGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleQuotationGroups[]    findAll()
 * @method GlobaleQuotationGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleQuotationGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleQuotationGroups::class);
    }

    // /**
    //  * @return GlobaleQuotationGroups[] Returns an array of GlobaleQuotationGroups objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GlobaleQuotationGroups
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
