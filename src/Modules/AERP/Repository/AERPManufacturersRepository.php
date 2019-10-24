<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPManufacturers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPManufacturersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPManufacturers::class);
    }
}
