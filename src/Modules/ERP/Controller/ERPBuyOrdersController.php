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
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsersConfig;
use App\Modules\ERP\Entity\ERPProviders;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSuppliers;
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
use App\Modules\ERP\Reports\ERPBuyOrdersReports;

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
			$usersConfigRepository=$this->getDoctrine()->getRepository(GlobaleUsersConfig::class);
			$companyRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);

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
			$supplierslist["fieldButtons"]=[["id"=>"select", "type" => "success", "default"=>true, "icon" => "fa fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
			$supplierslist["topButtons"]=[];
			$supplierslist["multiselect"]=false;


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
			$customerslist=$customersutils->formatListCustomized($this->getUser());
			$customerslist["fieldButtons"]=[["id"=>"select", "type" => "success", "default"=>true, "icon" => "fas fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
			$customerslist["topButtons"]=[];
			$customerslist["multiselect"]=false;

    	$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    	$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','BuyOrders');
    	array_push($breadcrumb,$new_breadcrumb);

			// Líneas -------------------------------
			// Búsqueda de vista de usuario
			$user 	 = $this->getUser();
			$company = $this->getUser()->getCompany();
			$tabs 	 = null;
			$tabsUser= $usersConfigRepository->findOneBy(["element"=>"buyorders","view"=>"Defecto","attribute"=>"tabs","active"=>1,"deleted"=>0,"company"=>$company,"user"=>$user]);
			if ($tabsUser!=null){
				$tabs = json_encode($tabsUser->getValue());
			}

			$supplier_id = '1304'; // TODO Se carga de base de datos para este pedido

			$spreadsheet = [];
			$spreadsheet['name']       = "buyorders";
			$spreadsheet['options']    = "pagination:1000000";
		  $spreadsheet['prototipe']  = "{reference:'', description:'', quantity:1, pvp:0, discount:0, shopping_price:'={pvp}-({pvp}*({discount}/100))', total:'={quantity}*{shopping_price}'}";
			if ($tabs!=null){
				$spreadsheet['tabsload'] = 1;
				$spreadsheet['tabs']   	 = $tabs;
			}else{
				$spreadsheet['tabsload'] = 0;
				$spreadsheet['tabs']   		 =
			 "[
				{ caption:'Datos generales',
					columns:[
						{name:'reference'},
						{name:'quantity'},
						{name:'discount'},
						{name:'pvp'},
						{name:'shopping_price'},
						{name:'total'}
					]
				},
				{ caption:'Precios',
					columns:[
						{name:'reference'},
						{name:'pvp'},
						{name:'shopping_price'},
						{name:'total'}
					]
				}
			  ]
			 ";
		 }
		  $spreadsheet['columns']    =
		   "[
		    { name: 'reference', type: 'dropdown', width:'100px', title:'Referencia', autocomplete:true, url: '/api/getWSProductsSupplier/".$supplier_id."',
					options: {
						remoteSearch: true,
						autocomplete: true,
						url: '/api/getWSProductsSupplier/#d|supplier-form-id|value|".$supplier_id."',
						onchange: {
							url: '/api/getWSProductSupplier/#d|supplier-form-id/#c|quantity/#c|reference',
							oncomplete: ''
						}
					}
				},
		    { name: 'description', type: 'text', width:'200px', title: 'Descripcion'},
		    { name: 'quantity', type: 'numeric', width:'100px', title: 'Cantidad' },
		    { name: 'pvp', type: 'text', width:'100px', title: 'PVP', locale: 'sp-SP', mask: '#.##0,0000 €', options: { style:'currency', currency: 'EUR' } },
		    { name: 'discount', type: 'numeric', width:'100px', title: 'Descuento'},
		    { name: 'shopping_price', type: 'text', width:'100px', title: 'Precio compra', readOnly:true , locale: 'sp-SP', mask: '#.##0,0000 €', options: { style:'currency', currency: 'EUR' } },
		    { name: 'total', type: 'text', width:'100px', title: 'Total', readOnly:true, locale: 'sp-SP', mask: '#.##0,0000 €', options: { style:'currency', currency: 'EUR' } }
		   ]";
			// TODO cargar de base de datos
		  $spreadsheet['data']       =
		   "[
		    {reference:'196899~7401195', description:'LOCTITE 577 sellador roscas 50g tubo uso general', quantity:'10',pvp:'20',discount:'50',shopping_price:'={pvp}-({pvp}*({discount}/100))',total:'={quantity}*{shopping_price}'},
		    {reference:'173112~741127213',description:'LOCTITE 596 sellador juntas rojo automovil 80ml', quantity:'5',pvp:'20',discount:'20',shopping_price:'={pvp}-({pvp}*({discount}/100))',total:'={quantity}*{shopping_price}'}
		   ]";
			$spreadsheet['onload'] 	   =
				"$('#supplier-form-id').val('".$supplier_id."');
				 $('#supplier-form-id').on(\"change\", function() {
 				 	if (typeof(document.getElementById('".$spreadsheet['name']."').jexcel[0]) == 'undefined'){
						var sheet = document.getElementById('".$spreadsheet['name']."').jexcel;
					  sheet.insertRow(1, sheet.options.data.length);
					  sheet.deleteRow(0, sheet.options.data.length-1);
					}else{
						for (var i=0; i<document.getElementById('".$spreadsheet['name']."').jexcel.length; i++){
							var sheet = document.getElementById('".$spreadsheet['name']."').jexcel[i];
							sheet.insertRow(1, sheet.options.data.length);
						  sheet.deleteRow(0, sheet.options.data.length-1);
						}
					}
				 });";

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
						'id' => $id,
						'spreadsheet' => $spreadsheet,
						'include_header' => [["type"=>"css", "path"=>"js/jexcel/jexcel.css"],
                                 ["type"=>"js",  "path"=>"js/jexcel/jexcel.js"],
																 ["type"=>"css", "path"=>"js/jsuites/jsuites.css"],
										             ["type"=>"js",  "path"=>"js/jsuites/jsuites.js"]
															 ],
    				]);
    		}
    		return new RedirectResponse($this->router->generate('app_login'));

  }

	/**
	* @Route("/{_locale}/ERP/buyorders/data/{id}/{action}", name="dataBuyOrders", defaults={"id"=0, "action"="read"})
	*/
	public function dataBuyOrders($id, $action, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$buyordersRepository=$this->getDoctrine()->getRepository(ERPBuyOrders::class);
		$buyorderslinesRepository=$this->getDoctrine()->getRepository(ERPBuyOrdersLines::class);
		$buyorderstatesRepository=$this->getDoctrine()->getRepository(ERPBuyOrdersStates::class);
		$suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$globalstatesRepository=$this->getDoctrine()->getRepository(GlobaleStates::class);
		$globalcountriesRepository=$this->getDoctrine()->getRepository(GlobaleCountries::class);

		//Get content of the json reques
		$fields=json_decode($request->getContent());

		$buyorder=$buyordersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		$supplier=$suppliersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->suppliercode, "active"=>1, "deleted"=>0]);
		$customer=null;
		if($fields->customercode)	$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->customercode, "active"=>1, "deleted"=>0]);
		$buyorderstate=$buyorderstatesRepository->findOneBy(["id"=>$fields->state, "active"=>1, "deleted"=>0]);
		$products=null;
		$stores=null;

		$newid=$buyordersRepository->getLastID()+1;
		if(!$buyorder){
			$buyorder=new ERPBuyOrders();
			$buyorder->setActive(1);
			$buyorder->setDeleted(0);
			$buyorder->setAuthor($this->getUser());
			$buyorder->setAgent($this->getUser());
			$buyorder->setDateadd(new \DateTime());

			if($newid<10) $buyorder->setCode("#PC".date("Y")."0000".$newid);
			else if($newid<100) $salesticket->setCode("#PC".date("Y")."000".$newid);
			else if($newid<1000) $salesticket->setCode("#PC".date("Y")."00".$newid);
			else if($newid<10000) $salesticket->setCode("#PC".date("Y")."0".$newid);

		}



		$buyorder->setCompany($this->getUser()->getCompany());
		$buyorder->setDateupd(new \DateTime());
		$buyorder->setState($buyorderstate);

	  $estimateddelivery=date_create_from_format('d/m/Y',$fields->estimateddelivery);
	//	dump("1:".$estimateddelivery);
	//	$dateformatted=\DateTime::createFromFormat('Y-m-d', strtotime($estimateddelivery));
	//	dump("2:".$dateformatted);
		$buyorder->setEstimateddelivery($estimateddelivery);


		$buyorder->setSupplier($supplier);
		$buyorder->setSuppliername($supplier->getName());
		$buyorder->setSuppliercode($supplier->getCode());
		$buyorder->setSupplierdeliverynote($fields->supplierdeliverynote);
		$buyorder->setEmail($fields->supplieremail);
		$buyorder->setPhone($fields->supplierphone);

	  $paymentmethod=$paymentMethodsrepository->findOneBy(["name"=>$fields->supplierpaymentmethod, "active"=>1, "deleted"=>0]);;
		$buyorder->setPaymentmethod($paymentmethod);


		$buyorder->setMinorder($fields->minorder);
		$buyorder->setFreeshipping($fields->freeshipping);
		$buyorder->setAdditionalcost(floatval($fields->additionalcost));
		$buyorder->setWeight($fields->weight);

		$store=$storesRepository->findOneBy(["id"=>$fields->store, "active"=>1, "deleted"=>0]);
  	$buyorder->setStore($store);

		$buyorder->setPriority($fields->priority);
		$buyorder->setReaded(intval($fields->readed));
		if($fields->shippingcharge=="PAGADOS") $buyorder->setShippingcharge(0);
		else $buyorder->setShippingcharge(1);

		 if($fields->typeofconfirmation=="WEB") $buyorder->setTypeofconfirmation(1);
		 else $buyorder->setTypeofconfirmation(0);

	//	$buyorder->setShippingcosts(intval($fields->shippingcosts));

			$destinationstate=$globalstatesRepository->findOneBy(["id"=>$fields->destinationstate, "active"=>1, "deleted"=>0]);
			$destinationcountry=$globalcountriesRepository->findOneBy(["id"=>$fields->destinationcountry, "active"=>1, "deleted"=>0]);


			if($customer){
				$buyorder->setCustomer($customer);

			}
			$buyorder->setDestinationname($fields->destinationname);
			$buyorder->setDestinationaddress($fields->destinationaddress);
			$buyorder->setDestinationphone($fields->destinationphone);
			$buyorder->setDestinationemail($fields->destinationemail);
			$buyorder->setDestinationpostcode($fields->destinationpostcode);
			$buyorder->setDestinationcity($fields->destinationcity);
			$buyorder->setDestinationstate($destinationstate);
			$buyorder->setDestinationcountry($destinationcountry);


			$this->getDoctrine()->getManager()->persist($buyorder);
			$this->getDoctrine()->getManager()->flush();



		return new JsonResponse(["result"=>1,"data"=>["id"=>$buyorder->getId()]]);
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

	/**
	 * @Route("/api/buyorders/print/{id}", name="printBuyOrder", defaults={"id"=0})
	 */
	 public function navisionPrintInvoice($id, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$reportsUtils = new ERPBuyOrdersReports();
		$orderRepository=$this->getDoctrine()->getRepository(ERPBuyOrders::class);
		$orderLinesRepository=$this->getDoctrine()->getRepository(ERPBuyOrdersLines::class);
		$order=$orderRepository->find($id);
		$lines=$orderLinesRepository->findBy(["buyorder"=>$order]);
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$id, "user"=>$this->getUser(), "order"=>$order, "lines"=>$lines];
		$report=$reportsUtils->create($params);
		return new JsonResponse($report);
	 }


}
