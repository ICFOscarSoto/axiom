<?php

namespace App\Modules\HR\Controller;

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
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRClocksUtils;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\HR\Entity\HRVacations;
use App\Modules\HR\Entity\HRSickleaves;
use App\Modules\HR\Entity\HRHollidays;
use App\Modules\HR\Entity\HRSchedules;
use App\Modules\HR\Reports\HRClocksReports;
use App\Modules\Globale\Entity\GlobaleNotifications;
use App\Modules\Globale\Config\GlobaleConfigVars;
use App\Modules\Globale\Controller\GlobaleFirebaseDevicesController;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;

class HRClocksController extends Controller
{
	 private $module='HR';
	 private $class=HRClocks::class;
	 private $utilsClass=HRClocksUtils::class;

    /**
     * @Route("/{_locale}/HR/clocks", name="clocks")
     */
     public function clocks(RouterInterface $router,Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
     $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
     $locale = $request->getLocale();
     $this->router = $router;
		 $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
		 $worker = $workerRepository->findOneBy(["user"=>$this->getUser(), "company"=>$this->getUser()->getCompany()]);
     $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		 $templateLists[]=[
       'id' => 'listClocks',
       'route' => 'clockslistworker',
       'routeParams' => ["id" => $worker->getId()],
       'orderColumn' => 1,
       'orderDirection' => 'DESC',
       'tagColumn' => 2,
       'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true),
       //'fieldButtons' => [["id"=>"", "type" => "default", "icon" => "default", "actionType" => "foreground", "route"=>""]],
			 'fieldButtons' => [],
       'topButtons' => []
     ];
 		 //$templateLists[]=$utils->formatList($this->getUser());
     if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
       return $this->render('@Globale/genericlist.html.twig', [
         'controllerName' => 'HRClocksController',
         'interfaceName' => 'Fichajes',
         'optionSelected' => "workers",
         'menuOptions' =>  $menurepository->formatOptions($userdata),
         'breadcrumb' =>  "workers",
         'userData' => $userdata,
         'lists' => $templateLists,
				 'include_header' => [["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
															["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
				 'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
         ]);
     } return new RedirectResponse($this->router->generate('app_login'));
     }

		 /**
      * @Route("/{_locale}/HR/clocks/status", name="clocksStatus")
      */
      public function status(RouterInterface $router,Request $request)
      {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
      //$this->denyAccessUnlessGranted('ROLE_ADMIN');
      $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
      $locale = $request->getLocale();
      $this->router = $router;
			$department=$request->query->get("department",0);
			$workcenter=$request->query->get("workcenter",0);
      $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
      $utils = new $this->utilsClass();
			$repositoryDepartment = $this->getDoctrine()->getRepository(HRDepartments::class);
 		  $departments = $repositoryDepartment->findBy(["company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
 		  $repositoryWorkCenters = $this->getDoctrine()->getRepository(HRWorkCenters::class);
 		  $workCenters = $repositoryWorkCenters->findBy(["company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			$repository = $this->getDoctrine()->getManager()->getRepository($this->class);
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				$clocksdata=$repository->findWorkersClocks($this->getUser()->getCompany(),$department,$workcenter);

        return $this->render('@HR/workersclocks.html.twig', [
          'controllerName' => 'HRClocksController',
          'interfaceName' => 'Estado Fichaje',
          'optionSelected' => "clocksStatus",
          'menuOptions' =>  $menurepository->formatOptions($userdata),
          'breadcrumb' =>  $menurepository->formatBreadcrumb('clocksStatus'),
          'userData' => $userdata,
					'clocksList' => $clocksdata,
 				  'departments' => $departments,
					'selectedDepartment' => $department,
 				  'workcenters' => $workCenters,
					'selectedWorkCenter' => $workcenter
          ]);
      } return new RedirectResponse($this->router->generate('app_login'));
      }

			/**
			 * @Route("/{_locale}/HR/{id}/clocks", name="workerClocks")
			 */
			public function index($id,RouterInterface $router,Request $request)
			{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$locale = $request->getLocale();
			$this->router = $router;
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			$obj=$clocksrepository->findOneBy(["id"=>$id]);

			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$utils = new HRClocksUtils();
			$templateLists=$utils->formatListbyWorker($id);
			$formUtils=new GlobaleFormUtils();
			if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPERVISOR')) {
				$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Clocks.json", $request, $this, $this->getDoctrine(),['enddevice','startdevice']);
				$templateLists=$utils->formatListbyWorker($id);
			}else{
				$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/ClocksAdmin.json", $request, $this, $this->getDoctrine());
				$templateLists=$utils->formatListbyWorkerAdmin($id);
			}
			$templateForms[]=$formUtils->formatForm('clocks', true, $id, $this->class);

			/*$utils = new GlobaleFormUtils();
 		  $utils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Clocks.json", $request, $this, $this->getDoctrine());
 		  $templateForms[]= $utils->make($id, $this->class, "read", "formClocks", "modal");
		 	*/
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			$worker=$workersrepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);

			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				return $this->render('@Globale/list.html.twig', [

					'listConstructor' => $templateLists,
					'forms' => $templateForms,
					'worker_id' => $id,
					'userData' => $userdata,
					'data_clocks' => ["Today"=>["data"=>$clocksrepository->todayClocks($worker), "class"=>"tile-blue"],
														"Yesterday"=>["data"=>$clocksrepository->yesterdayClocks($worker), "class"=>"tile-primary"],
													  "This week"=>["data"=>$clocksrepository->thisWeekClocks($worker), "class"=>"tile-blue"],
													  "Last week"=>["data"=>$clocksrepository->lastWeekClocks($worker), "class"=>"tile-primary"],
													  "This month"=>["data"=>$clocksrepository->thisMonthClocks($worker), "class"=>"tile-blue"],
													  "Last month"=>["data"=>$clocksrepository->lastMonthClocks($worker), "class"=>"tile-primary"],
													  "This year"=>["data"=>$clocksrepository->thisYearClocks($worker), "class"=>"tile-blue"],
														"Last year"=>["data"=>$clocksrepository->lastYearClocks($worker), "class"=>"tile-primary"]],
					'include_post_templates' => ['@HR/location.html.twig', '@HR/clocksprintselect.html.twig'],
					'include_pre_templates' => ['@HR/clockssummary.html.twig', '@HR/clockhistory_modal.html.twig']
					]);
			}
			return new RedirectResponse($this->router->generate('app_login'));
			}

			/**
			* @Route("/api/HR/clock/history/{id}", name="historyClocks")
			*/
			public function historyClocks($id, Request $request){
				$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
				$obj=$clocksrepository->findOneBy(["id"=>$id]);
				$historiesRepository=$this->getDoctrine()->getRepository(GlobaleHistories::class);
		    $user = $this->getUser();
		    $histories =	$historiesRepository->findBy(["entity"=>get_class($obj), "entity_id"=>$id, "company"=>$user->getCompany(), "active"=>true, "deleted"=>false],["dateadd"=>'DESC']);
				foreach($histories as $key=>$item){
					$histories[$key]->array_changes=json_decode($item->getChanges(),true);
				}
					return $this->render('@HR/clockhistory.html.twig',[
						'obj'=>$obj,
						'device'=>$obj->getStartdevice(),
						'histories'=>$histories
					]);
			}


		 /**
 		 * @Route("/api/HR/doclock/{company}/{id}", name="doClocks")
 		 */
 		 public function doClocks($company, $id, Request $request){
			date_default_timezone_set('Europe/Madrid');
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			$usersrepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$companiesrepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
			$config=new GlobaleConfigVars();
			$companiesrepository->find($company);

			if($id==0 && $request->request->get('user')!=null){
				$user=$usersrepository->findOneBy(["id"=>$request->request->get('user'), "active"=>1, "deleted"=>0]);
				if($user==null) return new JsonResponse(["result"=>-2]);
				$worker=$workersrepository->findOneBy(["company"=>$company, "user"=>$user, "active"=>1, "deleted"=>0]);
			}else $worker=$workersrepository->findOneBy(["company"=>$company,"clockCode"=>$id]);
			//Comprobamos si el empleado pertenece a la empresa

			if($worker===NULL) return new JsonResponse(["result"=>-1]);
			if($worker->getCompany()->getId()==$company){
				//Comprobamos si hay un fichaje SeekableIterator
				$lastClock=$clocksrepository->findOneBy(["worker"=>$worker, "end"=>NULL, "deleted"=>0, "active"=>1], ['id'=>'DESC']);
				$latitude = $request->request->get("latitude");
				$longitude = $request->request->get("longitude");
				if($lastClock===NULL){
					//Abrimos el fichaje
					$lastClock=new HRClocks();
					$lastClock->setWorker($worker);
					$lastClock->setStartLatitude($latitude);
					$lastClock->setStartLongitude($longitude);
					$lastClock->setStartIPaddress($request->getClientIp());
					$lastClock->setStart(new \DateTime());
					$lastClock->setDateupd(new \DateTime());
					$lastClock->setDateadd(new \DateTime());
					$lastClock->setInvalid(0);
					$lastClock->setActive(1);
					$lastClock->setDeleted(0);
					$this->getDoctrine()->getManager()->persist($lastClock);
          $this->getDoctrine()->getManager()->flush();
					$lastClock->postProccess($this->container->get('kernel'), $this->getDoctrine(), null);
					$notification=new GlobaleNotifications();
					$notification->setUser($worker->getUser());
					setlocale(LC_ALL,"es_ES.utf8");
					$date = new \DateTime();
					$notification->setText("Jornada laboral iniciada el ".strftime('%A %e de %B a las %H:%M:%S',$date->getTimestamp()));
					$notification->setDateadd(new \DateTime());
					$notification->setDateupd(new \DateTime());
					$notification->setReaded(0);
					$notification->setDeleted(0);
					$this->getDoctrine()->getManager()->persist($notification);
          $this->getDoctrine()->getManager()->flush();
					/*$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
					 $url = $this->generateUrl(
	            'sendFirebase',
	            ['id'  => $worker->getUser()->getId(),
 						 'notificationid' => $notification->getId()]
	        );
				  $ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,$config->host.$url);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result= curl_exec ($ch);*/
					return new JsonResponse(["result"=>1, "started"=>$lastClock->getStart(), "startedFormat"=>$lastClock->getStart()->format('d/m/Y H:i')]);
				}else{
					$lastClock->setEndLatitude($latitude);
					$lastClock->setEndLongitude($longitude);
					$lastClock->setEndIPaddress($request->getClientIp());
					$lastClock->setEnd(new \DateTime());
					$lastClock->setDateupd(new \DateTime());
					$lastClock->setTime(date_timestamp_get($lastClock->getEnd())-date_timestamp_get($lastClock->getStart()));
					$this->getDoctrine()->getManager()->persist($lastClock);
          $this->getDoctrine()->getManager()->flush();
					$lastClock->postProccess($this->container->get('kernel'), $this->getDoctrine(), null);
					$notification=new GlobaleNotifications();
					$notification->setUser($worker->getUser());
					setlocale(LC_ALL,"es_ES.utf8");
					$date = new \DateTime();
					$notification->setText("Jornada laboral finalizada el ".strftime('%A %e de %B a las %H:%M:%S',$date->getTimestamp()));
					$notification->setDateadd(new \DateTime());
					$notification->setDateupd(new \DateTime());
					$notification->setReaded(0);
					$notification->setDeleted(0);
					$this->getDoctrine()->getManager()->persist($notification);
          $this->getDoctrine()->getManager()->flush();
/*					$url = $this->generateUrl(
						 'sendFirebase',
						 ['id'  => $worker->getUser()->getId(),
						'notificationid' => $notification->getId()]
				 );
				 $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
				 $ch = curl_init();
				 curl_setopt($ch, CURLOPT_URL,$config->host.$url);
				 curl_setopt($ch, CURLOPT_POST, 1);
				 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				 $result= curl_exec ($ch);*/

				 return new JsonResponse(["result"=>1]);
				}
			}else return new JsonResponse(["result"=>-2]);
 		}


		/**
		 * @Route("/api/HR/notifyclockchange/{id}", name="notifyClockChange")
		 */
		 public function notifyClockChange($id, Request $request){
			 $clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			 $clock=$clocksrepository->find($id);
			 if(!$clock) return new JsonResponse(["result"=>-1]);
			 $clock->postProccess($this->container->get('kernel'), $this->getDoctrine(), null);
			 return new JsonResponse(["result"=>1]);
		 }

		/**
		 * @Route("/api/HR/{id}/clocks/export", name="exportWorkerClocks")
		 */
		 public function exportWorkerClocks($id, Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $utilsExport = new GlobaleExportUtils();
			 $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			 $worker = $workerRepository->find($id);
			 $user = $this->getUser();
	 		 $manager = $this->getDoctrine()->getManager();
	 		 $repository = $manager->getRepository($this->class);
	 		 $listUtils=new GlobaleListUtils();
	 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Clocks.json"),true);
	 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"worker", "value"=>$worker]],[],-1);
			 $result = $utilsExport->export($list,$listFields);
			 return $result;
		 }

		 /**
 		 * @Route("/api/HR/{year}/{month}/clocks/print", name="printWorkerClocks")
 		 */
 		 public function print($year,$month, Request $request){
 			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			 $ids=$request->request->get('ids');
			 $ids=explode(",",$ids);
			 $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "ids"=>$ids, "user"=>$this->getUser(), "year"=>$year, "month"=>$month];
			 $reportsUtils = new HRClocksReports();
			 $pdf=$reportsUtils->create($params);
			 return new Response("", 200, array('Content-Type' => 'application/pdf'));
 		 }

		  /**
			* @Route("/api/HR/{id}/clocks/collection", name="genericapicollection")
			*/
			public function genericapicollection($id, Request $request){
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				$manager = $this->getDoctrine()->getManager();
				$repository = $manager->getRepository($this->class);
				$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
 			 	$worker = $workerRepository->findOneBy(["id"=>$id, "user"=>$this->getUser()]);
				$parameters=$request->query->all();
				$filter=[];
				foreach($parameters as $key => $parameter){
					if(in_array("set".ucfirst($parameter),get_class_methods($this->class)))
						$filter[]=["type"=>"and", "column"=>$key, "value"=>$parameter];
				}
				$listUtils=new GlobaleListApiUtils();
				//if(property_exists($class, "user") && !in_array("ROLE_GLOBAL", $user->getRoles()) && !in_array("ROLE_SUPERADMIN", $user->getRoles()) && !in_array("ROLE_ADMIN", $user->getRoles()))
				//  $return=$listUtils->getRecords($user,$repository,$request,$manager,$class, array_merge([["type"=>"and", "column"=>"user", "value"=>$user]],$filter),[],-1);
				//else if(property_exists($class, "company"))
				//    $return=$listUtils->getRecords($user,$repository,$request,$manager,$class, array_merge([["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]],$filter),[],-1);
				//   else $return=$listUtils->getRecords($user,$repository,$request,$manager, $class,array_merge([],$filter),[],-1);
				$return=$listUtils->getRecords($this->getUser(),$repository,$request,$manager, $this->class,array_merge([["type"=>"and", "column"=>"worker", "value"=>$worker]],$filter),-1,[],["deleted"]);
				foreach($return["data"] as $key=>$value){
					//Pass corrected clocks as valid to APPS
					if($value["invalid"]==2) $return["data"][$key]["invalid"]=0;
				}
				return new JsonResponse($return);
			}

		/**
		 * @Route("/{_locale}/HR/clocks/data/{id}/{action}/{idworker}", name="dataClocks", defaults={"id"=0, "action"="read", "idworker"="0"})
		 */
		 public function data($id, $action, $idworker, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			$obj=$clocksrepository->findOneBy(["id"=>$id]);
			if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPERVISOR')) {
				$template=dirname(__FILE__)."/../Forms/Clocks.json";
			}else{
				if($obj!=NULL)
					if($obj->getInvalid()!=0){
								$template=dirname(__FILE__)."/../Forms/ClocksAdminIncidence.json";
					}else{
							$template=dirname(__FILE__)."/../Forms/ClocksAdmin.json";
					}else $template=dirname(__FILE__)."/../Forms/ClocksAdminIncidence.json";
			}
			$utils = new GlobaleFormUtils();
			$utilsObj=new $this->utilsClass();
			$clockRepository=$this->getDoctrine()->getRepository($this->class);
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			if($id==0){
				if($idworker==0 ) $idworker=$request->query->get('worker');
				if($idworker==0 || $idworker==null) $idworker=$request->request->get('id-parent',0);
				$worker = $workerRepository->find($idworker);
			}	else $obj = $clockRepository->find($id);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[]);
			}

			$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "worker"=>$id==0?$worker:$obj->getWorker()];

			//IF NO INCIDENDE AND SUPERVISOR... NO HISTORY :-)
			if(($obj && $obj->getInvalid()==0) && $this->get('security.authorization_checker')->isGranted('ROLE_SUPERVISOR'))
						$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(), method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[], null, [], [], [], false);
			else $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(), method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[], null, [], [], [], true);

			//-----------------   CLOUD ----------------------
			$utilsCloud = new CloudFilesUtils();
			$path="HRClocks";
			$templateLists=["id"=>$path,"list"=>[$utilsCloud->formatList($this->getUser(),$path,$id)],"types"=>["Justificante asistencia", "Declaraci??n responsable", "Otros"], "path"=>$this->generateUrl("cloudUpload",["id"=>$id, "path"=>$path])];
			//------------------------------------------------
			return $utils->make($id, $this->class, $action, "formworker", "modal", "@Globale/form.html.twig", null, null, ["filesHRClocks"=>["template"=>"@Cloud/genericlistfiles.html.twig", "vars"=>[
				"cloudConstructor"=>$templateLists,
				"scanner"=>$this->getUser()->getScanner(),
				'path' => "HRClocks",
				'id' => $id,
				'module' => "HR"
				]]]);
			//return $utils->make($id, $this->class, $action, "formworker", "modal");
		}

		/**
		 * @Route("/api/HR/clocks/worker/{id}/list", name="clockslistworker")
		 */
		public function clockslistworker($id,RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$user = $this->getUser();
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			//if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
			//	$worker = $workerRepository->findOneBy(["user"=>$user, "company"=>$user->getCompany()]);
			//}else	$worker = $workerRepository->findOneBy(["id"=>$id, "company"=>$user->getCompany()]);
			$worker = $workerRepository->findOneBy(["id"=>$id, "company"=>$user->getCompany()]);
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true);
			$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields,$this->class,[["type"=>"and", "column"=>"worker", "value"=>$worker]]);
			return new JsonResponse($return);
		}

	/**
	 * @Route("/api/HR/clocks/list", name="clockslist")
	 */
	public function listEntity(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
		return new JsonResponse($return);
	}

	/**
	 * @Route("/api/HR/clocks/status/list", name="clocksstatuslist")
	 */
	public function statuslistEntity(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$vacationsRepository=$manager->getRepository(HRVacations::class);
    $sickleaveRepository=$manager->getRepository(HRSickleaves::class);
    $hollidaysRepository=$manager->getRepository(HRHollidays::class);
		$schedulesRepository=$manager->getRepository(HRSchedules::class);
    $workerRepository=$manager->getRepository(HRWorkers::class);
		$repository = $manager->getRepository($this->class);
		$workersClocks=$repository->findWorkersClocks($user->getCompany());
		foreach($workersClocks as $key=>$item){

			$workersClocks[$key]["today"]=$repository->todayClocks($workerRepository->findOneBy(["id"=>$item["workerid"], "deleted"=>0]));
			$workersClocks[$key]["yesterday"]=$repository->yesterdayClocks($workerRepository->findOneBy(["id"=>$item["workerid"], "deleted"=>0]));

			$vacation=$vacationsRepository->dayVacations($workerRepository->find($item["workerid"]), date ("Y-m-d"));
			$workersClocks[$key]["vacation"]=$vacation?$vacation["type"]:0;
			$sickleave=$sickleaveRepository->daySickleave($workerRepository->find($item["workerid"]), date ("Y-m-d"));
			$workersClocks[$key]["sickleave"]=$sickleave?$sickleave["type"]:0;
			$holliday=$hollidaysRepository->dayHolliday($workerRepository->find($item["workerid"]), date ("Y"), date ("Y-m-d"));
			$workersClocks[$key]["holliday"]=$holliday?$holliday["type"]:0;
			$workersClocks[$key]["holliday_name"]=$holliday?$holliday["name"]:null;
			$mustWork=$schedulesRepository->mustWork($workerRepository->find($item["workerid"]), date("Y-m-d"), date("H:i:s"));
			$workersClocks[$key]["must_work"]=$mustWork?1:0;
			$workersClocks[$key]["must_work_start"]=$mustWork?$mustWork["start"]:null;
			$workersClocks[$key]["must_work_end"]=$mustWork?$mustWork["end"]:null;

		}
		return new JsonResponse($workersClocks);
	}

		/**
		 * @Route("/api/HR/clocks/{id}/get", name="getClock")
		 */
		public function getEntity($id){
			$obj = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$obj) {
        throw $this->createNotFoundException('No worker found for id '.$id );
			}
			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
		}

		/**
		 * @Route("/api/HR/clocks/worker/{company}/{id}/status", name="getClockWorkerStatus")
		 */
		public function getClockWorkerStatus($company, $id, Request $request){
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$usersrepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			//Comprobamos si el empleado pertenece a la empresa
			if($id==0 && $request->request->get('user')!=null){
				$user=$usersrepository->findOneBy(["id"=>$request->request->get('user'), "active"=>1, "deleted"=>0]);
				if($user==null) return new JsonResponse(["result"=>-2]);
				$worker=$workersrepository->findOneBy(["user"=>$user, "active"=>1, "deleted"=>0]);
			}else $worker=$workersrepository->findOneBy(["clockCode"=>$id]);
			if($worker===NULL) return new JsonResponse(["result"=>-1]);
			if($worker->getCompany()->getId()==$company){
				//Comprobamos si hay un fichaje SeekableIterator
				$lastClock=$clocksrepository->findOneBy(["worker"=>$worker,"end"=>NULL,"deleted"=>0,"active"=>1], ['start'=>'DESC']);
				if($lastClock===NULL){
					return new JsonResponse(["result"=>0]);
				}else return new JsonResponse(["result"=>1, "started"=>$lastClock->getStart(), "startedFormat"=>$lastClock->getStart()->format('d/m/Y H:i')]);
			} else return new JsonResponse(["result"=>-1]);
		}


		/**
		 * @Route("/api/HR/clocks/worker/getlastclocks", name="getlastclocks")
		 */
		public function getlastclocks(Request $request){
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$usersrepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);

			$user=$this->getUser();
			$worker=$workersrepository->findOneBy(["user"=>$user, "active"=>1, "deleted"=>0]);
			if($worker===NULL) return new JsonResponse(["result"=>-1]);

			$lastClock=$clocksrepository->findBy(["worker"=>$worker,"deleted"=>0,"active"=>1], ['start'=>'DESC'], 5);
			$result=[];
			foreach($lastClock as $clock){
				if($clock->getStartdevice()!=null) $item["startIcon"]="fas fa-fingerprint";
					else if($clock->getStartlongitude()!=null) $item["startIcon"]="fas fa-mobile-alt";
						else $item["startIcon"]="fas fa-desktop";
				$item["startDate"]=$clock->getStart()->format('d/m/Y H:i');
				if($clock->getEnddevice()!=null) $item["endIcon"]="fas fa-fingerprint";
					else if($clock->getEndlongitude()!=null) $item["endIcon"]="fas fa-mobile-alt";
						else $item["endIcon"]="fas fa-desktop";
				$item["endDate"]=$clock->getEnd()!=null?$clock->getEnd()->format('d/m/Y H:i'):"";
				$result[]=$item;
			}

			return new JsonResponse($result);


		}




/**
* @Route("/{_locale}/HR/clocks/{id}/incidence", name="declareIncidence")
*/
public function declareIncidence($id){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
	$obj=$clocksrepository->findOneBy(["id"=>$id]);
  $obj->setInvalid(1);
	$this->getDoctrine()->getManager()->persist($obj);
	$this->getDoctrine()->getManager()->flush();

	$history=new GlobaleHistories();
	$history->setEntity(get_class($obj));
	$history->setEntityId($id);
	$history->setCompany($this->getUser()->getCompany());
	$history->setUser($this->getUser());
	$history->setDateadd(new \DateTime());
	$history->setDateupd(new \DateTime());
	$history->setChanges(json_encode([["attribute"=>"invalid", "oldvalue"=>0, "newvalue"=>1]]));
	$history->setActive(TRUE);
	$history->setDeleted(FALSE);

	$this->getDoctrine()->getManager()->persist($history);
	$this->getDoctrine()->getManager()->flush();

	return new JsonResponse(array('result' => 1));
}


	/**
	* @Route("/{_locale}/HR/clocks/{id}/disable", name="disableClock")
	*/
	public function disable($id){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/HR/clocks/{id}/enable", name="enableClock")
	*/
	public function enable($id){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

		/**
	* @Route("/{_locale}/HR/clocks/{id}/delete", name="deleteClock", defaults={"id"=0})
	*/
	public function delete($id,Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
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

}
