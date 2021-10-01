<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleTaxes;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\ERP\Entity\ERPProviders;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPBuyOrdersUtils;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPBuyOrders;
use App\Modules\ERP\Entity\ERPBuyOrdersLines;
use App\Modules\ERP\Entity\ERPBuyOrdersStates;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPFinancialYears;
use App\Modules\Security\Utils\SecurityUtils;

class ERPBuyOrdersController extends Controller
{

		private $module='ERP';
		private $class=ERPBuyOrders::class;
		private $utilsClass=ERPBuyOrdersUtils::class;


	/**
	 * @Route("/{_locale}/ERP/buyorders", name="buyorders")
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
		$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/BuyOrders.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils->formatForm('buyorders', true, null, $this->class);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/buyorderslist.html.twig', [
				'controllerName' => 'buyordersController',
				'interfaceName' => 'Pedidos de Compra',
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
	 * @Route("/{_locale}/ERP/buyorders/form/{id}", name="ERPBuyOrdersForm", defaults={"id"=0}))
	 */
	public function ERPBuyOrdersForm($id, RouterInterface $router,Request $request)
	{
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

	    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
			$paymentMethodsrepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
			$buyordersrepository=$this->getDoctrine()->getRepository(ERPBuyOrders::class);
			$buyorderslinesrepository=$this->getDoctrine()->getRepository(ERPBuyOrdersLines::class);
			$buyordersstatesrepository=$this->getDoctrine()->getRepository(ERPBuyOrdersStates::class);
			$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
			$agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$destinationstatesRepository=$this->getDoctrine()->getRepository(GlobaleStates::class);
			$destinationcountriesRepository=$this->getDoctrine()->getRepository(GlobaleCountries::class);


			$buyorder=null;
			$buyorderlines=null;


			if($id!=0){

			 $buyorder=$buyordersrepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			 $buyorderlines=$buyorderslinesrepository->findOneBy(["buyorder"=>$buyorder]);
			}

			if($buyorder==null){
				$buyorder=new $this->class();
			//	$buyorderlines=new ERPBuyOrdersLines::class;
			}

			if($request->query->get('code',null)){
				$obj = $documentRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
				if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
				else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}

			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$locale = $request->getLocale();
			$this->router = $router;

			$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

			//Search Suppliers
			$classSuppliersUtils="\App\Modules\ERP\Utils\ERPSuppliersUtils";
			$suppliersutils = new $classSuppliersUtils();
			$supplierslist=$suppliersutils->formatListCustomized($this->getUser());
			$supplierslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
			$supplierslist["topButtons"]=[];


			//stores
			$store_objects=$storesRepository->findBy(["active"=>1,"deleted"=>0]);
			$stores=[];
			$option=null;
			$option["id"]=null;
			$option["text"]="Selecciona Almacén...";
			$stores[]=$option;
			foreach($store_objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$stores[]=$option;
			}

			//agents
			$agent_objects=$agentsRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$agents=[];
			$agents[]=$option;
			foreach($agent_objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName()." ".$item->getLastname();
				$agents[]=$option;
			}


			//states
			$objects=$buyordersstatesrepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$states=[];
			foreach($objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$states[]=$option;
			}

			//destination states
			$objects=$destinationstatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$destinationstates=[];
			foreach($objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$destinationstates[]=$option;
			}

			//destination countries
			$objects=$destinationcountriesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$destinationcountries=[];
			foreach($objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$destinationcountries[]=$option;
			}

			//Search Products
			$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
			$productsutils = new $classProductsUtils();
			$productslist=$productsutils->formatList($this->getUser());
			$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
			$productslist["topButtons"]=[];


			//Search Customers
			$classCustomersUtils="\App\Modules\ERP\Utils\ERPCustomersUtils";
			$customersutils = new $classCustomersUtils();
			$customerslist=$customersutils->formatListWithCode($this->getUser());
			$customerslist["fieldButtons"]=[["id"=>"select", "type" => "success", "default"=>true, "icon" => "fas fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
			$customerslist["topButtons"]=[];
			$customerslist["multiselect"]=false;

    	$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    	$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','BuyOrders');
    	array_push($breadcrumb,$new_breadcrumb);

      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    			return $this->render('@ERP/buyorders.html.twig', [
    				'moduleConfig' => $config,
    				'controllerName' => 'buyordersController',
    				'interfaceName' => 'BuyOrders',
    				'optionSelected' => 'genericindex',
    				'optionSelectedParams' => ["module"=>"ERP", "name"=>"BuyOrders"],
    				'menuOptions' =>  $menurepository->formatOptions($userdata),
    				'breadcrumb' =>  $breadcrumb,
    				'userData' => $userdata,
    				'supplierslist' => $supplierslist,
    				'productslist' => $productslist,
						'form' => 'buyorder',
						'buyorder' => $buyorder,
						'buyorderlines' => $buyorderlines,
						'stores' => $stores,
						'agents' => $agents,
						'states' => $states,
						'destinationstates' => $destinationstates,
						'destinationcountries' => $destinationcountries,
						'customerslist' => $customerslist,
						'id' => $id
    				]);
    		}
    		return new RedirectResponse($this->router->generate('app_login'));

  }

	/**
	 * @Route("/api/buyorders/list", name="buyorderslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/BuyOrders.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, CustomerGroups::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
		return new JsonResponse($return);
	}
}
