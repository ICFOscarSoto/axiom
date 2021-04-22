<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleUserGroups;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleCompaniesModules;
use App\Modules\Globale\Entity\GlobaleWorkstations;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Modules\Security\Utils\SecurityUtils;

class GlobaleWorkstationsController extends Controller
{
   	private $class=GlobaleWorkstations::class;
    private $module="Globale";


  /**
  * @Route("/api/global/workstations/poweron/{id}", name="powerOnWorkStation")
  */
  public function powerOnWorkStation($id,Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
      $workstationRepository=$this->getDoctrine()->getRepository($this->class);
      $workstation = $workstationRepository->findOneBy(["id"=>$id, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);
      if(!$workstation) return new JsonResponse(["result"=>-1]);
      if($workstation->getMac()==null) return new JsonResponse(["result"=>-2]);
      shell_exec("wakeonlan ".$workstation->getMac());
      return new JsonResponse(["result"=>1]);
  }

  /**
  * @Route("/api/global/workstations/poweroff/{id}", name="powerOffWorkStation")
  */
  public function powerOffWorkStation($id,Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
      $workstationRepository=$this->getDoctrine()->getRepository($this->class);
      $workstation = $workstationRepository->findOneBy(["id"=>$id, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);
      if(!$workstation) return new JsonResponse(["result"=>-1]);
      if($workstation->getIpaddress()==null) return new JsonResponse(["result"=>-2]);
      if($workstation->getUser()==null) return new JsonResponse(["result"=>-3]);
      if($workstation->getPassword()==null) return new JsonResponse(["result"=>-4]);
      if($workstation->getOs()==1) shell_exec("net rpc shutdown -I ".$workstation->getIpaddress()." -U ".$workstation->getUser()."%".$workstation->getPassword());
      if($workstation->getOs()==2) {};

      return new JsonResponse(["result"=>1]);
  }

}
