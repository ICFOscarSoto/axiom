<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesDeliveryNotesLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPSalesDeliveryNotesLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesDeliveryNotesLines::class);
    }

}
