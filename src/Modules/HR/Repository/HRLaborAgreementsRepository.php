<?php

namespace App\Modules\HR\Repository;

use App\Modules\HR\Entity\HRLaborAgreements;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method HRLaborAgreement|null find($id, $lockMode = null, $lockVersion = null)
 * @method HRLaborAgreement|null findOneBy(array $criteria, array $orderBy = null)
 * @method HRLaborAgreement[]    findAll()
 * @method HRLaborAgreement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HRLaborAgreementsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, HRLaborAgreements::class);
    }

    // /**
    //  * @return HRLaborAgreement[] Returns an array of HRLaborAgreement objects
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
    public function findOneBySomeField($value): ?HRLaborAgreement
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
