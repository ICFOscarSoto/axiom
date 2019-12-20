<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPFinancialYears;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ERPFinancialYearsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPFinancialYears::class);
    }
}
