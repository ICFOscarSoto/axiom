<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSalesOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class ERPSalesOrdersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSalesOrders::class);
    }

    public function getNextNum($company, $financialyear, $serie)
    {
      $query="SELECT IFNULL(MAX(number)+1,1) AS number FROM erpsales_orders WHERE company_id=:company AND serie_id=:serie AND financialyear_id=:financialyear AND active=1 AND deleted=0";
      $params=['company' => $company, 'serie' => $serie, 'financialyear' => $financialyear];
      $code=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $code;
    }

    public function findNews()
    {
      $query="SELECT id FROM erpsales_orders WHERE dateadd>='2020-11-14' AND dateadd<'2020-11-27'";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;

    }

    public function getOrdersWithExternalNumber(){
      $query="SELECT s.id, s.code, s.externalordernumber, s.date, s.customername, s.customercode, a.name
      FROM erpsales_orders s
      LEFT JOIN globale_users a ON a.id=s.agent_id
      WHERE s.externalordernumber!=NULL";
      $result=$this->getEntityManager()->getConnection()->executeQuery($query)->fetchAll();
      return $result;
    }


}
