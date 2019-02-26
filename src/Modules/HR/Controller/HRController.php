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


class HRController extends Controller
{

	 private $class=HRWorkers::class;

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
			$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
			return $utils->make($id, $this->class, $action, "formworker");
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
							'tabs' => [["name" => "data", "caption"=>"Datos trabajador", "active"=>true, "route"=>$this->generateUrl("dataWorker",["id"=>$id])],
												 ["name" => "paymentroll", "caption"=>"Nóminas"]
												]
			));
		}


		/**
     * @Route("/{_locale}/HR/{id}/holidays", name="holidays")
     */
    public function holidays($id, RouterInterface $router, Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$workCalendarRepository=$this->getDoctrine()->getRepository(HRWorkCalendars::class);
		$workCalendar=$workCalendarRepository->find($id);
		if($workCalendar!=NULL) $holidays=$workCalendar->getHollidays(); else $holidays=[];
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@HR/listhollidays.html.twig', [
				'controllerName' => 'HRController',
				'interfaceName' => 'Calendario laboral',
				'optionSelected' => 'workers',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'holidays' => $holidays
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
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
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, $this->class);
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
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, HRWorkCalendars::class);
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
	* @Route("/{_locale}/HR/workcalendar/{id}/save", name="saveWorkCalendar", defaults={"id"=0})
	*/
	public function saveWorkCalendar($id, Request $request){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$utils = new HRWorkCalendarsUtils();
		if(!$id) $obj=new HRWorkCalendars();
			else{
				$repository = $this->getDoctrine()->getRepository(HRWorkCalendars::class);
				$obj=$repository->find($id);
				if($obj===NULL) $obj=new HRWorkCalendars();
			}
		$result=$utils->formatForm($this->getUser(),$obj, $request, $this, $this->getDoctrine(), true);
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/HR/workcalendar/{id}/getform", name="getWorkCalendarform", defaults={"id"=0})
	*/
	public function getWorkCalendarform($id, Request $request){
	if(!$id) $obj=new HRWorkCalendars();
		else{
			$repository = $this->getDoctrine()->getRepository(HRWorkCalendars::class);
			$obj=$repository->find($id);
			if($obj===NULL) $obj=new HRWorkCalendars();
		}
		$formUtils=new GlobaleFormUtils();
		$formUtils->init($this->getDoctrine(),$request);
		$form=$formUtils->createFromEntity($obj, $this, [], [], false)->getForm();
		$formUtils->proccess($form,$obj);
		return $this->render('@Globale/form.html.twig', [
			'formConstructor' =>["form" => $form->createView(), "post"=>$this->generateUrl("saveWorkCalendar",["id"=>$id]) ,"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/WorkCalendars.json"),true)]
			]);

	}
	/**
	* @Route("/{_locale}/HR/workcalendar/{id}/disable", name="disableWorkCalendar")
	*/
	public function disableWorkCalendar($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, HRWorkCalendars::class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/HR/workcalendar/{id}/enable", name="enableWorkCalendar")
	*/
	public function enableWorkCalendar($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, HRWorkCalendars::class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/HR/workcalendar/{id}/delete", name="deleteWorkCalendar")
	*/
	public function deleteWorkCalendar($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, HRWorkCalendars::class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
