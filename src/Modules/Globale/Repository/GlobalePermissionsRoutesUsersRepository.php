<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobalePermissionsRoutesUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GlobalePermissionsRoutesUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method GlobalePermissionsRoutesUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method GlobalePermissionsRoutesUsers[]    findAll()
 * @method GlobalePermissionsRoutesUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobalePermissionsRoutesUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobalePermissionsRoutesUsers::class);
    }

    public function findByUserModule($user, $module){
      $query="SELECT m.NAME module, r.name, r.description, r.module_id, IFNULL(ru.allowaccess,3) allowaccess FROM globale_permissions_routes_users ru
              RIGHT JOIN globale_permissions_routes r ON r.id=ru.permissionroute_id AND ru.user_id=:val_user
              LEFT JOIN globale_modules m ON m.id=r.module_id
              WHERE r.module_id=:val_module
              ";
      $params=['val_user' => $user, 'val_module' => $module];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }


}
