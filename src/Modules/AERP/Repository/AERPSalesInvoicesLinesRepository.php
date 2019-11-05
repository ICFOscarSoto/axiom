<?php

namespace App\Modules\AERP\Repository;

use App\Modules\AERP\Entity\AERPSalesInvoicesLines;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPSalesInvoicesLinesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPSalesInvoicesLines::class);
    }

}
