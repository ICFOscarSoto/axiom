<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSeries;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPSeriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSeries::class);
    }
}
