<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersVendingMachines|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersVendingMachines|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersVendingMachines[]    findAll()
 * @method ERPStoresManagersVendingMachines[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersVendingMachinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersVendingMachines::class);
    }

    // /**
    //  * @return ERPStoresManagersVendingMachines[] Returns an array of ERPStoresManagersVendingMachines objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersVendingMachines
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
