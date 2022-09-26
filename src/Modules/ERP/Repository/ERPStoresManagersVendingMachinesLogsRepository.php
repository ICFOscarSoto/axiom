<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesLogs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersVendingMachinesLogs|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersVendingMachinesLogs|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersVendingMachinesLogs[]    findAll()
 * @method ERPStoresManagersVendingMachinesLogs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersVendingMachinesLogsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersVendingMachinesLogs::class);
    }

    // /**
    //  * @return ERPStoresManagersVendingMachinesLogs[] Returns an array of ERPStoresManagersVendingMachinesLogs objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersVendingMachinesLogs
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
