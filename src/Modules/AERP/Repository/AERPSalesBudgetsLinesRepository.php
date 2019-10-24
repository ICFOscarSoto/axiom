<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesBudgetsLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPSalesBudgetsLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesBudgetsLines::class);
    }

}
