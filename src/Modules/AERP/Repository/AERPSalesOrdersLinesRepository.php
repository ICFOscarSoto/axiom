<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesOrdersLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPSalesOrdersLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesOrdersLines::class);
    }

}
