<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobalePermissionsZones;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalePermissionsZones|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalePermissionsZones|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalePermissionsZones[]    findAll()
 * @method GlobalePermissionsZones[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalePermissionsZonesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalePermissionsZones::class);
    }


}
