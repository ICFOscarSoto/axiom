<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPStoreTickets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPStoreTickets|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPStoreTickets|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPStoreTickets[]    findAll()
 * @method ERPStoreTickets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPStoreTicketsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPStoreTickets::class);
    }

    // /**
    //  * @return ERPStoreTickets[] Returns an array of ERPStoreTickets objects
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
    public function findOneBySomeField($value): ?ERPStoreTickets
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getNotificationsTickets(){
      $query="SELECT et.agent_id, et.department_id,et.id, CONCAT(ets.notifytime,\" \", ets.notifyunit) notifyperiod, IFNULL(et.datelastnotify, et.dateupd) datelastnotify,
            	case ets.notifyunit
            		when 'MINUTE' then DATE_ADD(IFNULL(et.datelastnotify, et.dateupd),INTERVAL ets.notifytime MINUTE)
            		when 'HOUR' then DATE_ADD(IFNULL(et.datelastnotify, et.dateupd),INTERVAL ets.notifytime HOUR)
            		when 'DAY' then DATE_ADD(IFNULL(et.datelastnotify, et.dateupd),INTERVAL ets.notifytime DAY)
            		when 'WEEK' then DATE_ADD(IFNULL(et.datelastnotify, et.dateupd),INTERVAL ets.notifytime WEEK)
            		when 'MONTH' then DATE_ADD(IFNULL(et.datelastnotify, et.dateupd),INTERVAL ets.notifytime MONTH)
            		when 'YEAR' then DATE_ADD(IFNULL(et.datelastnotify, et.dateupd),INTERVAL ets.notifytime YEAR)
            	end as datenotify
            FROM erpstore_tickets et
            LEFT JOIN erpstore_tickets_states ets ON ets.id = et.storeticketstate_id
            LEFT JOIN globale_users gu ON gu.id = et.agent_id
            LEFT JOIN hrdepartments h ON h.id = et.department_id
            WHERE et.active = 1 AND et.deleted = 0 AND et.reason_id != 1
            HAVING datenotify<=NOW()";
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
    }


    public function getTicketsforInventory($store){
      $query="SELECT *
            FROM erpstore_tickets et
            WHERE et.active = 1 AND et.deleted = 0 AND et.storeticketstate_id!=2 AND et.reason_id=1 AND et.store_id=:STORE";
      $params=['STORE' => $store];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function getLastID(){
      $query='SELECT max(id)
      FROM erpstore_tickets';
      return $this->getEntityManager()->getConnection()->executeQuery($query)->fetchColumn(0);

    }
}
