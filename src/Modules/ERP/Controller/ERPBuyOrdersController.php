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
use App\Modules\ERP\Entity\ERPBuyOffert;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPFinancialYears;
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPAddresses;
use App\Modules\ERP\Entity\ERPVariantsValues;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPBuyOrdersReports;
use App\Modules\Cloud\Utils\CloudFilesUtils;

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
	 * @Route("/{_locale}/ERP/buyorders/form/{id}", name="formBuyOrders", defaults={"id"=0}))
	 * Muestra la ficha de un pedido de compra
	 */
	public function formBuyOrders($id, RouterInterface $router,Request $request)
	{
			// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
				return $this->redirect($this->generateUrl('unauthorized'));

			// Variables -----------------
			// Pedido
			$buyorder				= null;
			// Líneas de pedido
			$buyorderlines	= null;
			// Código del pedido, por si se ha pasado como parámetro en la petición en vez del ID
			$code 					= $request->query->get('code',null);
			// Usuario
			$user						= $this->getUser();
			// Compañia
			$company 				= $this->getUser()->getCompany();
			// Datos de usuario
			$userdata				= $this->getUser()->getTemplateData($this, $this->getDoctrine());
			// Router
			$this->router 	= $router;

			// Repositorios --------------
			// Repositorios ERP
			$erpBuyOrdersRepository						= $this->getDoctrine()->getRepository(ERPBuyOrders::class);
			$erpBuyOrdersLinesRepository			= $this->getDoctrine()->getRepository(ERPBuyOrdersLines::class);
			$erpBuyOrdersStatesRepository			= $this->getDoctrine()->getRepository(ERPBuyOrdersStates::class);
			$erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);
			$erpPaymentMethodsRepository			= $this->getDoctrine()->getRepository(ERPPaymentMethods::class);
			$erpStoresRepository							= $this->getDoctrine()->getRepository(ERPStores::class);
			$erpStocksRepository							= $this->getDoctrine()->getRepository(ERPStocks::class);
			$erpContactsRepository						= $this->getDoctrine()->getRepository(ERPContacts::class);
			// Repositorios Globale
			$globaleMenuOptionsRepository			= $this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$globaleUsersRepository						= $this->getDoctrine()->getRepository(GlobaleUsers::class);
			$globaleStatesRepository					= $this->getDoctrine()->getRepository(GlobaleStates::class);
			$globaleCountriesRepository				= $this->getDoctrine()->getRepository(GlobaleCountries::class);
			$globaleUsersConfigRepository			= $this->getDoctrine()->getRepository(GlobaleUsersConfig::class);

			// Si se ha pasado un identificador se busca este y sus líneas
			if ($id!=0){
			 $buyorder			= $erpBuyOrdersRepository->findOneBy(["company"=>$company, "id"=>$id, "active"=>1,"deleted"=>0]);
			 $buyorderlines	= $erpBuyOrdersLinesRepository->findOneBy(["buyorder"=>$buyorder]);
			}
			// Busqueda por código de pedido, se redirecciona a su ID correspondiente
			if($buyorder==null && $code!=null){
			 $buyorder			= $erpBuyOrdersRepository->findOneBy(["company"=>$company, "code"=>$code, "active"=>1,"deleted"=>0]);
			 if ($buyorder)
			 	return $this->redirectToRoute($request->get('_route'), ['id' => $buyorder->getId()]);
			 else
			 	return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}
			// Si id==0, code==null o no se ha encontrado se crea uno nuevo
			if ($buyorder==null){
				$buyorder			 = new $this->class();
				//$buyorderlines = new ERPBuyOrdersLines();
			}

			// Configuración (nº decimales, color...etc)
			$config	= $erpConfigurationRepository->findOneBy(["company"=>$company]);

			// Buscador de proveedores
			$supplierslist =
			  [
					'id' => 'listSuppliersBuyOrders',
					'route' => 'listSuppliersBuyOrders',
					'routeParams' => ['id' => $user->getId()],
					'orderColumn' => 2,
					'orderDirection' => 'ASC',
					'tagColumn' => 3,
					'multiselect' => false,
					'fields' =>
					 [
						['name'=> 'id', 'caption'=>''],
						['name'=> 'code', 'caption'=>'Código'],
						['name'=> 'name', 'caption'=>'Nombre'],
						['name'=> 'address', 'caption'=>'Dirección'],
						['name'=> 'city', 'caption'=>'Localidad'],
						['name'=> 'phone', 'caption'=>'Teléfono']
					 ],
					'fieldButtons' => [['id'=>'select', 'type' => 'success', 'default'=>true, 'icon' => 'fa fa-check', 'name' => 'editar', 'route' => null, 'actionType' => 'background', 'modal'=>'', 'confirm' => false, 'tooltip' =>'Seleccionar']],
					'topButtons' => [],
					'topButtonReload' => false
				];
			// Buscador de clientes
			$customerslist =
				[
					'id' => 'listCustomersBuyOrders',
					'route' => 'listCustomersBuyOrders',
					'routeParams' => ['id' => $user->getId()],
					'orderColumn' => 2,
					'orderDirection' => 'ASC',
					'tagColumn' => 3,
					'multiselect' => false,
					'fields' =>
					 [
						['name'=> 'id', 'caption'=>''],
						['name'=> 'code', 'caption'=>'Código'],
						['name'=> 'name', 'caption'=>'Nombre'],
						['name'=> 'address', 'caption'=>'Dirección'],
						['name'=> 'city', 'caption'=>'Localidad'],
						['name'=> 'phone', 'caption'=>'Teléfono']
					],
					'fieldButtons' => [['id'=>'select', 'type' => 'success', 'default'=>true, 'icon' => 'fa fa-check', 'name' => 'editar', 'route' => null, 'actionType' => 'background', 'modal'=>'', 'confirm' => false, 'tooltip' =>'Seleccionar']],
					'topButtons' => [],
					'topButtonReload' => false
				];

			// Almacenes (combo)
			$ostores=$erpStoresRepository->findBy(["active"=>1,"deleted"=>0]);
			$stores=[];
			$pos = 1;
			foreach($ostores as $item){
				$option=[];
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]='('.$item->getCode().') - '.$item->getName();
				$stores[]=$option;
				$pos++;
			}
			// Agentes y usuarios en general (combo)
			$oagents=$globaleUsersRepository->findBy(["company"=>$company, "active"=>1, "deleted"=>0],["name"=>"ASC"]);
			$agents=[];
			$option=[];
			$option["pos"]=0;
			$option["id"]=null;
			$option["text"]="Selecciona Agente...";
			$agents[]=$option;
			$pos = 1;

			foreach($oagents as $item){
				$option=[];
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName()." ".$item->getLastname();
				$agents[]=$option;
				$pos++;
			}
			// Estados del pedido (combo)
			$obuyordersstates=$erpBuyOrdersStatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$states=[];
			$pos = 1;
			foreach($obuyordersstates as $item){
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$states[]=$option;
				$pos++;
			}
			// Estado (provincia) general - Destinatario (combo)
			$ostates=$globaleStatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$destinationstates=[];
			$option=[];
			$option["pos"]=0;
			$option["id"]=null;
			$option["text"]="Selecciona provincia...";
			$destinationstates[]=$option;
			$pos = 1;
			foreach($ostates as $item){
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$destinationstates[]=$option;
				$pos++;
			}
			// Paises generales - Destinatario (combo)
			$ocountries=$globaleCountriesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$destinationcountries=[];
			$option=[];
			$option["pos"]=0;
			$option["id"]=null;
			$option["text"]="Selecciona país...";
			$destinationcountries[]=$option;
			$pos = 1;
			foreach($ocountries as $item){
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$destinationcountries[]=$option;
				$pos++;
			}

			// Contactos de un proveedor - Destinatario (combo)
			$osuppliercontacts=$erpContactsRepository->findBy(["supplier"=>$buyorder->getSupplier(),"purchaseorder"=>1,"active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$suppliercontacts=[];
			$option=[];
			$option["pos"]=0;
			$option["id"]=null;
			$option["text"]="Selecciona contacto...";
			$suppliercontacts[]=$option;
			$pos = 1;
			foreach($osuppliercontacts as $item){
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$suppliercontacts[]=$option;
				$pos++;
			}
			// Contactos de un cliente - Destinatario (combo)
			$odestinationcontacts=$erpContactsRepository->findBy(["customer"=>$buyorder->getCustomer(),"saleorder"=>1,"active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$destinationcontacts=[];
			$option=[];
			$option["pos"]=0;
			$option["id"]=null;
			$option["text"]="Selecciona contacto...";
			$destinationcontacts[]=$option;
			$pos = 1;
			foreach($odestinationcontacts as $item){
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$destinationcontacts[]=$option;
				$pos++;
			}

			// Miga
    	$nbreadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    	$breadcrumb=$globaleMenuOptionsRepository->formatBreadcrumb('genericindex','ERP','BuyOrders');
    	array_push($breadcrumb,$nbreadcrumb);

			// Líneas -------------------------------
			// Búsqueda de vista de usuario
			$tabs 	 = null;
			$tabsUser= $globaleUsersConfigRepository->findOneBy(["element"=>"buyorders","view"=>"Defecto","attribute"=>"tabs","active"=>1,"deleted"=>0,"company"=>$company,"user"=>$user]);
			if ($tabsUser!=null){
				$tabs = json_encode($tabsUser->getValue());
			}
			// Proveedor
			$supplier_id = ($buyorder->getSupplier()?$buyorder->getSupplier()->getId():0);
			// Decimales
			$ndecimals = 2;
			if ($config != null && $config->getDecimals()!=null)
				$ndecimals = $config->getDecimals();
			$decimals = str_repeat('0',$ndecimals);
			// Fecha estimada
			$dateestimated = ($buyorder->getEstimateddelivery()?$buyorder->getEstimateddelivery()->format('Y-m-d'):'');
			// Almacén por defecto para las líneas
			$buyorderslinestore_id = ($buyorder->getStore()?$buyorder->getStore()->getId().'~'.$buyorder->getStore()->getCode():'0~Almacén...');

			// Líneas
			$olines=$erpBuyOrdersLinesRepository->findBy(["buyorder"=>$buyorder, "active"=>1,"deleted"=>0],["linenum"=>"ASC"]);
			$lines = [];
			if ($olines!=null){
				for($i=0; $i<count($olines); $i++){
					$lines[$i] = [];
					$lines[$i]['id'] 							= (($olines[$i]->getId()!=null)?$olines[$i]->getId():'');
					$lines[$i]['product_id'] 			= (($olines[$i]->getProduct()!=null)?$olines[$i]->getProduct()->getId().'~'.$olines[$i]->getProduct()->getCode():'0~Artículo..');
					$lines[$i]['productname']			= (($olines[$i]->getProductname()!=null)?$olines[$i]->getProductname():'');
					$lines[$i]['variant_id']			= (($olines[$i]->getVariant()!=null)?$olines[$i]->getVariant()->getId().'~'.$olines[$i]->getVariantname().' - '.$olines[$i]->getVariantvalue():'0~Variante...');
					$lines[$i]['quantity']				= (($olines[$i]->getQuantity()!=null)?$olines[$i]->getQuantity():1);
					$lines[$i]['pvp']							= (($olines[$i]->getPvp()!=null)?number_format($olines[$i]->getPvp(),$ndecimals):0);
					$lines[$i]['discount1']				= (($olines[$i]->getDiscount1()!=null)?number_format($olines[$i]->getDiscount1(),$ndecimals):0);
					$lines[$i]['discount2']				= (($olines[$i]->getDiscount2()!=null)?number_format($olines[$i]->getDiscount2(),$ndecimals):0);
					$lines[$i]['discount3']				= (($olines[$i]->getDiscount3()!=null)?number_format($olines[$i]->getDiscount3(),$ndecimals):0);
					$lines[$i]['discount4']				= (($olines[$i]->getDiscount4()!=null)?number_format($olines[$i]->getDiscount4(),$ndecimals):0);
					$lines[$i]['discountequivalent'] = (($olines[$i]->getDiscountequivalent()!=null)?number_format($olines[$i]->getDiscountequivalent(),$ndecimals):0);
					$lines[$i]['totaldiscount']		= (($olines[$i]->getTotaldiscount()!=null)?number_format($olines[$i]->getTotaldiscount(),$ndecimals):0);
					$lines[$i]['store_id']				= (($olines[$i]->getStore()!=null)?$olines[$i]->getStore()->getId().'~'.$olines[$i]->getStore()->getCode():$buyorderslinestore_id);
					$lines[$i]['shoppingprice']		= (($olines[$i]->getshoppingprice()!=null)?number_format($olines[$i]->getShoppingprice(),$ndecimals):0);
					$lines[$i]['subtotal']				= (($olines[$i]->getSubtotal()!=null)?number_format($olines[$i]->getSubtotal(),$ndecimals):0);
					$lines[$i]['taxperc']					= (($olines[$i]->getTaxperc()!=null)?number_format($olines[$i]->getTaxperc(),$ndecimals):0);
					$lines[$i]['taxunit']					= (($olines[$i]->getTaxunit()!=null)?number_format($olines[$i]->getTaxunit(),$ndecimals):0);
					$lines[$i]['total']						= (($olines[$i]->getTotal()!=null)?number_format($olines[$i]->getTotal(),$ndecimals):0);
					$lines[$i]['packing']					= (($olines[$i]->getPacking()!=null)?$olines[$i]->getPacking():1);
					$lines[$i]['multiplicity']		= (($olines[$i]->getMultiplicity()!=null)?$olines[$i]->getMultiplicity():1);
					$lines[$i]['minimumquantityofbuy'] = (($olines[$i]->getminimumquantityofbuy()!=null)?$olines[$i]->getMinimumquantityofbuy():1);
					$lines[$i]['purchaseunit']		= (($olines[$i]->getPurchaseunit()!=null)?$olines[$i]->getPurchaseunit():1);
					$lines[$i]['dateestimated']		= (($olines[$i]->getDateestimated()!=null)?$olines[$i]->getDateestimated()->format('Y-m-d'):$dateestimated->format('Y-m-d'));
					$lines[$i]['weight']					= (($olines[$i]->getWeight()!=null)?number_format($olines[$i]->getWeight(),$ndecimals):0);
					$lines[$i]['purchasemeasure']	= (($olines[$i]->getPurchasemeasure()!=null)?$olines[$i]->getPurchasemeasure():'');

					$stock = null;
					if ($olines[$i]->getProduct()!=null && $olines[$i]->getProduct()->getStockcontrol()){
						// Agrupado por tanto se tiene en cuenta variante sino null
						$stock = $erpStocksRepository->getStock($olines[$i]->getProduct()->getId(),($olines[$i]->getProduct()->getGrouped()?(($olines[$i]->getVariant()!=null?$olines[$i]->getVariant()->getId():0)):null), ($olines[$i]->getStore()!=null?$olines[$i]->getStore()->getId():0));
						if ($stock!=null && is_array($stock)){
							$lines[$i]['stock']					  = ($stock['stock']!=null?$stock['stock']:0);
							$lines[$i]['minstock']				= ($stock['minstock']!=null?$stock['minstock']:0);
							$lines[$i]['stockpedingreceive']= ($stock['stockpedingreceive']!=null?$stock['stockpedingreceive']:0);
							$lines[$i]['stockpedingserve']= ($stock['stockpedingserve']!=null?$stock['stockpedingserve']:0);
							$lines[$i]['stockvirtual']		= ($stock['stockvirtual']!=null?$stock['stockvirtual']:0);
							$lines[$i]['stockt']					  = ($stock['stockt']!=null?$stock['stockt']:0);
							$lines[$i]['stockpedingreceivet']= ($stock['stockpedingreceivet']!=null?$stock['stockpedingreceivet']:0);
							$lines[$i]['stockpedingservet']= ($stock['stockpedingservet']!=null?$stock['stockpedingservet']:0);
							$lines[$i]['stockvirtualt']		= ($stock['stockvirtualt']!=null?$stock['stockvirtualt']:0);
						}
					}
					if ($stock==null){
						$lines[$i]['stock']					  = 0;
						$lines[$i]['minstock']				= 0;
						$lines[$i]['stockpedingreceive']= 0;
						$lines[$i]['stockpedingserve']= 0;
						$lines[$i]['stockvirtual']		= 0;
						$lines[$i]['stockt']					= 0;
						$lines[$i]['stockpedingreceivet']	= 0;
						$lines[$i]['stockpedingservet'] = 0;
						$lines[$i]['stockvirtualt']		= 0;
					}
				}
			}

			$spreadsheet = [];
			$spreadsheet['name']       = "buyorders";
			$spreadsheet['options']    = "pagination:1000000";
		  $spreadsheet['prototipe']  = "{
				id:'',
				product_id:'0~Artículo...',
				productname:'',
				variant_id:'0~Variante...',
				quantity:1,
				pvp:0.$decimals,
				discount1:0.$decimals,
				discount2:0.$decimals,
				discount3:0.$decimals,
				discount4:0.$decimals,
				discountequivalent:0.$decimals,
				totaldiscount:0.$decimals,
				store_id:'$buyorderslinestore_id',
				shoppingprice:0.$decimals,
				subtotal:0.$decimals,
				taxperc:0,
				taxunit:0.$decimals,
				total:0.$decimals,
				packing:1,
				multiplicity:1,
				minimumquantityofbuy:1,
				purchaseunit:1,
				dateestimated:'$dateestimated',
				weight:0,
				purchasemeasure:'',

				stock:0,
				minstock:0,
				stockpedingreceive:0,
				stockpedingserve:0,
				stockvirtual:0,
				stockt:0,
				stockpedingreceivet:0,
				stockpedingservet:0,
				stockvirtualt:0
			}";
			if ($tabs!=null){
				$spreadsheet['tabsload'] = 1;
				$spreadsheet['tabs']   	 = $tabs;
			}else{
				$spreadsheet['tabsload'] = 0;
				$spreadsheet['tabs']   		 =
			 "[
				{ caption:'Datos generales',
					columns:[
						{name:'product_id'},
						{name:'productname'},
						{name:'variant_id'},
						{name:'store_id'},
						{name:'stock'},
						{name:'quantity'},
						{name:'purchasemeasure'},
						{name:'pvp'},
						{name:'discountequivalent'},
						{name:'shoppingprice'},
						{name:'taxunit'},
						{name:'total'}
					]
				},
				{ caption:'Cantidades',
					columns:[
						{name:'product_id'},
						{name:'productname'},
						{name:'variant_id'},
						{name:'store_id'},
						{name:'minstock'},
						{name:'stock'},
						{name:'stockpedingreceive'},
						{name:'stockpedingserve'},
						{name:'stockvirtual'},
						{name:'stockt'},
						{name:'stockpedingreceivet'},
						{name:'stockpedingservet'},
						{name:'stockvirtualt'}
					]
				},
				{ caption:'Descuentos',
					columns:[
						{name:'product_id'},
						{name:'productname'},
						{name:'variant_id'},
						{name:'discount1'},
						{name:'discount2'},
						{name:'discount3'},
						{name:'discount4'},
						{name:'discountequivalent'},
						{name:'totaldiscount'}
					]
				},
				{ caption:'Embalaje',
					columns:[
						{name:'product_id'},
						{name:'productname'},
						{name:'variant_id'},
						{name:'quantity'},
						{name:'purchasemeasure'},
						{name:'purchaseunit'},
						{name:'packing'},
						{name:'multiplicity'},
						{name:'minimumquantityofbuy'}
					]
				}
			  ]
			 ";
		 }
		  $spreadsheet['columns']    =
		   "[
				{ name: 'id', type: 'numeric', width:'50px', title: 'ID', align: 'left'},
		    { name: 'product_id', type: 'dropdown', width:'100px',
					title:'Código', autocomplete:true,
					url: '/api/getWSProductsSupplier/".$supplier_id."',
					align: 'left',
					options: {
						remoteSearch: true,
						autocomplete: true,
						url: '/api/getWSProductsSupplier/#d|supplier-form-id|value|".$supplier_id."',
						onchange: {
							url: '/api/getWSProductSupplier/#d|supplier-form-id/#c|quantity/#c|product_id/#c|store_id',
							oncomplete: 'buyordersproduct'
						}
					}
				},
		    { name: 'productname', type: 'text', width:'250px', title: 'Descripción', align: 'left'},
				{ name: 'variant_id', type: 'dropdown', width:'100px',
					title:'Variante', autocomplete:true,
					align:'left',
					url: '/api/getWSProductVariants/___',
					options: {
						onchange: {
							url: '/api/getWSProductVariantPriceStock/#d|supplier-form-id/#c|product_id/#c|variant_id/#c|quantity/#c|store_id',
							oncomplete: 'buyordersline'
						}
					}
				},
		    { name: 'quantity', type: 'numeric', width:'100px', title: 'Cantidad', align: 'right',
					options: {
						onchange: {
							url: '/api/getWSProductVariantPrice/#d|supplier-form-id/#c|product_id/#c|variant_id/#c|quantity',
							oncomplete: 'buyordersline'
						}
					}
			  },
		    { name: 'pvp', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Precio compra (€)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buyordersline'
						}
					}},
				{ name: 'discount1', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto 1 (%)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buyordersline'
						}
					}},
				{ name: 'discount2', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto 2 (%)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buyordersline'
						}
					}},
				{ name: 'discount3', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto 3 (%)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buyordersline'
						}
					}},
				{ name: 'discount4', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto 4 (%)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buyordersline'
						}
					}},
		    { name: 'discountequivalent', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto equivalente (%)', readOnly:true, align: 'right'},
				{ name: 'totaldiscount', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto total (€)', readOnly:true, align: 'right' },
				{ name: 'store_id', type: 'dropdown', width:'100px',
					title:'Almacén', autocomplete:true,
					align:'left',
					url: '/api/getWSProductStores/0',
					options: {
						onchange: {
							url: '/api/getWSProductStock/#c|product_id/#c|variant_id/#c|store_id'
						}
					}
				},
				{ name: 'shoppingprice', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Importe (€)', readOnly:true, align: 'right'  },
				{ name: 'subtotal', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Subtotal (€)', readOnly:true , align: 'right' },
				{ name: 'taxperc', type: 'text', width:'100px', title: 'IVA (%)', readOnly:true, align: 'right' },
				{ name: 'taxunit', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'IVA (€)', readOnly:true, align: 'right'  },
		    { name: 'total', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Total (€)', readOnly:true , align: 'right'},
				{ name: 'packing', type: 'numeric', width:'100px', title: 'Packing', readOnly:true, align: 'right' },
				{ name: 'multiplicity', type: 'numeric', width:'100px', title: 'Multiplicidad', readOnly:true, align: 'right' },
				{ name: 'minimumquantityofbuy', type: 'numeric', width:'100px', title: 'Mínimo compra', readOnly:true, align: 'right' },
				{ name: 'purchaseunit', type: 'numeric', width:'100px', title: 'Unidad compra', readOnly:true , align: 'right'},
				{ name: 'dateestimated', type: 'calendar', width:'100px', title: 'Días estimados', align: 'left',
					options: {
						format:'DD/MM/YYYY',
						months:['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
						monthsFull:['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
						weekdays:['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sábado'],
						weekdays_short:['D', 'L', 'M', 'X', 'J', 'V', 'S'],
						resetButton:false, textDone:'Cerrar', textUpdate:'Aceptar', startingDay:1
					}
				},
				{ name: 'weight', type: 'numeric', decimal: '".$ndecimals."', width:'70px', title: 'Peso', readOnly:true, align: 'right' },
				{ name: 'purchasemeasure', type: 'text', width:'50px', title: 'UM', readOnly:true, align: 'left' },

				{ name: 'minstock', type: 'numeric', width:'50px', title: 'Min. stock', readOnly:true , align: 'right'},
				{ name: 'stock', type: 'numeric', width:'50px', title: 'Stock', readOnly:true , align: 'right'},
				{ name: 'stockpedingreceive', type: 'numeric', width:'50px', title: 'P. recibir', readOnly:true , align: 'right'},
				{ name: 'stockpedingserve', type: 'numeric', width:'50px', title: 'P. servir', readOnly:true, align: 'right' },
				{ name: 'stockvirtual', type: 'numeric', width:'50px', title: 'S. virtual', readOnly:true , align: 'right'},
				{ name: 'stockt', type: 'numeric', width:'50px', title: 'S. total', readOnly:true , align: 'right'},
				{ name: 'stockpedingreceivet', type: 'numeric', width:'50px', title: 'P. recibir total', readOnly:true , align: 'right'},
				{ name: 'stockpedingservet', type: 'numeric', width:'50px', title: 'P. servir total', readOnly:true , align: 'right'},
				{ name: 'stockvirtualt', type: 'numeric', width:'50px', title: 'S. virtual total', readOnly:true, align: 'right' }
		   ]";
			// Cargar de base de datos
			$spreadsheet['data']       = json_encode($lines);
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
				 });
				 var sheet = null;
				 if (typeof(document.getElementById('".$spreadsheet['name']."').jexcel[0]) == 'undefined')
				 	sheet = document.getElementById('".$spreadsheet['name']."').jexcel;
				 else
				 	sheet = document.getElementById('".$spreadsheet['name']."').jexcel[0];
				 var data    = sheet.getData();
				 var columns = sheet.options.columns;
				 if (data.length > 0 && columns.length>0){
				 	for(var i=0; i<data.length; i++){
				 		product_code = data[i][sheet.getColumnKey('product_id')].split('~')[1];
				 		if (product_code!=null && product_code!='' && product_code!='0'  && product_code!=0){
				 			var url = sheet.options.columns[sheet.getColumnKey('variant_id')].url;
				 			if (url !== undefined && url != ''){
				 				// Obtener variantes del producto que se ha modificado
				 				url = url.replace('___',i+'~'+product_code);
				 				$.ajax({
				 					url: url
				 				}).done(function(result) {
				 					if (result != null && result != ''){
				 						var oresult = JSON.parse(JSON.stringify(result.data));
										var posvariant_id = sheet.getColumnKey('variant_id');
				 						//sheet.options.columns[posvariant_id].source = oresult;
										if (typeof(sheet.options.columns[posvariant_id]['sources']) == 'undefined')
											sheet.options.columns[posvariant_id]['sources']= [];
										sheet.options.columns[posvariant_id]['sources'][result.line] =	oresult;
										if (typeof(sheet.options.columnso[3]['sources']) == 'undefined')
											sheet.options.columnso[3]['sources'] = [];
										sheet.options.columnso[3]['sources'][result.line] =	oresult;
										for (var i=1; i<document.getElementById('".$spreadsheet['name']."').jexcel.length; i++){
											var sheetothers = document.getElementById('".$spreadsheet['name']."').jexcel[i];
											if (!sheetothers.options.columns[sheetothers.getColumnKey('variant_id')].sources)
												sheetothers.options.columns[sheetothers.getColumnKey('variant_id')].sources = [];
											sheetothers.options.columns[sheetothers.getColumnKey('variant_id')].sources[result.line] =	oresult;
										}
				 					}
				 				});
				 			}
				 		}
				 	}
				}";

			// Files
			$utilsCloud = new CloudFilesUtils();
			$path="Buyorders";
			$templateLists=["id"=>$path,"list"=>[$utilsCloud->formatList($this->getUser(),$path,$id)],"types"=>["Otros"], "path"=>$this->generateUrl("cloudUpload",["id"=>$id, "path"=>$path])];

      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    			return $this->render('@ERP/buyorders.html.twig', [
    				'moduleConfig' => $config,
    				'controllerName' => 'buyordersController',
    				'interfaceName' => 'BuyOrders',
    				'optionSelected' => 'genericindex',
    				'optionSelectedParams' => ["module"=>"ERP", "name"=>"BuyOrders"],
    				'menuOptions' =>  $globaleMenuOptionsRepository->formatOptions($userdata),
    				'breadcrumb' =>  $breadcrumb,
    				'userData' => $userdata,
    				'supplierslist' => $supplierslist,
						'suppliercontacts' => $suppliercontacts,
						'form' => 'buyorder',
						'buyorder' => $buyorder,
						'buyorderlines' => $buyorderlines,
						'stores' => $stores,
						'agents' => $agents,
						'states' => $states,
						'destinationstates' => $destinationstates,
						'destinationcountries' => $destinationcountries,
						'destinationcontacts' => $destinationcontacts,
						'customerslist' => $customerslist,
						'id' => $id,
						'spreadsheet' => $spreadsheet,
						'cloudConstructor' => $templateLists,
						'include_header' => [["type"=>"css", "path"=>"js/jexcel/jexcel.css"],
                                 ["type"=>"js",  "path"=>"js/jexcel/jexcel.js"],
																 ["type"=>"css", "path"=>"js/jsuites/jsuites.css"],
										             ["type"=>"js",  "path"=>"js/jsuites/jsuites.js"],
																 ["type"=>"css", "path"=>"js/dropzone/dropzone.css"],
																 ["type"=>"js", "path"=>"js/dropzone/dropzone.js"],
																 ["type"=>"js", "path"=>"js/dropzone/dropzone-es.js"]
															 ],
    				]);
    		}
    		return new RedirectResponse($this->router->generate('app_login'));

  }

	/**
	* @Route("/{_locale}/ERP/buyorders/save", name="saveBuyOrders")
	*/
	public function saveBuyOrders(Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$result = false;

		// Parámetros --------------------------------------
		// Objeto con todos los datos del formulario
		$ojsbuyorders=json_decode($request->getContent(), true);

		if ($ojsbuyorders!=null){
			// Repositorios ------------------------------------
			$erpBuyOrdersRepository=$this->getDoctrine()->getRepository(ERPBuyOrders::class);
			$erpBuyOrdersLinesRepository=$this->getDoctrine()->getRepository(ERPBuyOrdersLines::class);
			$erpBuyOrdersStatesRepository=$this->getDoctrine()->getRepository(ERPBuyOrdersStates::class);
			$erpBuyOffertsRepository=$this->getDoctrine()->getRepository(ERPBuyOffert::class);
			$erpSuppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
			$erpCustomersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
			$erpPaymentMethodsRepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
			$erpStoresRepository=$this->getDoctrine()->getRepository(ERPStores::class);
			$erpContactsRepository=$this->getDoctrine()->getRepository(ERPContacts::class);
			$erpAddressesRepository=$this->getDoctrine()->getRepository(ERPAddresses::class);
			$erpProductsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$erpProducsVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$erpVariantsValuesRepository=$this->getDoctrine()->getRepository(ERPVariantsValues::class);
			$erpVariantsRepository=$this->getDoctrine()->getRepository(ERPVariants::class);
			$erpReferencesRepository=$this->getDoctrine()->getRepository(ERPReferences::class);
			// Globales
			$globaleCompaniesRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
			$globaleUsersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$globaleStatesRepository=$this->getDoctrine()->getRepository(GlobaleStates::class);
			$globaleCountriesRepository=$this->getDoctrine()->getRepository(GlobaleCountries::class);

			$buyorder	= null;
			$id 			= $ojsbuyorders['id'];
			if ($id!='' && $id!='0'){
				$buyorder	= $erpBuyOrdersRepository->find($id);
			}
			if ($buyorder==null){
				$buyorder = new ERPBuyOrders();
				$company 	= $globaleCompaniesRepository->find($ojsbuyorders['company_id']);
				$author 	= $globaleUsersRepository->find($ojsbuyorders['author_id']);
				$code 		= $erpBuyOrdersRepository->getNextCode();
				$buyorder->setCompany($company);
				$buyorder->setCode($code);
				// TODO
				$buyorder->setRevision(1);
				$buyorder->setActive(1);
				$buyorder->setDeleted(0);
				$buyorder->setAuthor($author);
				$buyorder->setDateadd(new \DateTime());
			}
			// Generales
			$agent 	= $globaleUsersRepository->find($ojsbuyorders['agent_id']);
			$state 	= $erpBuyOrdersStatesRepository->find($ojsbuyorders['state_id']);
			$buyorder->setAgent($agent);
			$buyorder->setState($state);
			$buyorder->setDateupd(new \DateTime());
			$buyorder->setTheircode($ojsbuyorders['theircode']);
			$buyorder->setPriority($ojsbuyorders['priority']);
			$buyorder->setObservationpriority($ojsbuyorders['observationpriority']);
			$buyorder->setWeight($ojsbuyorders['weight']);
			$buyorder->setTypeofconfirmation($ojsbuyorders['typeofconfirmation']);
			if ($ojsbuyorders['amount']!='')
				$buyorder->setAmount(floatval($ojsbuyorders['amount']));
			else
				$buyorder->setAmount(null);
			if ($ojsbuyorders['discount']!='')
				$buyorder->setDiscount(floatval($ojsbuyorders['discount']));
			else
				$buyorder->setDiscount(null);
			if ($ojsbuyorders['base']!='')
				$buyorder->setBase(floatval($ojsbuyorders['base']));
			else
				$buyorder->setBase(null);
			if ($ojsbuyorders['taxes']!='')
				$buyorder->setTaxes(floatval($ojsbuyorders['taxes']));
			else
				$buyorder->setTaxes(null);
			if ($ojsbuyorders['shipping']!='')
				$buyorder->setShipping(floatval($ojsbuyorders['shipping']));
			else
				$buyorder->setShipping(null);
			if ($ojsbuyorders['total']!='')
				$buyorder->setTotal(floatval($ojsbuyorders['total']));
			else
				$buyorder->setTotal(null);
			$buyorder->setObservationpublic($ojsbuyorders['observationpublic']);
	    // Fechas
			if ($ojsbuyorders['dateread']!='')
				$buyorder->setDateread(new \DateTime($ojsbuyorders['dateread']));
			else
				$buyorder->setDateread(null);
			if ($ojsbuyorders['datesend']!='')
	   		$buyorder->setDatesend(new \DateTime($ojsbuyorders['datesend']));
			else
				$buyorder->setDatesend(null);
			if ($ojsbuyorders['dateconfirmed']!='')
				$buyorder->setDateconfirmed(new \DateTime($ojsbuyorders['dateconfirmed']));
			else
				$buyorder->setDateconfirmed(null);
			if ($ojsbuyorders['estimateddelivery']!='')
				$buyorder->setEstimateddelivery(new \DateTime($ojsbuyorders['estimateddelivery']));
			else
				$buyorder->setEstimateddelivery(null);
			// Proveedor
			$supplier 			= $erpSuppliersRepository->find($ojsbuyorders['supplier_id']);
			$paymentmethod 	= $erpPaymentMethodsRepository->find($ojsbuyorders['paymentmethod_id']);
			$suppliercontact= $erpContactsRepository->find($ojsbuyorders['suppliercontact_id']);
			$buyorder->setSupplier($supplier);
			$buyorder->setPaymentmethod($paymentmethod);
			$buyorder->setSuppliercontact($suppliercontact);
			$buyorder->setSuppliername($ojsbuyorders['suppliername']);
			$buyorder->setSuppliercode($ojsbuyorders['suppliercode']);
			$buyorder->setEmail($ojsbuyorders['email']);
			$buyorder->setPhone($ojsbuyorders['phone']);
			$buyorder->setMinorder($ojsbuyorders['minorder']);
			$buyorder->setFreeshipping($ojsbuyorders['freeshipping']);
			$buyorder->setShippingcharge($ojsbuyorders['shippingcharge']);
			$buyorder->setSupplieraddress($ojsbuyorders['supplieraddress']);
			$buyorder->setSupplierpostcode($ojsbuyorders['supplierpostcode']);
			$buyorder->setSupplierstate($ojsbuyorders['supplierstate']);
			$buyorder->setSuppliercountry($ojsbuyorders['suppliercountry']);
			$buyorder->setSuppliercity($ojsbuyorders['suppliercity']);
			$buyorder->setSuppliervat($ojsbuyorders['suppliervat']);
			$buyorder->setSupplierpaymentterms($ojsbuyorders['supplierpaymentterms']);
			$buyorder->setSuppliercontactname($ojsbuyorders['suppliercontactname']);
			$buyorder->setSuppliercontactemail($ojsbuyorders['suppliercontactemail']);
			$buyorder->setSuppliercontactphone($ojsbuyorders['suppliercontactphone']);
			// Destino y cliente
			$store 				= $erpStoresRepository->find($ojsbuyorders['store_id']);
			$customer 		= $erpCustomersRepository->find($ojsbuyorders['customer_id']);
			$destination 	= $erpAddressesRepository->find($ojsbuyorders['destination_id']);
			$destinationstate 	= $globaleStatesRepository->find($ojsbuyorders['destinationstate_id']);
			$destinationcountry = $globaleCountriesRepository->find($ojsbuyorders['destinationcountry_id']);
			$destinationcontact = $erpContactsRepository->find($ojsbuyorders['destinationcontact_id']);
			$buyorder->setStore($store);
			$buyorder->setCustomer($customer);
			$buyorder->setDestination($destination);
			$buyorder->setDestinationstate($destinationstate);
			$buyorder->setDestinationcountry($destinationcountry);
			$buyorder->setDestinationcontact($destinationcontact);
			$buyorder->setDestinationname($ojsbuyorders['destinationname']);
			$buyorder->setDestinationaddress($ojsbuyorders['destinationaddress']);
			$buyorder->setDestinationphone($ojsbuyorders['destinationphone']);
			$buyorder->setDestinationemail($ojsbuyorders['destinationemail']);
			$buyorder->setDestinationpostcode($ojsbuyorders['destinationpostcode']);
			$buyorder->setDestinationcity($ojsbuyorders['destinationcity']);
			$buyorder->setDestinationcontactname($ojsbuyorders['destinationcontactname']);
			$buyorder->setDestinationcontactemail($ojsbuyorders['destinationcontactemail']);
			$buyorder->setDestinationcontactphone($ojsbuyorders['destinationcontactphone']);
	    // TODO Offert
			$offert = $erpBuyOffertsRepository->find($ojsbuyorders['offert_id']);
			$buyorder->setOffert($offert);

			$this->getDoctrine()->getManager()->persist($buyorder);
			$this->getDoctrine()->getManager()->flush();
			$result = $buyorder->getId();

			// Líneas
			// Poner todas las líneas de la base de datos a linenum=0
			$erpBuyOrdersLinesRepository->setLinenum($result);
			$lines =  $ojsbuyorders['lines'];
			if ($lines!=null && is_array($lines)){
				for($i=0; $i<count($lines); $i++){
					$buyorderline = null;
					$line 				= $lines[$i];
					$id 					= $line['id'];
					if ($id!='' && $id!='0'){
						$buyorderline	= $erpBuyOrdersLinesRepository->find($id);
					}
					if ($buyorderline==null){
						$buyorderline = new ERPBuyOrdersLines();
						$buyorderline->setActive(1);
						$buyorderline->setDeleted(0);
						$buyorderline->setDateadd(new \DateTime());
					}
					// Generales
					$product 					= $erpProductsRepository->find($line['product_id']);
					$variantvalue			= $erpVariantsValuesRepository->find($line['variant_id']);
					$productvariant 	= $erpProducsVariantsRepository->findOneBy(["product"=>$product,"variantvalue"=>$variantvalue,"active"=>"1","deleted"=>"0"]);
					$variant 					= null;
					if ($variantvalue!=null)
						$variant 				= $erpVariantsRepository->find($variantvalue->getVariantname());
					$store 						= $erpStoresRepository->find($line['store_id']);
					$reference				= $erpReferencesRepository->findOneBy(["supplier"=>$supplier,"product"=>$product, "productvariant"=>$productvariant,"active"=>"1","deleted"=>"0"]);
					if ($reference==null)
							$reference= $erpReferencesRepository->findOneBy(["supplier"=>$supplier,"product"=>$product,"active"=>"1","deleted"=>"0"]);
					$buyorderline->setBuyOrder($buyorder);
					$buyorderline->setProduct($product);
					$buyorderline->setVariant($variantvalue);
					$buyorderline->setDateupd(new \DateTime());
					$buyorderline->setLinenum($line['linenum']);
					$buyorderline->setCode($line['code']);
					$buyorderline->setProductname($line['productname']);
					$buyorderline->setQuantity($line['quantity']);
					$buyorderline->setPvp($line['pvp']);
					$buyorderline->setDiscount1($line['discount1']);
					$buyorderline->setDiscount2($line['discount2']);
					$buyorderline->setDiscount3($line['discount3']);
					$buyorderline->setDiscount4($line['discount4']);
					$buyorderline->setDiscountequivalent($line['discountequivalent']);
					$buyorderline->setTotaldiscount($line['totaldiscount']);
					$buyorderline->setShoppingprice($line['shoppingprice']);
					$buyorderline->setStore($store);
					$buyorderline->setSubtotal($line['subtotal']);
					$buyorderline->setTaxperc($line['taxperc']);
					$buyorderline->setTaxunit($line['taxunit']);
					$buyorderline->setTotal($line['total']);
					$buyorderline->setPacking($line['packing']);
					$buyorderline->setMultiplicity($line['multiplicity']);
					$buyorderline->setMinimumquantityofbuy($line['minimumquantityofbuy']);
					$buyorderline->setPurchaseunit($line['purchaseunit']);
					if ($line['dateestimated']!='')
						$buyorderline->setDateestimated(new \DateTime($line['dateestimated']));
					else
						$buyorderline->setDateestimated(null);

					$buyorderline->setWeight($line['weight']);
					$buyorderline->setPurchasemeasure($line['purchasemeasure']);
					if ($reference!=null)
						$buyorderline->setSupplierreference($reference->getName());
					else
						$buyorderline->setSupplierreference('');
					if ($store!=null){
						$buyorderline->setStorecode($store->getCode());
						$buyorderline->setStorename($store->getName());
					}else{
						$buyorderline->setStorecode('');
						$buyorderline->setStorename('');
					}
					if ($variant!=null){
						$buyorderline->setVariantname($variant->getName());
					}else{
						$buyorderline->setVariantname('');
					}
					if ($variantvalue!=null){
						$buyorderline->setVariantvalue($variantvalue->getName());
					}else{
						$buyorderline->setVariantvalue('');
					}

					$this->getDoctrine()->getManager()->persist($buyorderline);
					$this->getDoctrine()->getManager()->flush();
				}
			}
			// Las líneas que linenum=0 significa que ya no existen y que hay que eliminarlas
			$erpBuyOrdersLinesRepository->deleteLinenum($result);
		}

		return new JsonResponse(["result"=>$result]);
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
		$user = $this->getUser();
		$reportsUtils = new ERPBuyOrdersReports();
		$orderRepository=$this->getDoctrine()->getRepository(ERPBuyOrders::class);
		$orderLinesRepository=$this->getDoctrine()->getRepository(ERPBuyOrdersLines::class);
		$order=$orderRepository->find($id);
		// Decimales
		$erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);
		$config	= $erpConfigurationRepository->findOneBy(["company"=>$user->getCompany()]);
		$ndecimals = 2;
		if ($config != null && $config->getDecimals()!=null)
			$ndecimals = $config->getDecimals();
		$lines=$orderLinesRepository->findBy(["buyorder"=>$order],['linenum'=>'ASC']);
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$id, "user"=>$this->getUser(), "order"=>$order, "lines"=>$lines, "decimals"=>$ndecimals];
		$report=$reportsUtils->create($params);
		return new JsonResponse($report);
	 }

}
