<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPCustomerGroupsPrices;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPCustomerGroupsPricesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPCustomerGroupsPrices::class);
    }
}
