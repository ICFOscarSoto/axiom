<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleUsersWidgets;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleUsersWidgets|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleUsersWidgets|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleUsersWidgets[]    findAll()
 * @method GlobaleUsersWidgets[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUsersWidgetsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUsersWidgets::class);
    }

}
