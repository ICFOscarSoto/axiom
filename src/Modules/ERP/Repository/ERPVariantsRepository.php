<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPVariants;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPVariants|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPVariants|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPVariants[]    findAll()
 * @method ERPVariants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPVariantsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPVariants::class);
    }

    // /**
    //  * @return ERPVariants[] Returns an array of ERPVariants objects
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
    public function findOneBySomeField($value): ?ERPVariants
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
