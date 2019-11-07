<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesDeliveryNotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class AERPSalesDeliveryNotesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesDeliveryNotes::class);
    }

    public function getNextNum($company, $financialyear, $serie)
    {
      $query="SELECT IFNULL(MAX(number)+1,1) AS number FROM aerpsales_delivery_notes WHERE company_id=:company AND serie_id=:serie AND financialyear_id=:financialyear";
      $params=['company' => $company, 'serie' => $serie, 'financialyear' => $financialyear];
      $code=$this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
      return $code;
    }

}
