<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRDepartments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRDepartments|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRDepartments|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRDepartments[]    findAll()
 * @method HRDepartments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRDepartmentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRDepartments::class);
    }

    // /**
    //  * @return HRDepartments[] Returns an array of HRDepartments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('h.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HRDepartments
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
