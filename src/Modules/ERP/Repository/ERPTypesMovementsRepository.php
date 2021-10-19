<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPTypesMovements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPTypesMovements|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPTypesMovements|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPTypesMovements[]    findAll()
 * @method ERPTypesMovements[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPTypesMovementsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPTypesMovements::class);
    }

    // /**
    //  * @return ERPTypesMovements[] Returns an array of ERPTypesMovements objects
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
    public function findOneBySomeField($value): ?ERPTypesMovements
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
