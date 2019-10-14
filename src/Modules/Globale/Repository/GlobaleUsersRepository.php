<?php

namespace App\Modules\Globale\Repository;

use App\Modules\Globale\Entity\GlobaleUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GlobaleUsersRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GlobaleUsers::class);
    }

    public function getShareables($user, $query=""){
      return $this->createQueryBuilder('u')
          ->andWhere('u.company = :company')
          ->andWhere('u.active = :active')
          ->andWhere('u.deleted = :deleted')
          ->andWhere('u.id <> :user')
          ->andWhere('u.id <> :user')
          ->andWhere('u.name LIKE :query OR u.firstname LIKE :query OR u.email LIKE :query')
          ->setParameter('company', $user->getCompany())
          ->setParameter('active', true)
          ->setParameter('deleted', false)
          ->setParameter('user', $user->getId())
          ->setParameter('query', '%'.$query.'%')
          ->orderBy('u.id', 'ASC')
          ->getQuery()
          ->getResult()
      ;
    }

    function getNoUsers($user){
      return $this->createQueryBuilder('h')
          ->from('worker', 'w')
          ->leftJoin('u.user', 'u')
          ->where('w.user = :userId')
          ->setParameters(array(':userId' => null))
          ->getQuery()
          ->getResult();
    }

    public function isAdmin($user){
      $query="SELECT IF(COUNT(*)=0,0,1) admin FROM globale_user_groups g
              LEFT JOIN globale_users_user_groups ug ON ug.usergroup_id=g.id
              LEFT JOIN globale_users u ON u.id=ug.user_id
              WHERE u.id=:val_user AND g.isadmin=1 AND g.active=1 AND g.deleted=0 AND ug.active=1 AND ug.deleted=0";
      $params=['val_user' => $user];
      return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchColumn(0);
    }


    public function getAllowedRoutes($user){
      //Detect if user belongs to any admin group
      $isadmin=$this->isAdmin($user);
      if($isadmin){
        //Admin user, get all routes
        $query="SELECT r.name FROM globale_permissions_routes r
                LEFT JOIN globale_modules m ON m.id=r.module_id
                LEFT JOIN globale_companies_modules cm ON cm.module_id=m.id
                LEFT JOIN globale_companies c ON c.id=cm.companyown_id
                LEFT JOIN globale_users u ON u.company_id=c.id
                WHERE u.id=:val_user
                AND r.active=1 AND r.deleted=0
                AND m.active=1 AND m.deleted=0
                AND cm.active=1 AND cm.deleted=0
                AND c.active=1 AND c.deleted=0
                AND u.active=1 AND u.deleted=0
                UNION
                SELECT r.NAME FROM globale_permissions_routes r
                LEFT JOIN globale_modules m ON m.id=r.module_id
                WHERE m.NAME=\"Globale\"";
      }else{
        //if no user admin get allowed routes
        $query="SELECT r.name FROM globale_permissions_routes_users ru
                  LEFT JOIN globale_permissions_routes r ON r.id=ru.permissionroute_id
                  WHERE ru.allowaccess=1 AND ru.user_id=:val_user AND ru.active=1 AND ru.deleted=0 AND r.active=1 AND r.deleted=0
                UNION
                SELECT r.name FROM globale_permissions_routes_user_groups rg
                  LEFT JOIN globale_permissions_routes r ON r.id=rg.permissionroute_id
                  WHERE rg.allowaccess=1 AND rg.active=1 AND rg.deleted=0 AND r.active=1 AND r.deleted=0
                        AND r.id NOT IN (SELECT r.id FROM globale_permissions_routes_users ru
                                         LEFT JOIN globale_permissions_routes r ON r.id=ru.permissionroute_id
                                         WHERE ru.allowaccess=0 AND ru.user_id=:val_user)
                        AND rg.usergroup_id IN (SELECT g.id FROM globale_users_user_groups ug
                                                LEFT JOIN globale_user_groups g ON g.id=ug.usergroup_id
                                                WHERE ug.user_id=:val_user AND g.active=1 AND g.deleted=0 AND ug.active=1 AND ug.deleted=0)";
      }
      $params=['val_user' => $user];
      return array_column($this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll(),'name');
    }

    public function hasPermission($user, $route){
      $allowedRoutes=$this->getAllowedRoutes($user);
      if(in_array($route, $allowedRoutes)) return true; else return false;
    }
}
