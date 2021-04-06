<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSalesTicketsStates;
use App\Modules\ERP\Entity\ERPSalesTicketsHistory;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSalesTicketsUtils;
use App\Modules\ERP\Utils\ERPSalesTicketsHistoryUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPSalesTicketsController extends Controller
{
		private $class=ERPSalesTickets::class;
		private $utilsClass=ERPSalesTicketsUtils::class;
		private $module='ERP';


		/**
     * @Route("/{_locale}/ERP/salestickets", name="salestickets")
     */
    public function index($id, RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  	//	$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new $this->utilsClass();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/SalesTickets.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('salestickets', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'salesTicketsController',
  				'interfaceName' => 'Trazabilidad ventas',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
	        'forms' => $templateForms
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }


		/**
		 * @Route("/{_locale}/ERP/salestickets/form/{id}", name="formSalesTickets", defaults={"id"=0})
		 */
		 public function formSalesTickets($id, RouterInterface $router, Request $request)
		 {
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			 $configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		   $salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
			 $salesticketsstatesRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsStates::class);
			 $agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
	 	   $locale = $request->getLocale();
	 		 $this->router = $router;

			 $config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);


			if($request->query->get('code',null)){
				$obj = $documentRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
				if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
				else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}


			 //Search Customers
	 		$classCustomersUtils="\App\Modules\ERP\Utils\ERPCustomersUtils";
	 		$customersutils = new $classCustomersUtils();
	 		$customerslist=$customersutils->formatListWithCode($this->getUser());
	 		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
	 		$customerslist["topButtons"]=[];


			//Search Sales Orders

			/*
			$salesordersRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
			$salesorderslist=$salesordersRepository->getOrdersWithExternalNumber();
			*/
		 $classSalesOrdersUtils="\App\Modules\ERP\Utils\ERPSalesOrdersUtils";
		 $salesordersutils = new $classSalesOrdersUtils();
		 $salesorderslist=$salesordersutils->formatListWithNumber($this->getUser());

		 $salesorderslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		 $salesorderslist["topButtons"]=[];

			$salesticket=null;
			if($id!=0){
				$salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			}
			if($salesticket==null){
				$salesticket=new $this->class();
			}

			//sales ticket states
			$objects=$salesticketsstatesRepository->findBy(["active"=>1,"deleted"=>0]);
			$states=[];
			$option["id"]=null;
			$option["text"]="Estado del ticket";
			$states[]=$option;
			foreach($objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$states[]=$option;
			}

			//agents
			$agent_objects=$agentsRepository->findBy(["active"=>1,"deleted"=>0]);
			$agents=[];
			$option=null;
			$option["id"]=null;
		  $option["text"]="Elige agente...";
			$agents[]=$option;
			foreach($agent_objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$agents[]=$option;
			}

			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','SalesTickets');
			array_push($breadcrumb,$new_breadcrumb);

			$listSalesTicketsHistory = new ERPSalesTicketsHistoryUtils();

			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				return $this->render('@ERP/salestickets.html.twig', [
					'moduleConfig' => $config,
					'controllerName' => 'categoriesController',
					'interfaceName' => 'SalesTickets',
					'optionSelected' => 'genericindex',
					'optionSelectedParams' => ["module"=>"ERP", "name"=>"SalesTickets"],
					'menuOptions' =>  $menurepository->formatOptions($userdata),
					'breadcrumb' =>  $breadcrumb,
					'userData' => $userdata,
					'customerslist' => $customerslist,
					'salesorderslist' => $salesorderslist,
					'states' => $states,
					'agents' => $agents,
					'ticketType' => 'sales_ticket',
					'salesticket' => $salesticket,
					'salesticketshistorylist' => $listSalesTicketsHistory->formatListByTicket($id),
					'id' => $id,
					]);
			}
			return new RedirectResponse($this->router->generate('app_login'));





	}


	/**
	 * @Route("/{_locale}/ERP/salestickets/data/{id}/{action}", name="dataSalesTickets", defaults={"id"=0, "action"="read"})
	 */
	 public function dataSalesTickets($id, $action, Request $request){
	  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	$salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
		$salesordersRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
	 	$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$salesticketsstatesRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsStates::class);
	 	$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);


	 	$salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);

	 	//Get content of the json reques
	 	$fields=json_decode($request->getContent());
	 	$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->customercode, "active"=>1, "deleted"=>0]);

		$salesticketstate=$salesticketsstatesRepository->findOneBy(["id"=>$fields->salesticketstate, "active"=>1, "deleted"=>0]);
	 //	if(!$customer) return new JsonResponse(["result"=>0]); //if no customer, do nothing


	 	if(!$salesticket){
	 		$salesticket=new ERPSalesTickets();
	 		$salesticket->setActive(1);
	 		$salesticket->setDeleted(0);
	 		$salesticket->setDateadd(new \DateTime());
	 	}

		if($fields->salesticketnewagent!=""){
			$newagent=$agentsRepository->findOneBy(["id"=>$fields->salesticketnewagent,"active"=>1,"deleted"=>0]);
			$salesticket->setAgent($newagent);
		}
		else{
					$salesticket->setAgent($this->getUser());
		}
	 	$salesticket->setCompany($this->getUser()->getCompany());
	 	$salesticket->setCustomer($customer);
		$salesticket->setCustomername($fields->customername);
		if($fields->salesordernumber!="")
		{
			$salesorder=$salesordersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->salesordernumber, "active"=>1, "deleted"=>0]);
			$salesticket->setSalesOrder($salesorder);
		}
		$salesticket->setSalesordernumber($fields->salesordernumber);
		$salesticket->setExternalsalesordernumber($fields->externalsalesordernumber);
		$salesticket->setSalesticketstate($salesticketstate);
		$salesticket->setEmail($fields->email);
		$salesticket->setObservations($fields->observations);
	 	$salesticket->setDateupd(new \DateTime());
	 	$this->getDoctrine()->getManager()->persist($salesticket);


		$history_obj=new ERPSalesTicketsHistory();
		$history_obj->setAgent($this->getUser());
		$history_obj->setSalesTicket($salesticket);
		$history_obj->setObservations($fields->observations);
		$history_obj->setSalesticketstate($salesticketstate);
		$history_obj->setActive(1);
		$history_obj->setDeleted(0);
		$history_obj->setDateupd(new \DateTime());
		$history_obj->setDateadd(new \DateTime());

		$this->getDoctrine()->getManager()->persist($history_obj);
	 	$this->getDoctrine()->getManager()->flush();

	 	return new JsonResponse(["result"=>1,"data"=>["id"=>$salesticket->getId()]]);
	 	//return new JsonResponse(["result"=>1]);

	}


	/**
	 * @Route("/{_locale}/ERP/salestickets/info/{id}/{action}", name="formInfoSalesTickets", defaults={"id"=0, "action"="read"})
	 */
	 public function formInfoSalesTickets($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		 $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
		 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		 $breadcrumb=$menurepository->formatBreadcrumb('salestickets');
		 array_push($breadcrumb, $new_breadcrumb);
		 $template=dirname(__FILE__)."/../Forms/SalesTickets.json";
		 $formUtils = new GlobaleFormUtils();
		 $formUtilsSalesTickets = new ERPSalesTicketsUtils();
	 	 $formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsSalesTickets->getExcludedForm([]),$formUtilsSalesTickets->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$id]));
		 $salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
		 $salesticket=$salesticketsRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);

		// $listSalesTicketsHistory = new ERPSalesTicketsHistoryUtils();

		 return $this->render('@ERP/salestickets.html.twig', array(
			 'controllerName' => 'salesticketsController',
			 'interfaceName' => 'SalesTickets',
			 'optionSelected' => 'salestickets',
			 'form' => $formUtils->formatForm('salestickets', false, $id, $this->class, "dataSalesTickets"),
			 'userData' => $userdata,
			 'id' => $id,
			 'id_object' => $id,
			 /*,
			 'salesticketshistorylist' => $listSalesTicketsHistory->formatListByTickets($id),*/
		 ));

	}


		/**
		 * @Route("/api/salestickets/list", name="salesticketslist")
		 */
		public function indexlist(RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTickets.json"),true);
			$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, SalesTickets::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
			return new JsonResponse($return);
		}



		/**
		* @Route("/api/global/salestickets/{id}/get", name="getSalesTickets")
		*/
		public function getSalesTickets($id){
			$salestickets= $this->getDoctrine()->getRepository($this->class)->findOneById($id);
			if (!$salestickets) {
						throw $this->createNotFoundException('No currency found for id '.$id );
					}
					return new JsonResponse($salestickets->encodeJson());
		}

	/**
	* @Route("/{_locale}/ERP/salestickets/{id}/disable", name="disableSalesTickets")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/ERP/salestickets/{id}/enable", name="enableSalesTickets")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }


 /**
 * @Route("/{_locale}/ERP/salestraceability/{id}/delete", name="deleteSalesTickets")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }


}