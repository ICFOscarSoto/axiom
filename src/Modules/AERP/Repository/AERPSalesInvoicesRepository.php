<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesInvoices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class AERPSalesInvoicesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesInvoices::class);
    }

    public function getNextNum($company)
    {
      $query="SELECT IFNULL(MAX(number)+1,1) AS number FROM aerpsales_invoices WHERE company_id=:company";
      $params=['company' => $company];
      $code=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $code;
    }

}
