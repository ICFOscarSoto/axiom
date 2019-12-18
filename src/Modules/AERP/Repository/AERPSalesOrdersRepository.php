<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesOrders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class AERPSalesOrdersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesOrders::class);
    }

    public function getNextNum($company, $financialyear, $serie)
    {
      $query="SELECT IFNULL(MAX(number)+1,1) AS number FROM aerpsales_orders WHERE company_id=:company AND serie_id=:serie AND financialyear_id=:financialyear AND active=1 AND deleted=0";
      $params=['company' => $company, 'serie' => $serie, 'financialyear' => $financialyear];
      $code=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $code;
    }

}
