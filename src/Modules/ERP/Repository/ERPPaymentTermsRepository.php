<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPPaymentTerms;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPPaymentTerms|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPPaymentTerms|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPPaymentTerms[]    findAll()
 * @method ERPPaymentTerms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPPaymentTermsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPPaymentTerms::class);
    }

    // /**
    //  * @return ERPPaymentTerms[] Returns an array of ERPPaymentTerms objects
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
    public function findOneBySomeField($value): ?ERPPaymentTerms
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
