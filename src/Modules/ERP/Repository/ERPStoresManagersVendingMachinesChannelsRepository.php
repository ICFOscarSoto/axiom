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

    public function getLacks($vendingmachine){
      $query='SELECT id, name, productname, (quantity-minquantity) as lacks, quantity, minquantity, maxquantity
      FROM erpstores_managers_vending_machines_channels
      WHERE vendingmachine_id=:vendingmachine AND quantity<minquantity';
      $params=['vendingmachine' => $vendingmachine->getId()];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


    public function getChannels($array_ids)
    {
      $query="SELECT name, channel, productname, quantity, (quantity-minquantity) as lacks, minquantity, maxquantity
              	FROM erpstores_managers_vending_machines_channels
              	WHERE active = 1 AND deleted= 0
                AND id IN ($array_ids)";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }
}
