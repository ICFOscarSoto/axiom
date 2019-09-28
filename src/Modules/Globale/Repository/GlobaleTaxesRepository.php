<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleTaxes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleTaxes|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleTaxes|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleTaxes[]    findAll()
 * @method GlobaleTaxes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleTaxesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleTaxes::class);
    }

}
