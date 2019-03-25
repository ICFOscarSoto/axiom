<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPProductVariants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPProductVariants|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPProductVariants|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPProductVariants[]    findAll()
 * @method ERPProductVariants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPProductVariantsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPProductVariants::class);
    }

    // /**
    //  * @return ERPProductVariants[] Returns an array of ERPProductVariants objects
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
    public function findOneBySomeField($value): ?ERPProductVariants
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
