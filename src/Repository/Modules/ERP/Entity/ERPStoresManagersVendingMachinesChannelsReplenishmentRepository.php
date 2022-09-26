<?php

namespace App\Repository\Modules\ERP\Entity;

use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannelsReplenishment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoresManagersVendingMachinesChannelsReplenishment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoresManagersVendingMachinesChannelsReplenishment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoresManagersVendingMachinesChannelsReplenishment[]    findAll()
 * @method ERPStoresManagersVendingMachinesChannelsReplenishment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoresManagersVendingMachinesChannelsReplenishmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoresManagersVendingMachinesChannelsReplenishment::class);
    }

    // /**
    //  * @return ERPStoresManagersVendingMachinesChannelsReplenishment[] Returns an array of ERPStoresManagersVendingMachinesChannelsReplenishment objects
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
    public function findOneBySomeField($value): ?ERPStoresManagersVendingMachinesChannelsReplenishment
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
