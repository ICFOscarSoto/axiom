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
use App\Modules\ERP\Entity\ERPSalesTickets;
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
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
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
		 public function formSalesTickets($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/SalesTickets.json";
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('salestickets');
			array_push($breadcrumb, $new_breadcrumb);
/*
			if($request->query->get('code',null)){
				$obj = $productRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
				if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
				else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}
*/
			$tabs=[["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Sales Tickets data", "active"=>true, "route"=>$this->generateUrl("formInfoSalesTickets",["id"=>$id])]];

		return $this->render('@Globale/generictabform.html.twig', array(
									'controllerName' => 'SalesTicketsController',
									'interfaceName' => 'SalesTickets',
									'optionSelected' => 'salestickets',
									'menuOptions' =>  $menurepository->formatOptions($userdata),
									'breadcrumb' => $breadcrumb,
									'userData' => $userdata,
									'id' => $id,
									'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
									'tabs' => $tabs,
									'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																			["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
									'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
												 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
																			 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
					));


	}


	/**
	 * @Route("/{_locale}/ERP/salestickets/data/{id}/{action}", name="dataSalesTickets", defaults={"id"=0, "action"="read"})
	 */
	 public function dataSalesTickets($id, $action, Request $request){

	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $template=dirname(__FILE__)."/../Forms/SalesTickets.json";
	 $utils = new GlobaleFormUtils();
	 $obj = new $this->class();

	 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
	 $make= $utils->make($id, $this->class, $action, "formSalesTickets", "full", "@Globale/form.html.twig", "formSalesTickets");
	 return $make;
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
