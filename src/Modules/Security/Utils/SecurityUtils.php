<?php
namespace App\Modules\Security\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Modules\Globale\Entity\GlobaleModules;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleUserGroups;
use App\Modules\Globale\Entity\GlobaleUsersUserGroups;
use App\Modules\Globale\Entity\GlobalePermissionsZones;
use App\Modules\Globale\Entity\GlobalePermissionsZonesUsers;
use App\Modules\Globale\Entity\GlobalePermissionsZonesUserGroups;
use App\Modules\Globale\Entity\GlobalePermissionsRoutes;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUsers;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUserGroups;


class SecurityUtils
{
  public function checkRoutePermissions($module, $name, $user, $doctrine){
    $modulesRepository=$doctrine->getRepository(GlobaleModules::class);
    $userRepository=$doctrine->getRepository(GlobaleUsers::class);
    $userUsersGroupsRepository=$doctrine->getRepository(GlobaleUsersUserGroups::class);
    $zonesRepository=$doctrine->getRepository(GlobalePermissionsZones::class);
    $routesRepository=$doctrine->getRepository(GlobalePermissionsRoutes::class);
    $routesUserRepository=$doctrine->getRepository(GlobalePermissionsRoutesUsers::class);
    $routesUserGroupsRepository=$doctrine->getRepository(GlobalePermissionsRoutesUserGroups::class);

    $moduleObj=$modulesRepository->findOneBy(["name"=>$module, "deleted"=>0]);
    $permissionRoute=$routesRepository->findOneBy(["module"=>$moduleObj,"name"=>$name, "deleted"=>0]);

    if(!$permissionRoute && $moduleObj){ //No exist the route in security routes table, create it
      $permissionRoute=new GlobalePermissionsRoutes();
      $permissionRoute->setName($name);
      $permissionRoute->setModule($moduleObj);
      $permissionRoute->setActive(1);
      $permissionRoute->setDeleted(0);
      $permissionRoute->setDateadd(new \DateTime());
      $permissionRoute->setDateupd(new \DateTime());
      $doctrine->getManager()->persist($permissionRoute);
      $doctrine->getManager()->flush();
    }

    $isAdmin=$userRepository->isAdmin($user->getId());
    if(in_array('ROLE_GLOBAL',$user->getRoles())) return true;
    if($isAdmin) return true;

    //Check if user has explicit policies
    $routeUser=$routesUserRepository->findOneBy(["permissionroute"=>$permissionRoute, "user"=>$user, "active"=>1, "deleted"=>0]);
    if($routeUser!=null){
      if($routeUser->getAllowaccess()==1) return true;
      if($routeUser->getAllowaccess()==0) return false;
    }

    //No explicit user policy or set to inherit group, check groups policies
    $userGroups=$userUsersGroupsRepository->findBy(["user"=>$user, "active"=>1, "deleted"=>0]);
    foreach($userGroups as $userGroup){
      $routeGroup=$routesUserGroupsRepository->findOneBy(["permissionroute"=>$permissionRoute, "usergroup"=>$userGroup->getUsergroup(), "active"=>1, "deleted"=>0]);
      if($routeGroup!=null){
        if($routeGroup->getAllowaccess()==1) return true;
      }
    }
    return false;

  }

  public function getZonePermissions($user, $doctrine){
    $modulesRepository=$doctrine->getRepository(GlobaleModules::class);
    $userRepository=$doctrine->getRepository(GlobaleUsers::class);
    $userUsersGroupsRepository=$doctrine->getRepository(GlobaleUsersUserGroups::class);
    $zonesRepository=$doctrine->getRepository(GlobalePermissionsZones::class);
    $zonesUserRepository=$doctrine->getRepository(GlobalePermissionsZonesUsers::class);
    $zonesUserGroupsRepository=$doctrine->getRepository(GlobalePermissionsZonesUserGroups::class);

    $permissionZones=$zonesRepository->findBy(["active"=>1, "deleted"=>0]);
    $permissions=[];
    foreach($permissionZones as $permissionZone){
      $permissions[$permissionZone->getName()]=["allowaccess" => false];
      //Allow all for user global
      if(in_array('ROLE_GLOBAL',$user->getRoles())){
        $permissions[$permissionZone->getName()]=["allowaccess" => true];
        continue;
      }

      //Check if user has explicit policies
      if($permissions[$permissionZone->getName()]["allowaccess"]==false){
        $userpolicy=$zonesUserRepository->findOneBy(["permissionzone"=>$permissionZone, "user"=>$user, "active"=>1, "deleted"=>0]);
        if($userpolicy){
            $permissions[$permissionZone->getName()]=["allowaccess" => $userpolicy->getAllowaccess()==1?true:false];
        }
      }

      //No explicit user policy or set to inherit group, check groups policies
      if($permissions[$permissionZone->getName()]["allowaccess"]==false){
        $userGroups=$userUsersGroupsRepository->findBy(["user"=>$user, "active"=>1, "deleted"=>0]);
        foreach($userGroups as $userGroup){
          $grouppolicy=$zonesUserGroupsRepository->findOneBy(["permissionzone"=>$permissionZone, "usergroup"=>$userGroup->getUsergroup(), "active"=>1, "deleted"=>0]);
          if($grouppolicy && $grouppolicy->getAllowaccess()){
              $permissions[$permissionZone->getName()]=["allowaccess" => true];
              continue;
          }
        }
      }

    }
    return $permissions;
  }

  public function isAdmin($user, $doctrine){
    $userRepository=$doctrine->getRepository(GlobaleUsers::class);
    $userGroupsRepository=$doctrine->getRepository(GlobaleUserGroups::class);
    $userUsersGroupsRepository=$doctrine->getRepository(GlobaleUsersUserGroups::class);
    $adminGroups=$userGroupsRepository->findBy(["company"=>$user->getCompany(),"isadmin"=>1,"active"=>1,"deleted"=>0]);
    $isAdmin=false;
    foreach($adminGroups as $group){
      $usergroup=$userUsersGroupsRepository->findOneBy(["user"=>$user,"usergroup"=>$group,"active"=>1,"deleted"=>0]);
      if($usergroup){
        $isAdmin=true;
        break;
      }

    }
    return $isAdmin;

  }
}
