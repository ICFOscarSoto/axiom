<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRCourses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRCourse|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRCourse|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRCourse[]    findAll()
 * @method HRCourse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRCoursesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRCourses::class);
    }

    // /**
    //  * @return HRCourse[] Returns an array of HRCourse objects
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
    public function findOneBySomeField($value): ?HRCourse
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
