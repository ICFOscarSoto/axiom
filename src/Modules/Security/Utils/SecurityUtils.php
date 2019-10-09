<?php
namespace App\Modules\Security\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Modules\Globale\Entity\GlobaleModules;
use App\Modules\Globale\Entity\GlobaleUsersUserGroups;
use App\Modules\Globale\Entity\GlobalePermissionsZones;
use App\Modules\Globale\Entity\GlobalePermissionsRoutes;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUsers;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUserGroups;

class SecurityUtils
{
  public function checkRoutePermissions($module, $name, $user, $doctrine){
    if(in_array('ROLE_GLOBAL',$user->getRoles())) return true;
    $modulesRepository=$doctrine->getRepository(GlobaleModules::class);
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

  public function checkZonePermissions($module, $name, $user, $doctrine){
    return true;
  }
}
