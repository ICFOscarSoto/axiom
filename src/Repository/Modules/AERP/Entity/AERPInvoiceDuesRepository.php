<?php

namespace App\Repository\Modules\AERP\Entity;

use App\Modules\AERP\Entity\AERPInvoiceDues;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPInvoiceDues|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPInvoiceDues|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPInvoiceDues[]    findAll()
 * @method AERPInvoiceDues[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPInvoiceDuesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPInvoiceDues::class);
    }

    // /**
    //  * @return AERPInvoiceDues[] Returns an array of AERPInvoiceDues objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AERPInvoiceDues
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
