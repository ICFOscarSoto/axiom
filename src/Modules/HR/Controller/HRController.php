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
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HRController extends Controller
{

	 private $class=HRWorkers::class;
	 private $utilsClass=HRWorkersUtils::class;

    /**
     * @Route("/{_locale}/HR/workers", name="workers")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
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
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
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
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
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
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/Workers.json";
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
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
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
												 /*["name" => "clothes", "icon"=>"fa fa-headphones", "caption"=>"Ropa y EPI"],
												 ["name" => "paymentroll", "icon"=>"fa fa-eur", "caption"=>"Nóminas"],
												 ["name" => "contracts", "icon"=>"fa fa-briefcase", "caption"=>"Contratos"],*/
												 ["name" => "sickleave", "icon"=>"fa fa-hospital-o", "caption"=>"Bajas", "route"=>$this->generateUrl("sickleaves",["id"=>$id])],
												 ["name" => "vacations", "icon"=>"fa fa-paper-plane", "caption"=>"Vacaciones", "route"=>$this->generateUrl("vacations",["id"=>$id])],
												 ["name" => "clocks", "icon"=>"fa fa-clock-o", "caption"=>"Fichajes", "route"=>$this->generateUrl("workerClocks",["id"=>$id])],
												 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"workers"])]
												],
							'include_header' => [["type"=>"css", "path"=>"/js/jvectormap/jquery-jvectormap-1.2.2.css"],
																	 ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
																	 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
							'include_footer' => [["type"=>"css", "path"=>"/js/ol/ol.css"],
		 															 ["type"=>"js",  "path"=>"/js/ol/ol.js"],
																	 ["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
										 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
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
    $return=$listUtils->getRecords($this->getUser(),$repository,$request,$manager, $this->class,$filter,-1,["clockcode"]);
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

			if(method_exists($user,'preProccess')) $user->{'preProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			$this->getDoctrine()->getManager()->persist($user);
			$this->getDoctrine()->getManager()->flush();
			if(method_exists($user,'postProccess')) $user->{'postProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			$obj->setUser($user);

			if(method_exists($obj,'preProccess')) $obj->{'preProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());
			$this->getDoctrine()->getManager()->persist($obj);
			$this->getDoctrine()->getManager()->flush();
			if(method_exists($obj,'postProccess')) $obj->{'postProccess'}($this->get('kernel'), $this->getDoctrine(), $this->getUser());

			//return new Response();
			return new JsonResponse(["result"=>1,"worker"=>$obj->getId(), "user"=>$user->getId()]);
		}else{
			return new JsonResponse(["result"=>-1]);
		}
		/*$obj = $this->getDoctrine()->getRepository($this->class)->findById($id);
		if (!$obj) {
			throw $this->createNotFoundException('No worker found for id '.$id );
		}
		return new JsonResponse();
		return new JsonResponse($company->encodeJson());*/
	}



}
