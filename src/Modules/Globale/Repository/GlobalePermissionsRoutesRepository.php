<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobalePermissionsRoutes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalePermissionsRoutes|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalePermissionsRoutes|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalePermissionsRoutes[]    findAll()
 * @method GlobalePermissionsRoutes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalePermissionsRoutesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalePermissionsRoutes::class);
    }
}
