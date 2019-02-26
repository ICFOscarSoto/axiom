<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPDepartments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Departments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Departments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Departments[]    findAll()
 * @method Departments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPDepartmentsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPDepartments::class);
    }

    // /**
    //  * @return Departments[] Returns an array of Departments objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Departments
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
