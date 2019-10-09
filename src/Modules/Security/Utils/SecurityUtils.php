<?php
namespace App\Modules\Security\Utils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Modules\Globale\Entity\GlobalePermissionsZones;
use App\Modules\Globale\Entity\GlobaleModules;

class SecurityUtils
{
  public function checkRoutePermissions($module, $name, $user, $doctrine){
    $class='App\Modules\Globale\Entity\GlobalePermissionsRoutes';
    $modulesRepository=$doctrine->getRepository(GlobaleModules::class);
    $moduleObj=$modulesRepository->findOneBy(["name"=>$module, "deleted"=>0]);
    $routesRepository=$doctrine->getRepository($class);
    $searchRoute=$routesRepository->findOneBy(["module"=>$moduleObj,"name"=>$name, "deleted"=>0]);

    if(!$searchRoute && $moduleObj){ //No exist the route in security routes table, create it
      $obj=new $class();
      $obj->setName($name);
      $obj->setModule($moduleObj);
      $obj->setActive(1);
      $obj->setDeleted(0);
      $obj->setDateadd(new \DateTime());
      $obj->setDateupd(new \DateTime());
      $doctrine->getManager()->persist($obj);
      $doctrine->getManager()->flush();
    }
    //Check if user has explicit policies
    
    //If not, check groups policies
    return true;
  }
  public function checkZonePermissions($module, $name, $user, $doctrine){

    return true;
  }
}
