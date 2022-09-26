<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPVariantsTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPVariantsTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPVariantsTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPVariantsTypes[]    findAll()
 * @method ERPVariantsTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPVariantsTypesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPVariantsTypes::class);
    }

    // /**
    //  * @return ERPVariantsTypes[] Returns an array of ERPVariantsTypes objects
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
    public function findOneBySomeField($value): ?ERPVariantsTypes
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
