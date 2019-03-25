<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPVariantsGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPVariantsGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPVariantsGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPVariantsGroups[]    findAll()
 * @method ERPVariantsGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPVariantsGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPVariantsGroups::class);
    }

    // /**
    //  * @return ERPVariantsGroups[] Returns an array of ERPVariantsGroups objects
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
    public function findOneBySomeField($value): ?ERPVariantsGroups
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
