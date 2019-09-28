<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AERPContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method AERPContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method AERPContact[]    findAll()
 * @method AERPContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPContactRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPContact::class);
    }

    // /**
    //  * @return AERPContact[] Returns an array of AERPContact objects
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
    public function findOneBySomeField($value): ?AERPContact
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
