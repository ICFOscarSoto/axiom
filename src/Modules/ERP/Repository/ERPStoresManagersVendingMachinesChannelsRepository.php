<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersVendingMachinesChannels|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersVendingMachinesChannels|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersVendingMachinesChannels[]    findAll()
 * @method ERPStoresManagersVendingMachinesChannels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersVendingMachinesChannelsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersVendingMachinesChannels::class);
    }

    // /**
    //  * @return ERPStoresManagersVendingMachinesChannels[] Returns an array of ERPStoresManagersVendingMachinesChannels objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersVendingMachinesChannels
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
