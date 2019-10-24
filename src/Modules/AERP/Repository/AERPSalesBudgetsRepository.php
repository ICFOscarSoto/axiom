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

}
