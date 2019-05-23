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
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRWorkersUtils;
use App\Modules\HR\Utils\HRWorkCalendarsUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use App\Modules\HR\Entity\HRWorkCalendars;
use App\Modules\HR\Entity\HRHollidays;


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
				'lists' => $templateLists
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
			$utils = new GlobaleFormUtils();
	    $utilsObj=new $this->utilsClass();
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$obj = $workerRepository->find($id);
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
			return $this->render('@Globale/generictabform.html.twig', array(
							'controllerName' => 'WorkersController',
							'interfaceName' => 'Trabajadores',
							'optionSelected' => 'workers',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>true, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
												 ["name" => "paymentroll", "caption"=>"Nóminas"],
												 ["name" => "contracts", "caption"=>"Contratos"],
												 ["name" => "clocks", "caption"=>"Fichajes", "route"=>$this->generateUrl("workerClocks",["id"=>$id])],
												 ["name" => "files", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"workers"])]
												]
							/*'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>$tab=='data'?true:false, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
												 ["name" => "paymentroll", "active"=>($tab=='paymentroll' && $id)?true:false, "caption"=>"Nóminas"]
												]*/
			));
		}





	/**
	 * @Route("/api/HR/workers/list", name="workerslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
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
			dump ($obj);
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
	* @Route("/{_locale}/admin/global/workers/{id}/delete", name="deleteWorker")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}


	/**
	 * @Route("/{_locale}/firebase/send/{token}", name="sendFirebase")
	 */
	 public function sendFirebase($token, Request $request){
		 	$url = "https://fcm.googleapis.com/fcm/send";
			$token = $token;
			$serverKey = 'AAAAf9MGJoU:APA91bE6KicZ68wYAnLBfZcawG1vkO3DBdO24CeVFIW0ctkDGiYMJ7AuDq3I7k6nlqsIGIM-0hkpS9YigFWFAreX2CSlWj1YFHNdu5lFfzqxR1mBJ3FS2gOGJfLRnSfYvSOrgZ6cRgI0';
			$title = "Notification title";
			$body = "Hello I am from Your php server";
			$notification = array('title' =>$title , 'body' => $body, 'data' => 'TEST DATA');
			$arrayToSend = array('to' => $token, 'data' => ['body'=> ['op'=>'logout']], 'priority'=>'high');
			$json = json_encode($arrayToSend);
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Authorization: key='. $serverKey;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
			//Send the request
			$response = curl_exec($ch);
			//Close request
			if ($response === FALSE) {
			die('FCM Send Error: ' . curl_error($ch));
			}
			curl_close($ch);

		return new Response('Sended.');
	}

}
