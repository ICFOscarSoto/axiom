<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleHistories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleHistories|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleHistories|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleHistories[]    findAll()
 * @method GlobaleHistories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleHistoriesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleHistories::class);
    }


}
