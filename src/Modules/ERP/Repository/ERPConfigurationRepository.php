<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ERPConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPConfiguration::class);
    }
}
