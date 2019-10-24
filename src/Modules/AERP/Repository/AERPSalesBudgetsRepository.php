<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesBudgets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class AERPSalesBudgetsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesBudgets::class);
    }

    public function getNextNum($company)
    {
      $query="SELECT IFNULL(MAX(number)+1,1) AS number FROM aerpsales_budgets WHERE company_id=:company";
      $params=['company' => $company];
      $code=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $code;
    }

}
