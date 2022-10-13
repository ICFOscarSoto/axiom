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

    public function findDeadMachines(){

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

    public function getLoadsList($vendingmachine, $date) {
      $query='SELECT productcode, productname, quantity
              FROM erpstores_managers_vending_machines_channels_replenishment
              WHERE channel_id IN
	                     (SELECT id
	                     FROM erpstores_managers_vending_machines_channels
	                     WHERE vendingmachine_id=:vendingmachine)
              AND DATE(dateadd)=:date';
      $params=['vendingmachine' => $vendingmachine, 'date' => $date];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getConnectionLostVendingMachines(){
      $query='SELECT id, name FROM erpstores_managers_vending_machines
                WHERE lastcheck <= DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND connectionlostnotified = 0 AND active = 1 AND deleted = 0';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }
}
