<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPPaymentMethods;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPPaymentMethodsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPPaymentMethods::class);
    }

}
