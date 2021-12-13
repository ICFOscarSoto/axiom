<?php

namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRWorkersUtils;
use App\Modules\HR\Utils\HRWorkCalendarsUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use App\Modules\HR\Entity\HRWorkCalendars;
use App\Modules\HR\Entity\HRHollidays;
use App\Modules\HR\Entity\HRSchedules;
use App\Modules\HR\Entity\HRShifts;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Modules\Security\Utils\SecurityUtils;


class HRController extends Controller
{

	 private $class=HRWorkers::class;
	 private $module='HR';
	 private $utilsClass=HRWorkersUtils::class;

    /**
     * @Route("/{_locale}/HR/workers", name="workers")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$utils = new HRWorkersUtils();
		$templateLists[]=$utils->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'HRController',
				'interfaceName' => 'Trabajadores',
				'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists,
				'include_post_templates' => ['@HR/clocksprintselect.html.twig'],
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/HR/workers/data/{id}/{action}", name="dataWorker", defaults={"id"=0, "action"="read"})
		 */
		 public function dataWorker($id, $action, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$template=dirname(__FILE__)."/../Forms/Workers.json";
			$utils = new GlobaleFormUtils();
	    $utilsObj=new $this->utilsClass();
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$obj = $workerRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[]);
			}
	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "worker"=>$obj];
			$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
			return $utils->make($id, $this->class, $action, "formworker", "full", "@Globale/form.html.twig", "formWorker");
		}

		/**
		 * @Route("/{_locale}/HR/workers/form/{id}", name="formWorker", defaults={"id"=0})
		 */
		 public function formWorker($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$template=dirname(__FILE__)."/../Forms/Workers.json";
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$breadcrumb=$menurepository->formatBreadcrumb('workers');
			array_push($breadcrumb, $new_breadcrumb);
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$obj = $workerRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[
						"status_code"=>404,
						"status_text"=>"Objeto no encontrado"
					]);
			}
			$entity_name=$obj?$obj->getLastName().', '.$obj->getName().' ('.$obj->getIdcard().')':'';
			return $this->render('@Globale/generictabform.html.twig', array(
							'entity_name' => $entity_name,
							'controllerName' => 'WorkersController',
							'interfaceName' => 'Trabajadores',
							'optionSelected' => 'workers',
							'menuOptions' =>  $menurepository->formatOptions($userdata),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "icon"=>"fa-address-card-o","active"=>true, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
												 /*["name" => "clothes", "icon"=>"fa fa-headphones", "caption"=>"Ropa y EPI"],
												 ["name" => "paymentroll", "icon"=>"fa fa-eur", "caption"=>"Nóminas"],
												 ["name" => "contracts", "icon"=>"fa fa-briefcase", "caption"=>"Contratos"],*/
												 ["name" => "sickleave", "icon"=>"fa fa-hospital-o", "caption"=>"Bajas", "route"=>$this->generateUrl("sickleaves",["id"=>$id])],
												 ["name" => "vacations", "icon"=>"fa fa-paper-plane", "caption"=>"Vacaciones", "route"=>$this->generateUrl("vacations",["id"=>$id])],
												 ["name" => "clocks", "icon"=>"fa fa-clock-o", "caption"=>"Fichajes", "route"=>$this->generateUrl("workerClocks",["id"=>$id])],
												 ["name" => "equipment", "icon"=>"fa fa-wrench", "caption"=>"Equipamiento", "route"=>$this->generateUrl("generictablist",["module"=>"HR", "name"=>"WorkerEquipment", "id"=>$id])],
												 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"workers", "module"=>"HR", "types"=>json_encode(["Contrato laboral","Currículum Vitae","Certificado Cuenta Bancaria","Nomina","Finiquito","Tratamiento datos","Indemnización","Expediente","DNI","Pasaporte","Permiso Trabajo","Otros"])])]
												],
							'include_tab_post_templates' => ['@HR/workerequipments.html.twig'],

							'include_header' => [["type"=>"css", "path"=>"/js/jvectormap/jquery-jvectormap-1.2.2.css"],
																	 ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
																	 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
							'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
										 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
		 															 ["type"=>"css", "path"=>"/css/timeline.css"]]
							/*'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>$tab=='data'?true:false, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
												 ["name" => "paymentroll", "active"=>($tab=='paymentroll' && $id)?true:false, "caption"=>"Nóminas"]
												]*/
			));
		}





	/**
	 * @Route("/api/HR/workers/list/{type}/{id}", name="workerslist", defaults={"type"="all", "id"=0})
	 */
	public function indexlist($type,$id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true);
		switch($type){
			case "all":
				$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
			break;
			case "department":
				$repositoryDeparments = $manager->getRepository(HRDepartments::class);
				$department=$repositoryDeparments->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
				$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"department", "value"=>$department]]);
			break;
			case "workcenter":
				$repositoryWorkCenter = $manager->getRepository(HRWorkCenters::class);
				$workcenter=$repositoryWorkCenter->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
				$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"workcenters", "value"=>$workcenter]]);
			break;
		}

		return new JsonResponse($return);
	}

	/**
	 * @Route("/api/HR/workcalendars/list", name="workcalendarslist")
	 */
	public function workcalendarslist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(HRWorkCalendars::class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkCalendars.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, HRWorkCalendars::class);
		return new JsonResponse($return);
	}

		/**
		 * @Route("/api/HR/workers/{id}/get", name="getWorker")
		 */
		public function getWorker($id){
			$obj = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$obj) {
        throw $this->createNotFoundException('No worker found for id '.$id );
			}
			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
		}


	/**
	* @Route("/{_locale}/admin/global/workers/{id}/disable", name="disableWorker")
	*/
	public function disable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/workers/{id}/enable", name="enableWorker")
	*/
	public function enable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
  * @Route("/{_locale}/admin/global/workers/{id}/delete", name="deleteWorker", defaults={"id"=0})
  */
  public function delete($id,Request $request){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   if($id!=0) $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
    else {
      $ids=$request->request->get('ids');
      $ids=explode(",",$ids);
      foreach($ids as $item){
        $result=$entityUtils->deleteObject($item, $this->class, $this->getDoctrine());
      }
    }
   return new JsonResponse(array('result' => $result));
  }

	/**
	 * @Route("/api/HR/workers/export", name="exportWorkers")
	 */
	 public function exportWorkers(Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $utilsExport = new GlobaleExportUtils();
		 //$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
		 //$worker = $workerRepository->find($id);
		 $user = $this->getUser();
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Workers.json"),true);
		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $result = $utilsExport->export($list,$listFields);
		 return $result;
	 }

	 /**
 	 * @Route("/api/HR/workers/print", name="printWorkers")
 	 */
 	 public function printWorkers(Request $request){
 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 		 $utilsPrint = new GlobalePrintUtils();
 		 $user = $this->getUser();
 		 $manager = $this->getDoctrine()->getManager();
 		 $repository = $manager->getRepository($this->class);
 		 $listUtils=new GlobaleListUtils();
 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Workers.json"),true);
 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $utilsPrint->title="LISTADO DE TRABAJADORES";
 		 $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
		 return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
 	 }



	/**
  * @Route("/api/HR/workers/collection", name="genericapiWorkerscollection")
  */
  public function genericapiWorkerscollection(Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $parameters=$request->query->all();
    $filter[]=["type"=>"and", "column"=>"company", "value"=>$this->getUser()->getCompany()];
    foreach($parameters as $key => $parameter){
      if(in_array("set".ucfirst($parameter),get_class_methods($this->class)))
        $filter[]=["type"=>"and", "column"=>$key, "value"=>$parameter];
    }
    $listUtils=new GlobaleListApiUtils();
    $return=$listUtils->getRecords($this->getUser(),$repository,$request,$manager, $this->class,$filter,-1,[]);
    return new JsonResponse($return);
  }


	/**
	 * @Route("/api/HR/workers/add", name="addUserWorker")
	 */
	public function addUserWorker(Request $request, UserPasswordEncoderInterface $encoder){
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$id=$request->request->get('id');
			$status=$request->request->get('status')!=null?$request->request->get('status'):1;
			$obj=null;
			$user=null;
			if($id==null){
				//New worker
				if($request->request->get('email')!=null && $request->request->get('password')!=null && $request->request->get('idcard')!=null && $request->request->get('name')!=null && $request->request->get('lastname')!=null){
						$obj = new $this->class();
						$user = new GlobaleUsers();
						if($status==null) $status=1;
						$obj->setDateadd(new \DateTime());
	          $obj->setDeleted(false);
						$obj->setActive(true);
						$user->setDateadd(new \DateTime());
	          $user->setDeleted(false);
						$user->setActive(true);
						$user->setRoles(["ROLE_USER"]);
	          //If object has Company save with de user Company
	          if(method_exists($obj,'setCompany')) $obj->setCompany($this->getUser()->getCompany());
						if(method_exists($user,'setCompany')) $user->setCompany($this->getUser()->getCompany());
				}else return new JsonResponse(["result"=>-2]);
			}else{
				//Edit workers
				$obj = $this->getDoctrine()->getRepository($this->class)->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
				$user=$obj->getUser();
				//Check if company owns this worker
				if(!$obj) return new JsonResponse(["result"=>-1]);
			}
			//Get available fields
			$obj->setIdcard($request->request->get('idcard')!=null?$request->request->get('idcard'):$obj->getIdcard());
			$obj->setSs($request->request->get('ss')!=null?$request->request->get('ss'):$obj->getSs());
			$obj->setName($request->request->get('name')!=null?$request->request->get('name'):$obj->getName());
			$obj->setLastname($request->request->get('lastname')!=null?$request->request->get('lastname'):$obj->getLastname());
			$obj->setExternal($request->request->get('external')!=null?$request->request->get('external'):$obj->getExternal());
			$obj->setStatus($status!=null?$status:$obj->getStatus());
			setlocale(LC_ALL,"es_ES.utf8");
			$obj->setDateofemploy($request->request->get('dateofemploy')!=null?new \DateTime("@".$request->request->get('dateofemploy')):$obj->getDateofemploy());
			$obj->setAddress($request->request->get('address')!=null?$request->request->get('address'):$obj->getAddress());
			$obj->setCity($request->request->get('city')!=null?$request->request->get('city'):$obj->getCity());
			$obj->setState($request->request->get('state')!=null?$request->request->get('state'):$obj->getState());
			$obj->setPostcode($request->request->get('postcode')!=null?$request->request->get('postcode'):$obj->getPostcode());
			$obj->setPhone($request->request->get('phone')!=null?$request->request->get('phone'):$obj->getPhone());
			$obj->setMobile($request->request->get('mobile')!=null?$request->request->get('mobile'):$obj->getMobile());
			$obj->setEmail($request->request->get('email')!=null?$request->request->get('email'):$obj->getEmail());
			$obj->setCountry($request->request->get('country')!=null?$this->getDoctrine()->getRepository(GlobaleCountries::class)->findOneBy(["id"=>$request->request->get('country')]):$obj->getCountry());
			$obj->setBank($request->request->get('bank')!=null?$request->request->get('bank'):$obj->getBank());
			$obj->setCcc($request->request->get('ccc')!=null?$request->request->get('ccc'):$obj->getCcc());
			$obj->setIban($request->request->get('iban')!=null?$request->request->get('iban'):$obj->getIban());
			$obj->setBirthdate($request->request->get('birthdate')!=null?new \DateTime("@".$request->request->get('birthdate')):$obj->getBirthdate());
			$obj->setAllowremoteclock($request->request->get('allowremoteclock')!=null?$request->request->get('allowremoteclock'):$obj->getAllowremoteclock());
			$obj->setDepartment($request->request->get('department')!=null?$this->getDoctrine()->getRepository(HRDepartments::class)->findOneBy(["id"=>$request->request->get('department'), "company"=>$this->getUser()->getCompany()]):$obj->getDepartment());
			$obj->setWorkcenters($request->request->get('workcenters')!=null?$this->getDoctrine()->getRepository(HRWorkcenters::class)->findOneBy(["id"=>$request->request->get('workcenters'), "company"=>$this->getUser()->getCompany()]):$obj->getWorkcenters());
			$obj->setDateupd(new \DateTime());
			$user->setDateupd(new \DateTime());
			$user->setEmail($request->request->get('email')!=null?$request->request->get('email'):$user->getEmail());
			if($request->request->get('password')!=null)
				$user->setPassword($encoder->encodePassword($user, $request->request->get('password')));
			$user->setName($request->request->get('name')!=null?$request->request->get('name'):$user->getName());
			$user->setLastname($request->request->get('lastname')!=null?$request->request->get('lastname'):$user->getLastname());
			//Save user
			if(method_exists($user,'preProccess')) $user->{'preProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			$this->getDoctrine()->getManager()->persist($user);
			$this->getDoctrine()->getManager()->flush();
			if(method_exists($user,'postProccess')) $user->{'postProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			$obj->setUser($user);
			//Save worker
			if(method_exists($obj,'preProccess')) $obj->{'preProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			$this->getDoctrine()->getManager()->persist($obj);
			$this->getDoctrine()->getManager()->flush();
			if(method_exists($obj,'postProccess')) $obj->{'postProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			return new JsonResponse(["result"=>1,"worker"=>$obj->getId(), "user"=>$user->getId()]);
		}else{
			//No login
			return new JsonResponse(["result"=>-1]);
		}
	}

	/**
	 * @Route("/api/HR/workers/selectschedule/{module}/{name}/{id}", name="workerSelectSchedule", defaults={"id"=0})
	 */
	 public function workerSelectSchedule($module, $name, $id, Request $request){
		 if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
		 	$schedulesRepository = $this->getDoctrine()->getRepository(HRSchedules::class);
			$shiftsRepository = $this->getDoctrine()->getRepository(HRShifts::class);
			$schedule=$schedulesRepository->findOneBy(["id"=>$id, "active"=>1,"deleted"=>0, "company"=>$this->getUser()->getCompany()]);
			$shifts=$shiftsRepository->findBy(["schedule"=>$schedule,"active"=>1,"deleted"=>0]);
			$return=[];
			foreach($shifts as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$return[]=$option;
			}
			return new JsonResponse($return);
	 	}else{
			return new JsonResponse([]);
		}
 	}


	/**
	 * @Route("/api/HR/workers/getextensions/2/json", name="workerGetExtensions", defaults={"id"=0})
	 */
	 public function workerGetExtensions(Request $request){
		 	$array=["refresh"=>3600, "items"=>[
				["number"=>11, "name"=>"Manolo Jimenez", "firstname"=>"", "lastname"=> "", "phone"=> "11", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>12, "name"=>"Juanjo Roncero", "firstname"=>"", "lastname"=> "", "phone"=> "12", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>13, "name"=>"Antonio Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "13", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>14, "name"=>"Maria Jose Puche", "firstname"=>"", "lastname"=> "", "phone"=> "14", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>15, "name"=>"Juan Ruiz", "firstname"=>"", "lastname"=> "", "phone"=> "15", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>16, "name"=>"Antonio Jose Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "16", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>17, "name"=>"Chema Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "17", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>18, "name"=>"Juanga Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "18", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>19, "name"=>"Charo Puche", "firstname"=>"", "lastname"=> "", "phone"=> "19", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>20, "name"=>"Juan Manuel Toribio", "firstname"=>"", "lastname"=> "", "phone"=> "20", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>22, "name"=>"Ricardo Garcia", "firstname"=>"", "lastname"=> "", "phone"=> "22", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>23, "name"=>"Carlos Delgado", "firstname"=>"", "lastname"=> "", "phone"=> "23", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>24, "name"=>"Manolo Ortega", "firstname"=>"", "lastname"=> "", "phone"=> "24", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>25, "name"=>"Carlos Javier Delgado", "firstname"=>"", "lastname"=> "", "phone"=> "25", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>26, "name"=>"Daniel Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "26", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>27, "name"=>"Gabriel Toribio", "firstname"=>"", "lastname"=> "", "phone"=> "27", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>28, "name"=>"Juan Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "28", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>29, "name"=>"Daniel Gabaldon", "firstname"=>"", "lastname"=> "", "phone"=> "29", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>30, "name"=>"Jesus Jimenez", "firstname"=>"", "lastname"=> "", "phone"=> "30", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>32, "name"=>"Miguel Picazo", "firstname"=>"", "lastname"=> "", "phone"=> "32", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>33, "name"=>"Javier Garcia", "firstname"=>"", "lastname"=> "", "phone"=> "33", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>34, "name"=>"Alvaro Lopez", "firstname"=>"", "lastname"=> "", "phone"=> "34", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>35, "name"=>"Olivia Sanchez", "firstname"=>"", "lastname"=> "", "phone"=> "35", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>36, "name"=>"David Martinez Rentero", "firstname"=>"", "lastname"=> "", "phone"=> "36", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>37, "name"=>"Juan Catalan", "firstname"=>"", "lastname"=> "", "phone"=> "37", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>38, "name"=>"Oscar Marin", "firstname"=>"", "lastname"=> "", "phone"=> "38", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>39, "name"=>"Sergio Garcia", "firstname"=>"", "lastname"=> "", "phone"=> "39", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>40, "name"=>"Juan Luis Arias", "firstname"=>"", "lastname"=> "", "phone"=> "40", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>41, "name"=>"Ricardo Molina", "firstname"=>"", "lastname"=> "", "phone"=> "41", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>42, "name"=>"Ivan Gacto", "firstname"=>"", "lastname"=> "", "phone"=> "42", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>52, "name"=>"Oscar Soto", "firstname"=>"", "lastname"=> "", "phone"=> "52", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>53, "name"=>"Jose Carlos Marin", "firstname"=>"", "lastname"=> "", "phone"=> "53", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>54, "name"=>"David Martinez Garcia", "firstname"=>"", "lastname"=> "", "phone"=> "54", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>55, "name"=>"Fran Martinez", "firstname"=>"", "lastname"=> "", "phone"=> "55", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>56, "name"=>"Antonio Martinez", "firstname"=>"", "lastname"=> "", "phone"=> "56", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>57, "name"=>"Joaquin Ramirez", "firstname"=>"", "lastname"=> "", "phone"=> "57", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>58, "name"=>"Rafael Rubio", "firstname"=>"", "lastname"=> "", "phone"=> "58", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>65, "name"=>"Emilio (Romica)", "firstname"=>"", "lastname"=> "", "phone"=> "65", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>66, "name"=>"Jose Miguel Pardo", "firstname"=>"", "lastname"=> "", "phone"=> "66", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>670287735, "name"=>"Paco Cano", "firstname"=>"", "lastname"=> "", "phone"=> "670287735", "mobile"=> "", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
				["number"=>617455674, "name"=>"Hugo López", "firstname"=>"", "lastname"=> "", "phone"=> "", "mobile"=> "617455674", "email"=> "", "address"=> "", "city"=> "", "state"=> "", "zip"=> "", "comment"=> "", "presence"=> 0, "info"=> ""],
			]];

			return new JsonResponse($array);
	 }

}
