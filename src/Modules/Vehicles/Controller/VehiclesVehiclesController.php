<?php

namespace App\Modules\Vehicles\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use App\Modules\Cloud\Controller\CloudController;
use App\Modules\Security\Utils\SecurityUtils;

use App\Modules\Vehicles\Entity\VehiclesVehicles;
use App\Modules\Vehicles\Utils\VehiclesVehiclesUtils;

class VehiclesVehiclesController extends Controller
{
	 private $module='Vehicles';
	 private $class=VehiclesVehicles::class;
	 private $utilsClass=VehiclesVehiclesUtils::class;


	 /**
	  * @Route("/{_locale}/vehicles/vehicles/data/{id}/{action}", name="dataVehicle", defaults={"id"=0, "action"="read"})
	  */
	  public function dataVehicle($id, $action, Request $request){
	 	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	$this->denyAccessUnlessGranted('ROLE_ADMIN');
	 	$template=dirname(__FILE__)."/../Forms/Vehicles.json";
	 	$utils = new GlobaleFormUtils();
	 	$utilsObj=new $this->utilsClass();
	 	$workerRepository=$this->getDoctrine()->getRepository($this->class);
	 	$obj = $workerRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 	if($id!=0 && $obj==null){
	 			return $this->render('@Globale/notfound.html.twig',[]);
	 	}
	 	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "object"=>$obj];
	 	$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	 	return $utils->make($id, $this->class, $action, "formvehicle", "full", "@Globale/form.html.twig", "formVehicles");
	 }

	 /**
	  * @Route("/{_locale}/vehicles/vehicles/form/{id}", name="formVehicle", defaults={"id"=0})
	  */
	  public function formVehicles($id, Request $request){
	 	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
	 	$template=dirname(__FILE__)."/../Forms/Vehicles.json";
	 	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
	 	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	 	$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
	 	$breadcrumb=$menurepository->formatBreadcrumb('vehicles');
	 	array_push($breadcrumb, $new_breadcrumb);
	 	$vehicleRepository=$this->getDoctrine()->getRepository($this->class);
	 	$obj = $vehicleRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 	if($id!=0 && $obj==null){
	 			return $this->render('@Globale/notfound.html.twig',[
	 				"status_code"=>404,
	 				"status_text"=>"Objeto no encontrado"
	 			]);
	 	}
	 	$entity_name=$obj?$obj->getBrand().' '.$obj->getModel().' - '.$obj->getLicenseplate():'';
	 	return $this->render('@Globale/generictabform.html.twig', array(
	 					'entity_name' => $entity_name,
	 					'controllerName' => 'VehiclesController',
	 					'interfaceName' => 'Vehículos',
						'optionSelected' => 'genericindex',
						'optionSelectedParams' => ["module"=>"Vehicles", "name"=>"Vehicles"],
	 					'menuOptions' =>  $menurepository->formatOptions($userdata),
	 					'breadcrumb' => $breadcrumb,
	 					'userData' => $userdata,
	 					'id' => $id,
	 					'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
	 					'tabs' => [["name" => "data", "caption"=>"Datos vehículo", "icon"=>"fa-address-card-o","active"=>true, "route"=>$this->generateUrl("dataVehicle",["id"=>$id])],
											 ["name" => "refuelings", "caption"=>"Repostajes", "icon"=>"fa-gas-pump", "route"=>$this->generateUrl("generictablist",["module"=>"Vehicles", "name"=>"Refuelings", "id"=>$id])],
	 										],
	 					'include_header' => [["type"=>"css", "path"=>"/js/jvectormap/jquery-jvectormap-1.2.2.css"],
	 															 ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
	 															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
	 					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
	 															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
	 															 ["type"=>"css", "path"=>"/css/timeline.css"]]

	 	));
	 }

}
