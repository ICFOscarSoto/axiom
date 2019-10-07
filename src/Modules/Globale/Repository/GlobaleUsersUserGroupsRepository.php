<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleUsersUserGroups;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobaleUsersUserGroups|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobaleUsersUserGroups|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobaleUsersUserGroups[]    findAll()
 * @method GlobaleUsersUserGroups[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUsersUserGroupsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUsersUserGroups::class);
    }
}
