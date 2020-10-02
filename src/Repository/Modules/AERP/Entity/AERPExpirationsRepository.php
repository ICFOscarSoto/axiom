<?php
namespace App\Repository\Modules\AERP\Entity;

use App\Modules\AERP\Entity\AERPExpirations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AERPExpirationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPExpirations::class);
    }

}
