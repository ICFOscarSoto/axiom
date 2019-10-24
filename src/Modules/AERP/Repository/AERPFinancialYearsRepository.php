<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPFinancialYears;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPFinancialYearsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPFinancialYears::class);
    }
}
