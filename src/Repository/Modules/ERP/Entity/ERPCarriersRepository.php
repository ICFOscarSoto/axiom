<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPCarriers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCarriers|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCarriers|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCarriers[]    findAll()
 * @method ERPCarriers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPCarriersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPCarriers::class);
    }

    // /**
    //  * @return ERPCarriers[] Returns an array of ERPCarriers objects
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
    public function findOneBySomeField($value): ?ERPCarriers
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
