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
use App\Modules\Globale\Entity\GlobalePermissionsRoutes;
use App\Modules\Globale\Entity\GlobalePermissionsRoutesUsers;
use App\Modules\Globale\Entity\GlobaleCompaniesModules;
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
      $modules=$companiesModulesRepository->findBy(["companyown"=>$this->getUser()->getCompany()]);
      //Get routers for each module
      $permisions=[];
      foreach($modules as $key=>$module){
        $permissionsRoutesUsersRepository=$this->getDoctrine()->getRepository(GlobalePermissionsRoutesUsers::class);
        $permissions[$module->getModule()->getName()]["description"]=$module->getModule()->getDescription();
        $permissions[$module->getModule()->getName()]["routes"]=$permissionsRoutesUsersRepository->findByUserModule($id, $module->getModule()->getId());

      }

      return $this->render('@Globale/userpermissions.html.twig',[
        "permissions"=>$permissions,

      ]);
    }


}
