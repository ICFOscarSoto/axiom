<?php

namespace App\Repository\Modules\ERP\Entiti;

use App\Modules\ERP\Entiti\ERPShoppingDiscounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPShoppingDiscounts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPShoppingDiscounts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPShoppingDiscounts[]    findAll()
 * @method ERPShoppingDiscounts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPShoppingDiscountsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPShoppingDiscounts::class);
    }

    // /**
    //  * @return ERPShoppingDiscounts[] Returns an array of ERPShoppingDiscounts objects
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
    public function findOneBySomeField($value): ?ERPShoppingDiscounts
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
