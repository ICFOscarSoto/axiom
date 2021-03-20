<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPMyBackpack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPMyBackpack|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPMyBackpack|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPMyBackpack[]    findAll()
 * @method ERPMyBackpack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPMyBackpackRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPMyBackpack::class);
    }

    // /**
    //  * @return ERPMyBackpack[] Returns an array of ERPMyBackpack objects
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
    public function findOneBySomeField($value): ?ERPMyBackpack
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
