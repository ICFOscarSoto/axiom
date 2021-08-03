<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPSATWarrantiesStates;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPSATWarrantiesStates|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPSATWarrantiesStates|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPSATWarrantiesStates[]    findAll()
 * @method ERPSATWarrantiesStates[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPSATWarrantiesStatesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSATWarrantiesStates::class);
    }

    // /**
    //  * @return ERPSATWarrantiesStates[] Returns an array of ERPSATWarrantiesStates objects
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
    public function findOneBySomeField($value): ?ERPSATWarrantiesStates
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
