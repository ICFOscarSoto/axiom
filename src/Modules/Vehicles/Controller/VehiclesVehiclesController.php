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
use App\Modules\HR\Entity\HRWorkers;

use App\Modules\Vehicles\Entity\VehiclesVehicles;
use App\Modules\Vehicles\Entity\VehiclesUses;
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
	 	return $utils->make($id, $this->class, $action, "formvehicle", "full", "@Globale/form.html.twig", "formVehicle");
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
											 ["name" => "uses", "caption"=>"Usos vehículo", "icon"=>"fa-gas-pump", "route"=>$this->generateUrl("generictablist",["module"=>"Vehicles", "name"=>"Uses", "id"=>$id])],
											 /*["name" => "refuelings", "caption"=>"Repostajes", "icon"=>"fa-gas-pump", "route"=>$this->generateUrl("generictablist",["module"=>"Vehicles", "name"=>"Refuelings", "id"=>$id])],*/
											 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"vehicles", "module"=>"Vehicles", "types"=>json_encode(["Permiso circulación","Ficha técnica","Poliza seguro","Resultado inspección","Otros"])])]
	 										],
	 					'include_header' => [["type"=>"css", "path"=>"/js/jvectormap/jquery-jvectormap-1.2.2.css"],
	 															 ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
	 															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
	 					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
	 															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
	 															 ["type"=>"css", "path"=>"/css/timeline.css"]]
	 	));
	 }

	 /**
	  * @Route("/api/vehicles/getinuse", name="getVehicleInUse")
	  */
	  public function getVehicleInUse(Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$vehicleUsesRepository=$this->getDoctrine()->getRepository(VehiclesUses::class);

			//Obtenemos el tranajador asociado al usuario que realiza la llamada
			$worker=$workerRepository->findOneBy(['user'=>$this->getUser(),'active'=>1, 'deleted'=>0]);
			if(!$worker) return new JsonResponse(['result'=>-1, 'text'=>'No existe el trabajador asociado']);
			//Obtenemos el uso de vehiculo que esta abierto por el trabajador
			$vehicleUse=$vehicleUsesRepository->findOneBy(['worker'=>$worker, 'end'=>null, 'active'=>1, 'deleted'=>0]);
			if(!$vehicleUse){
				$vehicle=new VehiclesVehicles();
				return new JsonResponse($vehicle->encodeJson($this->getDoctrine()));
			}
			if($vehicleUse->getVehicle()->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(['result'=>-1, 'text'=>'No existe el vehiculo escaneado']);
			return new JsonResponse($vehicleUse->getVehicle()->encodeJson($this->getDoctrine()));

		}

		/**
 	  * @Route("/api/vehicles/leavevehicle", name="leaveVehicle")
 	  */
 	  public function leaveVehicle(Request $request){
 			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
 			$vehicleUsesRepository=$this->getDoctrine()->getRepository(VehiclesUses::class);

 			//Obtenemos el tranajador asociado al usuario que realiza la llamada
 			$worker=$workerRepository->findOneBy(['user'=>$this->getUser(),'active'=>1, 'deleted'=>0]);
 			if(!$worker) return new JsonResponse(['result'=>-1, 'text'=>'No existe el trabajador asociado']);
 			//Obtenemos el uso de vehiculo que esta abierto por el trabajador
 			$vehicleUse=$vehicleUsesRepository->findOneBy(['worker'=>$worker, 'end'=>null, 'active'=>1, 'deleted'=>0]);
 			if(!$vehicleUse) return new JsonResponse(['result'=>-1, 'text'=>'No existe ningun vehiculo en uso por el usuario']);
 			if($vehicleUse->getVehicle()->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(['result'=>-1, 'text'=>'No existe ningun vehiculo en uso por el usuario']);
			$vehicleUse->setEnd(new \DateTime());
			$vehicleUse->setEndlatitude($request->request->get("latitude"));
			$vehicleUse->setEndlongitude($request->request->get("longitude"));
			$vehicleUse->setObservations($request->request->get("observations"));
			$vehicleUse->setDateupd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($vehicleUse);
			$this->getDoctrine()->getManager()->flush();
			return new JsonResponse(['result'=>1, 'text'=>'Se ha liberado el vehiculo correctamente']);
 		}

		/**
 	  * @Route("/api/vehicles/takevehicle/{id}", name="takeVehicle", defaults={"id"=0})
 	  */
 	  public function takeVehicle($id, Request $request){
 			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
 			$vehicleRepository=$this->getDoctrine()->getRepository(VehiclesVehicles::class);
 			$vehicleUsesRepository=$this->getDoctrine()->getRepository(VehiclesUses::class);

 			//Obtenemos el tranajador asociado al usuario que realiza la llamada
 			$worker=$workerRepository->findOneBy(['user'=>$this->getUser(),'active'=>1, 'deleted'=>0]);
 			if(!$worker) return new JsonResponse(['result'=>-1, 'text'=>'No existe el trabajador asociado']);
 			//Comprobamos que el usuario no tenga otro vehiculo en uso
 			$vehicleUse=$vehicleUsesRepository->findOneBy(['worker'=>$worker, 'end'=>null, 'active'=>1, 'deleted'=>0]);
 			if($vehicleUse) return new JsonResponse(['result'=>-1, 'text'=>'Ya hay un vehiculo en uso por el usuario']);
			//Comprobamos que el vehiculo no este cogido por otro usuario
			$vehicle=$vehicleRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
			if(!$vehicle) return new JsonResponse(['result'=>-1, 'text'=>'No existe el vehiculo escaneado']);
 			if($vehicle->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(['result'=>-1, 'text'=>'No existe el vehiculo escaneado']);
			$vehicleUse = new VehiclesUses();
			$vehicleUse->setStart(new \DateTime());
			$vehicleUse->setVehicle($vehicle);
			$vehicleUse->setWorker($worker);
			$vehicleUse->setAuthor($this->getUser());
			$vehicleUse->setActive(1);
			$vehicleUse->setDeleted(0);
			$vehicleUse->setDateadd(new \DateTime());
			$vehicleUse->setDateupd(new \DateTime());
			$vehicleUse->setStartlatitude($request->request->get("latitude"));
			$vehicleUse->setStartlongitude($request->request->get("longitude"));

			$this->getDoctrine()->getManager()->persist($vehicleUse);
			$this->getDoctrine()->getManager()->flush();
			return new JsonResponse(['result'=>1, 'text'=>'Se ha liberado el vehiculo correctamente']);

 		}


		/**
		* @Route("/api/vehicles/getuses", name="getVehiclesUses")
		*/
		public function getVehiclesUses(Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
 			$vehicleRepository=$this->getDoctrine()->getRepository(VehiclesVehicles::class);
 			$vehicleUsesRepository=$this->getDoctrine()->getRepository(VehiclesUses::class);
			//Obtenemos el tranajador asociado al usuario que realiza la llamada
			$worker=$workerRepository->findOneBy(['user'=>$this->getUser(),'active'=>1, 'deleted'=>0]);
			if(!$worker) return new JsonResponse(['result'=>-1, 'text'=>'No existe el trabajador asociado']);
			$vehiclesUses=$vehicleUsesRepository->findBy(['worker'=>$worker, 'active'=>1, 'deleted'=>0],['start' => 'DESC']);
			$result=[];
			foreach($vehiclesUses as $vehicleUse){
				$item['id']=$vehicleUse->getId();
				$item['start']=$vehicleUse->getStart()->getTimestamp();
				$item['end']=$vehicleUse->getEnd()?$vehicleUse->getEnd()->getTimestamp():0;
				$item['startlatitude']=$vehicleUse->getStartLatitude();
				$item['startlongitude']=$vehicleUse->getStartLongitude();
				$item['endlatitude']=$vehicleUse->getEndLatitude();
				$item['endlongitude']=$vehicleUse->getEndLongitude();
				$item['vehicle']=$vehicleUse->getVehicle()->encodeJson($this->getDoctrine());
				$result[]=$item;
			}
			return new JsonResponse($result);
		}

}
