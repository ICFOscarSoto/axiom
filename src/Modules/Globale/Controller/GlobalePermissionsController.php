<?php

namespace App\Modules\Globale\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleUserGroups;
use App\Modules\Globale\Entity\GlobaleModules;
use App\Modules\Globale\Entity\GlobalePermissionsRoutes;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUsers;
use App\Modules\Globale\Entity\GlobaleCompaniesModules;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUserGroups;
/**
 * @Route("/")
 */
class GlobalePermissionsController extends Controller
{
	 /**
     * @Route("/{_locale}/user/{id}/permissions", name="userPermissions")
     */
    public function userPermissions($id, RouterInterface $router,Request $request){
      //Get company user modules enabled
      $companiesModulesRepository=$this->getDoctrine()->getRepository(GlobaleCompaniesModules::class);
      $modulesRepository=$this->getDoctrine()->getRepository(GlobaleModules::class);
      $permissionsRoutesUsersRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutesUsers::class);
      $userRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
      $user=$userRepository->findOneBy(["company"=>$this->getUser()->getCompany(),"id"=>$id, "active"=>1, "deleted"=>0]);
      if(!$user) return $this->render('@Globale/notfound.html.twig',["status_code"=>404, "status_text"=>"Objeto no encontrado"]);

      $moduleGlobal=$modulesRepository->findOneBy(["name"=>"Globale"]);
      $modules=$companiesModulesRepository->findBy(["companyown"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

      //Get routers for each module
      $permisions=[];
      $permissions[$moduleGlobal->getName()]["description"]=$moduleGlobal->getDescription();
      $permissions[$moduleGlobal->getName()]["routes"]=$permissionsRoutesUsersRepository->findByUserModule($id, $moduleGlobal->getId());
      foreach($modules as $key=>$module){
        $permissions[$module->getModule()->getName()]["description"]=$module->getModule()->getDescription();
        $permissions[$module->getModule()->getName()]["routes"]=$permissionsRoutesUsersRepository->findByUserModule($id, $module->getModule()->getId());
      }
      return $this->render('@Globale/userpermissions.html.twig',[
        "permissions"=>$permissions,
        "id"=>$id
      ]);
    }

    /**
      * @Route("/{_locale}/user/{id}/setpermissions", name="userSetPermissions")
      */
     public function userSetPermissions($id, RouterInterface $router,Request $request){
       $permissions=json_decode($request->getContent());
       $usersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
       $permissionsRoutesRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutes::class);
       $permissionsRoutesUsersRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutesUsers::class);
       $user=$usersRepository->findOneBy(["company"=>$this->getUser()->getCompany(),"id"=>$id, "active"=>1, "deleted"=>0]);
       if(!$user) new JsonResponse(['result'=>-1]);
       foreach($permissions as $permission){
         //Search the routes-users
         $permisionRoute=$permissionsRoutesRepository->findOneBy(["id"=>$permission->route, "active"=>1, "deleted"=>0]);
         if(!$permisionRoute) continue;
         $permissionsRoutesUser=$permissionsRoutesUsersRepository->findOneBy(["user"=>$user, "permissionroute"=>$permisionRoute, "active"=>1, "deleted"=>0]);
         if($permissionsRoutesUser){
           $permissionsRoutesUser->setAllowaccess($permission->value);
         }else{
           if($permission->value!=3){
             $permissionsRoutesUser = new GlobalePermissionsRoutesUsers();
             $permissionsRoutesUser->setUser($user);
             $permissionsRoutesUser->setPermissionroute($permisionRoute);
             $permissionsRoutesUser->setAllowaccess($permission->value);
             $permissionsRoutesUser->setActive(1);
             $permissionsRoutesUser->setDeleted(0);
             $permissionsRoutesUser->setDateadd(new \DateTime());
           }
         }
         if(!$permissionsRoutesUser) continue;
         $permissionsRoutesUser->setDateupd(new \DateTime());
         $this->getDoctrine()->getManager()->persist($permissionsRoutesUser);
         $this->getDoctrine()->getManager()->flush();
       }
       return new JsonResponse(['result'=>1]);

     }

     /**
        * @Route("/{_locale}/usergroup/{id}/permissions", name="userGroupPermissions")
        */
       public function userGroupPermissions($id, RouterInterface $router,Request $request){
         //Get company user modules enabled
         $modulesRepository=$this->getDoctrine()->getRepository(GlobaleModules::class);
         $usergroupRepository=$this->getDoctrine()->getRepository(GlobaleUserGroups::class);
         $companiesModulesRepository=$this->getDoctrine()->getRepository(GlobaleCompaniesModules::class);
         $permissionsRoutesUsersRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutesUserGroups::class);
         $usergroup=$usergroupRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
         if(!$usergroup) return $this->render('@Globale/notfound.html.twig',["status_code"=>404, "status_text"=>"Objeto no encontrado"]);

         $moduleGlobal=$modulesRepository->findOneBy(["name"=>"Globale"]);
         $modules=$companiesModulesRepository->findBy(["companyown"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

         //Get routers for each module
         $permisions=[];
         $permissions[$moduleGlobal->getName()]["description"]=$moduleGlobal->getDescription();
         $permissions[$moduleGlobal->getName()]["routes"]=$permissionsRoutesUsersRepository->findByUserGroupModule($id, $moduleGlobal->getId());
         foreach($modules as $key=>$module){
           $permissions[$module->getModule()->getName()]["description"]=$module->getModule()->getDescription();
           $permissions[$module->getModule()->getName()]["routes"]=$permissionsRoutesUsersRepository->findByUserGroupModule($id, $module->getModule()->getId());
         }
         return $this->render('@Globale/usergrouppermissions.html.twig',[
           "permissions"=>$permissions,
           "id"=>$id,
           "isadmin"=>$usergroup->getIsadmin()?true:false
         ]);
       }


       /**
         * @Route("/{_locale}/groupuser/{id}/setpermissions", name="groupSetPermissions")
         */
        public function groupSetPermissions($id, RouterInterface $router,Request $request){
          $permissions=json_decode($request->getContent());
          $userGroupsRepository=$this->getDoctrine()->getRepository(GlobaleUserGroups::class);
          $permissionsRoutesRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutes::class);
          $permissionsRoutesUserGroupsRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutesUserGroups::class);
          $usergroup=$userGroupsRepository->findOneBy(["company"=>$this->getUser()->getCompany(),"id"=>$id, "active"=>1, "deleted"=>0]);
          if(!$usergroup) new JsonResponse(['result'=>-1]);
          foreach($permissions as $permission){
            //Search the routes-users
            $permisionRoute=$permissionsRoutesRepository->findOneBy(["id"=>$permission->route, "active"=>1, "deleted"=>0]);
            if(!$permisionRoute) continue;
            $permissionsRoutesUser=$permissionsRoutesUserGroupsRepository->findOneBy(["usergroup"=>$usergroup, "permissionroute"=>$permisionRoute, "active"=>1, "deleted"=>0]);
            if($permissionsRoutesUser){
              $permissionsRoutesUser->setAllowaccess($permission->value);
            }else{
              if($permission->value!=0){
                $permissionsRoutesUser = new GlobalePermissionsRoutesUserGroups();
                $permissionsRoutesUser->setUsergroup($usergroup);
                $permissionsRoutesUser->setPermissionroute($permisionRoute);
                $permissionsRoutesUser->setAllowaccess($permission->value);
                $permissionsRoutesUser->setActive(1);
                $permissionsRoutesUser->setDeleted(0);
                $permissionsRoutesUser->setDateadd(new \DateTime());
              }
            }
            if(!$permissionsRoutesUser) continue;
            $permissionsRoutesUser->setDateupd(new \DateTime());
            $this->getDoctrine()->getManager()->persist($permissionsRoutesUser);
            $this->getDoctrine()->getManager()->flush();
          }
          return new JsonResponse(['result'=>1]);

        }
}
