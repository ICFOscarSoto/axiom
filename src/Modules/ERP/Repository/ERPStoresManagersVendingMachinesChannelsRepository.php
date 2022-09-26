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
      $query='SELECT id, name, productname, -(quantity-minquantity) as lacks, quantity, minquantity, maxquantity
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

    public function getLoadsMachine($id){
      $query='SELECT date(dateadd) as date FROM erpstores_managers_vending_machines_channels_replenishment WHERE
              channel_id IN (SELECT id FROM erpstores_managers_vending_machines_channels WHERE vendingmachine_id=:vendingmachine)
              GROUP BY date(dateadd)
              ORDER by date(dateadd) DESC
              LIMIT 10';
      $params=['vendingmachine' => $id];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


    public function getLoadsMachineDate($id,$date){
      $query='SELECT cr.productcode, cr.productname, cr.quantity,  cr.quantity/vc.multiplier AS upload,  vc.multiplier
              FROM erpstores_managers_vending_machines_channels_replenishment cr, erpstores_managers_vending_machines_channels vc
              WHERE date(cr.dateadd)=:date
              AND cr.channel_id  IN (SELECT id FROM erpstores_managers_vending_machines_channels WHERE vendingmachine_id=:vendingmachine)
              AND cr.channel_id=vc.id';
      $params=['vendingmachine' => $id, 'date'=>$date];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }
}
