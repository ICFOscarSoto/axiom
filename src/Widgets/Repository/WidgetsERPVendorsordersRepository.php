<?php

namespace App\Widgets\Repository;

use App\Widgets\Entity\WidgetsERPVendorsorders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WidgetsERPVendorsOrders|null find($id, $lockMode = null, $lockVersion = null)
 * @method WidgetsERPVendorsOrders|null findOneBy(array $criteria, array $orderBy = null)
 * @method WidgetsERPVendorsOrders[]    findAll()
 * @method WidgetsERPVendorsOrders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WidgetsERPVendorsordersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WidgetsERPVendorsorders::class);
    }

    public function getOrdersbyVendor($company, $start, $end){
      $query="SELECT u.id, u.NAME, IFNULL(u.lastname,'') lastname, ROUND(SUM(o.totalbase),2) total, ROUND(SUM(o.totalbase-o.cost),2) benefit  FROM erpsales_orders o
              LEFT JOIN globale_users u ON u.id=o.agent_id
              WHERE STATUS=1 AND o.DATE >= :START AND o.DATE<=:END
              AND u.company_id=:COMPANY AND o.shipmentdate IS NOT NULL
              GROUP BY(agent_id) ORDER BY u.NAME, u.lastname";

      $params=['START' => $start,
               'END' => $end,
               'COMPANY' => $company->getId()
               ];

      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
    }

    public function getBudgetsbyVendor($company, $start, $end){
      $query="SELECT u.id, u.NAME, IFNULL(u.lastname,'') lastname, ROUND(SUM(o.totalbase),2) total, ROUND(SUM(o.totalbase-o.cost),2) benefit  FROM erpsales_budgets o
              LEFT JOIN globale_users u ON u.id=o.agent_id
              WHERE STATUS=1 AND o.DATE >= :START AND o.DATE<=:END
              AND u.company_id=:COMPANY
              GROUP BY(agent_id) ORDER BY u.NAME, u.lastname";

      $params=['START' => $start,
               'END' => $end,
               'COMPANY' => $company->getId()
               ];

      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
    }

    public function getBudgetsbyVendorAndMonth($company, $vendor, $start, $end){
      $query="SELECT u.id, u.NAME, IFNULL(u.lastname,'') lastname, ROUND(SUM(o.totalbase),2) total, ROUND(SUM(o.totalbase-o.cost),2) benefit  FROM erpsales_budgets o
              LEFT JOIN globale_users u ON u.id=o.agent_id
              WHERE STATUS=1 AND o.DATE >= :START AND o.DATE<=:END
              AND u.company_id=:COMPANY
              GROUP BY(agent_id) ORDER BY u.NAME, u.lastname";

      $params=['START' => $start,
               'END' => $end,
               'COMPANY' => $company->getId()
               ];

      $result=$this->getEntityManager()->getConnection()->executeQuery($query,$params)->fetchAll();
      return $result;
    }

}
