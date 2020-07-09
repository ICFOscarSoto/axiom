<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPSeries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ERPSeriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPSeries::class);
    }
}
