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
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsersConfig;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPBuyDeliveryNotesUtils;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPBuyDeliveryNotes;
use App\Modules\ERP\Entity\ERPBuyDeliveryNotesLines;
use App\Modules\ERP\Entity\ERPBuyDeliveryNotesStates;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPVariantsTypes;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\Security\Utils\SecurityUtils;
//use App\Modules\ERP\Reports\ERPBuyDeliveryNotesReports;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use App\Modules\Globale\Helpers\HelperHistory;

class ERPBuyDeliveryNotesController extends Controller
{

		private $module='ERP';
		private $class=ERPBuyDeliveryNotes::class;
		private $utilsClass=ERPBuyDeliveryNotesUtils::class;


	/**
	 * @Route("/{_locale}/ERP/BuyDeliveryNotes", name="BuyDeliveryNotes")
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
		$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/BuyDeliveryNotes.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils->formatForm('BuyDeliveryNotes', true, null, $this->class);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/BuyDeliveryNoteslist.html.twig', [
				'controllerName' => 'BuyDeliveryNotesController',
				'interfaceName' => 'Albaranes de Compra',
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
	 * @Route("/{_locale}/ERP/BuyDeliveryNotes/form/{id}", name="formBuyDeliveryNotes", defaults={"id"=0}))
	 * Muestra la ficha de un albarán de compra
	 */
	public function formBuyDeliveryNotes($id, RouterInterface $router,Request $request)
	{
			// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
				return $this->redirect($this->generateUrl('unauthorized'));

			// Variables -----------------
			// albarán
			$buydeliverynote			= null;
			// Líneas de albarán
			$buydeliverynotelines	= null;
			// Código del albarán, por si se ha pasado como parámetro en la petición en vez del ID
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
			$erpBuyDeliveryNotesRepository					= $this->getDoctrine()->getRepository(ERPBuyDeliveryNotes::class);
			$erpBuyDeliveryNotesLinesRepository			= $this->getDoctrine()->getRepository(ERPBuyDeliveryNotesLines::class);
			$erpBuyDeliveryNotesStatesRepository		= $this->getDoctrine()->getRepository(ERPBuyDeliveryNotesStates::class);
			$erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);
			$erpStoresRepository							= $this->getDoctrine()->getRepository(ERPStores::class);
			$erpStocksRepository							= $this->getDoctrine()->getRepository(ERPStocks::class);
			$supplierCommentLinesRepository		= $this->getDoctrine()->getRepository(ERPSupplierCommentLines::class);
			// Repositorios Globale
			$globaleMenuOptionsRepository			= $this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$globaleUsersRepository						= $this->getDoctrine()->getRepository(GlobaleUsers::class);
			$globaleStatesRepository					= $this->getDoctrine()->getRepository(GlobaleStates::class);
			$globaleCountriesRepository				= $this->getDoctrine()->getRepository(GlobaleCountries::class);
			$globaleUsersConfigRepository			= $this->getDoctrine()->getRepository(GlobaleUsersConfig::class);

			// Si se ha pasado un identificador se busca este y sus líneas
			if ($id!=0){
			 $buydeliverynote				= $erpBuyDeliveryNotesRepository->findOneBy(["company"=>$company, "id"=>$id, "active"=>1,"deleted"=>0]);
			 $buydeliverynotelines	= $erpBuyDeliveryNotesLinesRepository->findOneBy(["buydeliverynote"=>$buydeliverynote]);
			}
			// Busqueda por código de albarán, se redirecciona a su ID correspondiente
			if($buydeliverynote==null && $code!=null){
			 $buydeliverynote				= $erpBuyDeliveryNotesRepository->findOneBy(["company"=>$company, "code"=>$code, "active"=>1,"deleted"=>0]);
			 if ($buydeliverynote)
			 	return $this->redirectToRoute($request->get('_route'), ['id' => $buydeliverynote->getId()]);
			 else
			 	return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}
			// Si id==0, code==null o no se ha encontrado se crea uno nuevo
			if ($buydeliverynote==null){
				$buydeliverynote			 = new $this->class();
			}

			// Configuración (nº decimales, color...etc)
			$config	= $erpConfigurationRepository->findOneBy(["company"=>$company]);

			// Buscador de proveedores
			$supplierslist =
			  [
					'id' => 'listSuppliersBuyDeliveryNotes',
					'route' => 'listSuppliersBuyDeliveryNotes',
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
			$option["id"]=0;
			$option["text"]="Agente...";
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
			// Estados del albarán (combo)
			$oBuyDeliveryNotesstates=$erpBuyDeliveryNotesStatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$states=[];
			$pos = 1;
			foreach($oBuyDeliveryNotesstates as $item){
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$states[]=$option;
				$pos++;
			}

			// Miga
    	$nbreadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    	$breadcrumb=$globaleMenuOptionsRepository->formatBreadcrumb('genericindex','ERP','BuyDeliveryNotes');
    	array_push($breadcrumb,$nbreadcrumb);

			// Líneas -------------------------------
			// Búsqueda de vista de usuario
			$tabs 	 = null;
			$tabsUser= $globaleUsersConfigRepository->findOneBy(["element"=>"BuyDeliveryNotes","view"=>"Defecto","attribute"=>"tabs","active"=>1,"deleted"=>0,"company"=>$company,"user"=>$user]);
			if ($tabsUser!=null){
				$tabs = json_encode($tabsUser->getValue());
			}
			// Proveedor
			$supplier_id = ($buydeliverynote->getSupplier()?$buydeliverynote->getSupplier()->getId():0);
			// Decimales
			$ndecimals = 2;
			if ($config != null && $config->getDecimals()!=null)
				$ndecimals = $config->getDecimals();
			$decimals = str_repeat('0',$ndecimals);
			// Almacén por defecto para las líneas
			$buydeliverynoteslinestore_id = ($buydeliverynote->getStore()?$buydeliverynote->getStore()->getId().'~'.$buydeliverynote->getStore()->getCode():'0~Almacén...');

			// Líneas -----------
			$olines=$erpBuyDeliveryNotesLinesRepository->findBy(["buydeliverynote"=>$buydeliverynote, "active"=>1,"deleted"=>0],["linenum"=>"ASC"]);
			$lines = [];
			if ($olines!=null){
				for($i=0; $i<count($olines); $i++){
					$lines[$i] = [];
					$lines[$i]['id'] 							= (($olines[$i]->getId()!=null)?$olines[$i]->getId():'');
					$lines[$i]['store_id']				= (($olines[$i]->getStore()!=null)?$olines[$i]->getStore()->getId().'~'.$olines[$i]->getStore()->getCode():$buydeliverynoteslinestore_id);
					$lines[$i]['product_id'] 			= (($olines[$i]->getProductvariant()!=null)?$olines[$i]->getProductvariant()->getProduct()->getId().'~'.$olines[$i]->getProductvariant()->getProduct()->getCode():'0~Artículo...');
					$lines[$i]['productname']			= (($olines[$i]->getProductname()!=null)?$olines[$i]->getProductname():'');
					$lines[$i]['variant_id']			= (($olines[$i]->getProductvariant()->getVariant()!=null)?$olines[$i]->getProductvariant()->getVariant()->getId().'~'.$olines[$i]->getVarianttype().' - '.$olines[$i]->getVariantname():'0~Sin variante');
					$lines[$i]['quantity']				= (($olines[$i]->getQuantity()!=null)?$olines[$i]->getQuantity():1);
					$lines[$i]['amount']					= (($olines[$i]->getAmount()!=null)?number_format($olines[$i]->getAmount(),$ndecimals):'0.'+$decimals);
					$lines[$i]['discountperc']		= (($olines[$i]->getDiscountperc()!=null)?number_format($olines[$i]->getDiscountperc(),$ndecimals):'0.'.$decimals);
					$lines[$i]['discountunit']		= (($olines[$i]->getDiscountunit()!=null)?number_format($olines[$i]->getDiscountunit(),$ndecimals):'0.'.$decimals);
					$lines[$i]['base']						= (($olines[$i]->getBase()!=null)?number_format($olines[$i]->getBase(),$ndecimals):'0.'+$decimals);
					$lines[$i]['taxperc']					= (($olines[$i]->getTaxperc()!=null)?number_format($olines[$i]->getTaxperc(),$ndecimals):'0.'.$decimals);
					$lines[$i]['taxunit']					= (($olines[$i]->getTaxunit()!=null)?number_format($olines[$i]->getTaxunit(),$ndecimals):'0.'.$decimals);
					$lines[$i]['total']						= (($olines[$i]->getTotal()!=null)?number_format($olines[$i]->getTotal(),$ndecimals):'0.'.$decimals);


					$lines[$i]['datefinish']			= (($olines[$i]->getDatefinish()!=null)?$olines[$i]->getDatefinish():'');
					$lines[$i]['subtotal']				= (($olines[$i]->getSubtotal()!=null)?number_format($olines[$i]->getSubtotal(),$ndecimals):'0.'.$decimals);

					$lines[$i]['packing']					= (($olines[$i]->getPacking()!=null)?$olines[$i]->getPacking():1);
					$lines[$i]['multiplicity']		= (($olines[$i]->getMultiplicity()!=null)?$olines[$i]->getMultiplicity():1);
					$lines[$i]['minimumquantityofbuy'] = (($olines[$i]->getMinimumquantityofbuy()!=null)?$olines[$i]->getMinimumquantityofbuy():1);
					$lines[$i]['purchaseunit']		= (($olines[$i]->getPurchaseunit()!=null)?$olines[$i]->getPurchaseunit():1);
					$lines[$i]['dateestimated']		= (($olines[$i]->getDateestimated()!=null)?$olines[$i]->getDateestimated()->format('Y-m-d'):$dateestimated->format('Y-m-d'));
					$lines[$i]['weight']					= (($olines[$i]->getWeight()!=null)?number_format($olines[$i]->getWeight(),$ndecimals):'0.'.$decimals);
					$lines[$i]['purchasemeasure']	= (($olines[$i]->getPurchasemeasure()!=null)?$olines[$i]->getPurchasemeasure():'');
					$quantity             = intval($lines[$i]['quantity']);
          $minimumquantityofbuy = intval($lines[$i]['minimumquantityofbuy']);
          $multiplicity         = intval($lines[$i]['multiplicity']);
          $packing              = intval($lines[$i]['packing']);
          $purchaseunit         = intval($lines[$i]['purchaseunit']);
          $quantitycomment      = '';
          // Se muestra el tooltip con los mínimos de compra
          if ($minimumquantityofbuy>1){
            $quantitycomment .= "Mínimo de compra: $minimumquantityofbuy\n";
            $lines[$i]['quantity__min'] = $minimumquantityofbuy;
          }
          if ($multiplicity>1){
            $quantitycomment .= "Multiplicidad: $multiplicity\n";
            $lines[$i]['quantity__multiplicity'] = $multiplicity;
          }
          if ($packing>1){
            $quantitycomment .= "Packing: $packing\n";
          }
          if ($purchaseunit>1){
            $quantitycomment .= "Unidad de compra: $purchaseunit\n";
          }
				}
			}
			// Jexcel de líneas de producto
			$spreadsheet = [];
			$spreadsheet['name']       = "BuyDeliveryNotes";
			$spreadsheet['options']    = "pagination:1000000, search: true, loadmasive: true";
		  $spreadsheet['prototipe']  = "{
				id:'',
				product_id:'0~Artículo...',
				productname:'',
				variant_id:'0~Sin variante',
				quantity:1,
				amount:'0.$decimals',
				discountperc:'0',
				discountunit:'0.$decimals',
				discount:'0.$decimals',
				base:'0.$decimals',
				taxperc:'0',
				taxunit:'0.$decimals',
				total:'0.$decimals',
				store_id:'$buydeliverynoteslinestore_id',
				datefinish:''
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
						{name:'quantity'},
						{name:'amount'},
						{name:'discountunit'},
						{name:'base'},
						{name:'taxunit'},
						{name:'total'}
					]
				}
			  ]
			 ";
		 }
		  $spreadsheet['columns']    =
		   "[
				{ name: 'id', type: 'numeric', width:'50px', title: 'ID', align: 'right'},
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
							oncomplete: 'buydeliverynotesproduct'
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
							oncomplete: 'buydeliverynotesline'
						}
					}
				},
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
		    { name: 'quantity', type: 'numeric', width:'100px', title: 'Cantidad', align: 'right',
					options: {
						onchange: {
							url: '/api/getWSProductVariantPrice/#d|supplier-form-id/#c|product_id/#c|variant_id/#c|quantity',
							oncomplete: 'buydeliverynotesline'
						}
					}
			  },
				{ name: 'amount', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Importe (€)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buydeliverynotesline'
						}
					}},
					{ name: 'discountperc', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto (%)', align: 'right',
						options: {
							onchange: {
								oncomplete: 'buydeliverynotesline'
							}
						}},
					{ name: 'discountunit', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Dto (€)', readOnly:true, align: 'right' },
		    { name: 'base', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Base (€)', align: 'right',
					options: {
						onchange: {
							oncomplete: 'buydeliverynotesline'
						}
					}},
				{ name: 'subtotal', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Subtotal (€)', readOnly:true , align: 'right' },
				{ name: 'taxperc', type: 'text', width:'100px', title: 'IVA (%)', readOnly:true, align: 'right' },
				{ name: 'taxunit', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'IVA (€)', readOnly:true, align: 'right'  },
		    { name: 'total', type: 'numeric', decimal: '".$ndecimals."', width:'100px', title: 'Total (€)', readOnly:true , align: 'right'}
		   ]";
			// Cargar de base de datos
			$spreadsheet['data']       = json_encode($lines);
			$spreadsheet['onload'] 	   =
				"$('#supplier-form-id').val('".$supplier_id."');
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
										if (sheet.options.columns[posvariant_id]['sources'][result.line].length>1 && sheet.getValueFromKey('variant_id', result.line)=='0~Sin variante')
										 sheet.setValueFromKey('variant_id', result.line, '0~Variante...', true);
										for (var i=1; i<document.getElementById('".$spreadsheet['name']."').jexcel.length; i++){
											var sheetothers = document.getElementById('".$spreadsheet['name']."').jexcel[i];
											if (!sheetothers.options.columns[sheetothers.getColumnKey('variant_id')].sources)
												sheetothers.options.columns[sheetothers.getColumnKey('variant_id')].sources = [];
											sheetothers.options.columns[sheetothers.getColumnKey('variant_id')]['sources'][result.line] =	oresult;
											if (sheetothers.options.columns[sheetothers.getColumnKey('variant_id')]['sources'][result.line].length>1 && sheetothers.getValueFromKey('variant_id', result.line)=='0~Sin variante')
											 sheetothers.setValueFromKey('variant_id', result.line, '0~Variante...', true);
										}
				 					}
				 				});
				 			}
				 		}
				 	}
				}";
			// Jexcel de carga masiva de líneas de producto
			$spreadsheetcm = [];
			$spreadsheetcm['name']       = "BuyDeliveryNotescm";
			$spreadsheetcm['options']    = "pagination:1000000, search: true, allowManualInsertRow: false, allowDeletingAllRows: true";
		  $spreadsheetcm['prototipe']  = "{
				id:'',
				variantid:'',
				category:'',
				productcode:'',
				productname:'',
				variantname:'0~Sin variante',
				quantity:0
			}";
			$spreadsheetcm['columns']    =
		   "[
				{ name: 'id', type: 'numeric', width:'50px', title: 'ID', align: 'right'},
				{ name: 'variantid', type: 'numeric', width:'50px', title: 'ID', align: 'right'},
				{ name: 'category', type: 'text', width:'200px', title: 'Categoría', align: 'left', readOnly:true},
		    { name: 'productcode', type: 'text', width:'80px', title: 'Código', align: 'left', readOnly:true},
		    { name: 'productname', type: 'text', width:'300px', title: 'Descripción', align: 'left'},
				{ name: 'variantname', type: 'text', width:'100px',	title:'Variante', align:'left'},
		    { name: 'quantity', type: 'numeric', width:'50px', title: 'Cantidad', align: 'right'}
		   ]";
			$spreadsheetcm['data']       = '[]';
			$spreadsheetcm['onload'] 	   =
				"var sheet = document.getElementById('".$spreadsheetcm['name']."').jexcel;
				 sheet.hideColumnKey('id');
				 sheet.hideColumnKey('variantid');
				";

			// Files
			$utilsCloud = new CloudFilesUtils();
			$path="ERPBuyDeliveryNotes";
			$templateLists=["id"=>$path,"list"=>[$utilsCloud->formatList($this->getUser(),$path,$id)],"types"=>["Otros"], "path"=>$this->generateUrl("cloudUpload",["id"=>$id, "path"=>$path])];

      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    			return $this->render('@ERP/BuyDeliveryNotes.html.twig', [
    				'moduleConfig' => $config,
    				'controllerName' => 'BuyDeliveryNotesController',
    				'interfaceName' => 'Albaranes',
    				'optionSelected' => 'genericindex',
    				'optionSelectedParams' => ["module"=>"ERP", "name"=>"BuyDeliveryNotes"],
    				'menuOptions' =>  $globaleMenuOptionsRepository->formatOptions($userdata),
    				'breadcrumb' =>  $breadcrumb,
    				'userData' => $userdata,
    				'supplierslist' => $supplierslist,
						'form' => 'buydeliverynote',
						'buydeliverynote' => $buydeliverynote,
						'buydeliverynotelines' => $buydeliverynotelines,
						'stores' => $stores,
						'agents' => $agents,
						'states' => $states,
						'paymentmethods' => $paymentmethods,
						'paymentterms' => $paymentterms,
						'carriers' => $carriers,
						'shippings' => $shippings,
						'orderchannel' => $orderchannel,
						'destinationstates' => $destinationstates,
						'destinationcountries' => $destinationcountries,
						'customerslist' => $customerslist,
						'id' => $id,
						'spreadsheetBuyDeliveryNotescs' => $spreadsheetcs,
						'spreadsheetBuyDeliveryNotescc' => $spreadsheetcc,
						'spreadsheetBuyDeliveryNotes' => $spreadsheet,
						'spreadsheetBuyDeliveryNotescm' => $spreadsheetcm,
						'cloudConstructor' => $templateLists,
						'token' => uniqid('sign_').time(),
						'history' => HelperHistory::createHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $id, $this->getUser(), $this->getDoctrine(), true),
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
	* @Route("/{_locale}/ERP/BuyDeliveryNotes/save", name="saveBuyDeliveryNotes")
	*/
	public function saveBuyDeliveryNotes(Request $request){
		/*$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$result = false;

		// Parámetros --------------------------------------
		// Objeto con todos los datos del formulario
		$ojsBuyDeliveryNotes=json_decode($request->getContent(), true);

		if ($ojsBuyDeliveryNotes!=null){
			// Repositorios ------------------------------------
			$erpBuyDeliveryNotesRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotes::class);
			$erpBuyDeliveryNotesLinesRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesLines::class);
			$erpBuyDeliveryNotesStatesRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesStates::class);
			$erpBuyDeliveryNotesContactsRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesContacts::class);
			$erpBuyOffertsRepository=$this->getDoctrine()->getRepository(ERPBuyOffert::class);
			$erpSuppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
			$erpCustomersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
			$erpPaymentMethodsRepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
			$erpPaymentTermsRepository=$this->getDoctrine()->getRepository(ERPPaymentTerms::class);
			$erpCarriersRepository=$this->getDoctrine()->getRepository(ERPCarriers::class);
			$erpStoresRepository=$this->getDoctrine()->getRepository(ERPStores::class);
			$erpAddressesRepository=$this->getDoctrine()->getRepository(ERPAddresses::class);
			$erpProductsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$erpProducsVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$erpVariantsRepository=$this->getDoctrine()->getRepository(ERPVariants::class);
			$erpReferencesRepository=$this->getDoctrine()->getRepository(ERPReferences::class);
			$erpContactsRepository=$this->getDoctrine()->getRepository(ERPContacts::class);
			// Globales
			$globaleCompaniesRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
			$globaleUsersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$globaleStatesRepository=$this->getDoctrine()->getRepository(GlobaleStates::class);
			$globaleCountriesRepository=$this->getDoctrine()->getRepository(GlobaleCountries::class);

			$buydeliverynote	= null;
			$id 			= $ojsBuyDeliveryNotes['id'];
			if ($id!='' && $id!='0'){
				$buydeliverynote	= $erpBuyDeliveryNotesRepository->find($id);
			}
			if ($buydeliverynote==null){
				$buydeliverynote = new ERPBuyDeliveryNotes();
				$company 	= $globaleCompaniesRepository->find($ojsBuyDeliveryNotes['company_id']);
				$author 	= $globaleUsersRepository->find($ojsBuyDeliveryNotes['author_id']);
				$code 		= $erpBuyDeliveryNotesRepository->getNextCode();
				$buydeliverynote->setCompany($company);
				$buydeliverynote->setCode($code);
				// TODO
				$buydeliverynote->setRevision(1);
				$buydeliverynote->setActive(1);
				$buydeliverynote->setDeleted(0);
				$buydeliverynote->setAuthor($author);
				$buydeliverynote->setDateadd(new \DateTime());
			}
			// Generales
			$agent 			= $globaleUsersRepository->find($ojsBuyDeliveryNotes['agent_id']);
			$state_old 	= $buydeliverynote->getState();
			$state 			= $erpBuyDeliveryNotesStatesRepository->find($ojsBuyDeliveryNotes['state_id']);
			$buydeliverynote->setAgent($agent);
			$buydeliverynote->setState($state);
			$buydeliverynote->setDateupd(new \DateTime());
			$buydeliverynote->setTheircode($ojsBuyDeliveryNotes['theircode']);
			$buydeliverynote->setPriority($ojsBuyDeliveryNotes['priority']);
			$buydeliverynote->setObservationpriority($ojsBuyDeliveryNotes['observationpriority']);
			$buydeliverynote->setWeight($ojsBuyDeliveryNotes['weight']);
			$orderchannel = null;
			if ($ojsBuyDeliveryNotes['orderchannel']!=null && $ojsBuyDeliveryNotes['orderchannel']!='')
				$orderchannel = $ojsBuyDeliveryNotes['orderchannel'];
			$buydeliverynote->setOrderchannel($orderchannel);
			if ($ojsBuyDeliveryNotes['amount']!='')
				$buydeliverynote->setAmount(floatval($ojsBuyDeliveryNotes['amount']));
			else
				$buydeliverynote->setAmount(null);
			if ($ojsBuyDeliveryNotes['discount']!='')
				$buydeliverynote->setDiscount(floatval($ojsBuyDeliveryNotes['discount']));
			else
				$buydeliverynote->setDiscount(null);
			if ($ojsBuyDeliveryNotes['base']!='')
				$buydeliverynote->setBase(floatval($ojsBuyDeliveryNotes['base']));
			else
				$buydeliverynote->setBase(null);
			if ($ojsBuyDeliveryNotes['taxes']!='')
				$buydeliverynote->setTaxes(floatval($ojsBuyDeliveryNotes['taxes']));
			else
				$buydeliverynote->setTaxes(null);
			if ($ojsBuyDeliveryNotes['shipping']!='')
				$buydeliverynote->setShipping(floatval($ojsBuyDeliveryNotes['shipping']));
			else
				$buydeliverynote->setShipping(null);
			if ($ojsBuyDeliveryNotes['total']!='')
				$buydeliverynote->setTotal(floatval($ojsBuyDeliveryNotes['total']));
			else
				$buydeliverynote->setTotal(null);
			$buydeliverynote->setObservationpublic($ojsBuyDeliveryNotes['observationpublic']);
	    // Fechas
			if ($ojsBuyDeliveryNotes['dateread']!='')
				$buydeliverynote->setDateread(new \DateTime($ojsBuyDeliveryNotes['dateread']));
			else
				$buydeliverynote->setDateread(null);
			if ($ojsBuyDeliveryNotes['datesend']!='')
	   		$buydeliverynote->setDatesend(new \DateTime($ojsBuyDeliveryNotes['datesend']));
			else
				$buydeliverynote->setDatesend(null);
			if ($ojsBuyDeliveryNotes['dateconfirmed']!='')
				$buydeliverynote->setDateconfirmed(new \DateTime($ojsBuyDeliveryNotes['dateconfirmed']));
			else
				$buydeliverynote->setDateconfirmed(null);
			if ($ojsBuyDeliveryNotes['estimateddelivery']!='')
				$buydeliverynote->setEstimateddelivery(new \DateTime($ojsBuyDeliveryNotes['estimateddelivery']));
			else
				$buydeliverynote->setEstimateddelivery(null);

			// Proveedor
			$supplier 			= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['supplier_id'])))
				$supplier 			= $erpSuppliersRepository->find($ojsBuyDeliveryNotes['supplier_id']);
			$paymentmethod 	= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['paymentmethod_id'])))
				$paymentmethod 	= $erpPaymentMethodsRepository->find($ojsBuyDeliveryNotes['paymentmethod_id']);
			$paymentterms 	= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['paymentterms_id'])))
				$paymentterms 	= $erpPaymentTermsRepository->find($ojsBuyDeliveryNotes['paymentterms_id']);
			$carrier 	= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['carrier_id'])))
				$carrier 	= $erpCarriersRepository->find($ojsBuyDeliveryNotes['carrier_id']);
			$buydeliverynote->setSupplier($supplier);
			$buydeliverynote->setPaymentmethod($paymentmethod);
			$buydeliverynote->setPaymentterms($paymentterms);
			$buydeliverynote->setCarrier($carrier);
			$buydeliverynote->setSuppliername($ojsBuyDeliveryNotes['suppliername']);
			$buydeliverynote->setSuppliercode($ojsBuyDeliveryNotes['suppliercode']);
			$buydeliverynote->setEmail($ojsBuyDeliveryNotes['email']);
			$buydeliverynote->setPhone($ojsBuyDeliveryNotes['phone']);
			$buydeliverynote->setMinorder($ojsBuyDeliveryNotes['minorder']);
			$buydeliverynote->setFreeshipping($ojsBuyDeliveryNotes['freeshipping']);
			$shippingcharge = null;
			if ($ojsBuyDeliveryNotes['shippingcharge']!=null && $ojsBuyDeliveryNotes['shippingcharge']!='')
				$shippingcharge = $ojsBuyDeliveryNotes['shippingcharge'];
			$buydeliverynote->setShippingcharge($shippingcharge);
			$buydeliverynote->setSupplieraddress($ojsBuyDeliveryNotes['supplieraddress']);
			$buydeliverynote->setSupplierpostcode($ojsBuyDeliveryNotes['supplierpostcode']);
			$buydeliverynote->setSupplierstate($ojsBuyDeliveryNotes['supplierstate']);
			$buydeliverynote->setSuppliercountry($ojsBuyDeliveryNotes['suppliercountry']);
			$buydeliverynote->setSuppliercity($ojsBuyDeliveryNotes['suppliercity']);
			$buydeliverynote->setSuppliervat($ojsBuyDeliveryNotes['suppliervat']);

			$buydeliverynote->setSuppliercomment($ojsBuyDeliveryNotes['suppliercomment']);
			$buydeliverynote->setSupplierbuydeliverynote($ojsBuyDeliveryNotes['supplierbuydeliverynote']);
			$buydeliverynote->setSuppliershipping($ojsBuyDeliveryNotes['suppliershipping']);
			$buydeliverynote->setSupplierpayment($ojsBuyDeliveryNotes['supplierpayment']);
			$buydeliverynote->setSupplierspecial($ojsBuyDeliveryNotes['supplierspecial']);

			// Destino y cliente
			$store				= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['store_id'])))
				$store 				= $erpStoresRepository->find($ojsBuyDeliveryNotes['store_id']);
			$customer 		= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['customer_id'])))
				$customer 		= $erpCustomersRepository->find($ojsBuyDeliveryNotes['customer_id']);
			$destination 	= null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['destination_id'])))
				$destination 	= $erpAddressesRepository->find($ojsBuyDeliveryNotes['destination_id']);
			$destinationstate 	=	null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['destinationstate_id'])))
				$destinationstate 	= $globaleStatesRepository->find($ojsBuyDeliveryNotes['destinationstate_id']);
			$destinationcountry = null;
			if (ctype_digit(strval($ojsBuyDeliveryNotes['destinationcountry_id'])))
				$destinationcountry = $globaleCountriesRepository->find($ojsBuyDeliveryNotes['destinationcountry_id']);
			$buydeliverynote->setStore($store);
			$buydeliverynote->setCustomer($customer);
			$buydeliverynote->setDestination($destination);
			$buydeliverynote->setDestinationstate($destinationstate);
			$buydeliverynote->setDestinationcountry($destinationcountry);
			$buydeliverynote->setDestinationname($ojsBuyDeliveryNotes['destinationname']);
			$buydeliverynote->setDestinationaddress($ojsBuyDeliveryNotes['destinationaddress']);
			$buydeliverynote->setDestinationphone($ojsBuyDeliveryNotes['destinationphone']);
			$buydeliverynote->setDestinationemail($ojsBuyDeliveryNotes['destinationemail']);
			$buydeliverynote->setDestinationpostcode($ojsBuyDeliveryNotes['destinationpostcode']);
			$buydeliverynote->setDestinationcity($ojsBuyDeliveryNotes['destinationcity']);
	    // TODO Offert
			$offert = $erpBuyOffertsRepository->find($ojsBuyDeliveryNotes['offert_id']);
			$buydeliverynote->setOffert($offert);

			$this->getDoctrine()->getManager()->persist($buydeliverynote);
			$this->getDoctrine()->getManager()->flush();
			$result = $buydeliverynote->getId();

			// Histórico de cambios y fechas
			if ($state_old!=$state && $state!=null){
				$globaleHistoriesRepository		= $this->getDoctrine()->getRepository(GlobaleHistories::class);
				$date = date('Y-m-d H:i:s');
				switch ($state->getId()) {
				    case 1:
							// 1.- Abierto
							if ($state_old==null)
								$globaleHistoriesRepository->addHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $result, $buydeliverynote->getCompany(),  $this->getUser(), '[]');
			        break;
				    case 2:
							// 2.- Lanzado
							$globaleHistoriesRepository->addHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $result, $buydeliverynote->getCompany(),  $this->getUser(), '[{"description": "Se ha lanzado el albarán"},{"description":"Se cambio la fecha de envio a '.date('d/m/Y').'", "attribute": "datesend", "oldvalue": "'.($buydeliverynote->getDatesend()==null?'':$buydeliverynote->getDatesend()->format('Y-m-d H:i:s')).'", "newvalue":"'.$date.'"}]');
							$buydeliverynote->setDatesend(new \DateTime());
							$this->getDoctrine()->getManager()->persist($buydeliverynote);
							$this->getDoctrine()->getManager()->flush();
							break;
						case 3:
							// 3.- Confirmado
							$globaleHistoriesRepository->addHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $result, $buydeliverynote->getCompany(),  $this->getUser(), '[{"description": "Se ha confirmado el albarán"},{"description":"Se cambio la fecha de confirmación a '.date('d/m/Y').'", "attribute": "dateconfirmed", "oldvalue": "'.($buydeliverynote->getDateconfirmed()==null?'':$buydeliverynote->getDateconfirmed()->format('Y-m-d H:i:s')).'", "newvalue":"'.$date.'"}]');
							$buydeliverynote->setDateconfirmed(new \DateTime());
							$this->getDoctrine()->getManager()->persist($buydeliverynote);
							$this->getDoctrine()->getManager()->flush();
							break;
						case 4:
							// 4.- Recibido incompleto
							$globaleHistoriesRepository->addHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $result, $buydeliverynote->getCompany(),  $this->getUser(), '[{"description": "Recibido incompleto"}]');
			        break;
						case 5:
							// 5.- Recibido
							$globaleHistoriesRepository->addHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $result, $buydeliverynote->getCompany(),  $this->getUser(), '[{"description": "Recibido"}]');
							break;
						case 6:
							// 4.- Cancelado
							$globaleHistoriesRepository->addHistory('App\Modules\ERP\Entity\ERPBuyDeliveryNotes', $result, $buydeliverynote->getCompany(),  $this->getUser(), '[{"description": "Cancelado"}]');
							break;
				}
			}

			// Contactos del proveedor
			// Poner todas los contactos con teléfono a '~|/%^%/|~' indicando que no es válido
			// Si se actualiza se deja el contacto sino se borra
			// Tipo 0 Contactos proveedor 1 Cliente
			$erpBuyDeliveryNotesContactsRepository->setDeletecontacts($result,0);
			$lines =  $ojsBuyDeliveryNotes['contactssupplier'];
			if ($lines!=null && is_array($lines)){
				for($i=0; $i<count($lines); $i++){
					$buydeliverynotecontact = null;
					$line 					 = $lines[$i];
					$id 						 = $line['id'];
					if ($id!='' && $id!='0' && $id!=0){
						$buydeliverynotecontact	= $erpBuyDeliveryNotesContactsRepository->find($id);
					}
					if ($buydeliverynotecontact==null){
						$buydeliverynotecontact = new ERPBuyDeliveryNotesContacts();
						$buydeliverynotecontact->setActive(1);
						$buydeliverynotecontact->setDeleted(0);
						$buydeliverynotecontact->setDateadd(new \DateTime());
					}
					$buydeliverynotecontact->setbuydeliverynote($buydeliverynote);
					$contact 					= $erpContactsRepository->find($line['contact_id']);
					$buydeliverynotecontact->setContact($contact);
					$buydeliverynotecontact->setDateupd(new \DateTime());
					$buydeliverynotecontact->setType(0);
					$buydeliverynotecontact->setName($line['name']);
					$buydeliverynotecontact->setEmail($line['email']);
					$buydeliverynotecontact->setPhone($line['phone']);
					$this->getDoctrine()->getManager()->persist($buydeliverynotecontact);
					$this->getDoctrine()->getManager()->flush();
				}
			}
			// Elimina los contactos con phone='~|/%^%/|~' del albarán ya que ya no son válidos
	    // Tipo 0 Contactos proveedor 1 Cliente
			$erpBuyDeliveryNotesContactsRepository->deleteContacts($result, 0);

			// Contactos del cliente
			// Poner todas los contactos con teléfono a '~|/%^%/|~' indicando que no es válido
			// Si se actualiza se deja el contacto sino se borra
			// Tipo 0 Contactos proveedor 1 Cliente
			$erpBuyDeliveryNotesContactsRepository->setDeletecontacts($result,1);
			$lines =  $ojsBuyDeliveryNotes['contactscustomer'];
			if ($lines!=null && is_array($lines)){
				for($i=0; $i<count($lines); $i++){
					$buydeliverynotecontact = null;
					$line 					 = $lines[$i];
					$id 						 = $line['id'];
					if ($id!='' && $id!='0' && $id!=0){
						$buydeliverynotecontact	= $erpBuyDeliveryNotesContactsRepository->find($id);
					}
					if ($buydeliverynotecontact==null){
						$buydeliverynotecontact = new ERPBuyDeliveryNotesContacts();
						$buydeliverynotecontact->setActive(1);
						$buydeliverynotecontact->setDeleted(0);
						$buydeliverynotecontact->setDateadd(new \DateTime());
					}
					$buydeliverynotecontact->setbuydeliverynote($buydeliverynote);
					$contact 					= $erpContactsRepository->find($line['contact_id']);
					$buydeliverynotecontact->setContact($contact);
					$buydeliverynotecontact->setDateupd(new \DateTime());
					$buydeliverynotecontact->setType(1);
					$buydeliverynotecontact->setName($line['name']);
					$buydeliverynotecontact->setEmail($line['email']);
					$buydeliverynotecontact->setPhone($line['phone']);
					$this->getDoctrine()->getManager()->persist($buydeliverynotecontact);
					$this->getDoctrine()->getManager()->flush();
				}
			}
			// Elimina los contactos con phone='~|/%^%/|~' del albarán ya que ya no son válidos
			// Tipo 0 Contactos proveedor 1 Cliente
			$erpBuyDeliveryNotesContactsRepository->deleteContacts($result, 1);

			// Líneas
			// Poner todas las líneas de la base de datos a linenum=0
			$erpBuyDeliveryNotesLinesRepository->setLinenum($result);
			$lines =  $ojsBuyDeliveryNotes['lines'];
			if ($lines!=null && is_array($lines)){
				for($i=0; $i<count($lines); $i++){
					$buydeliverynoteline = null;
					$line 				= $lines[$i];
					$id 					= $line['id'];
					if ($id!='' && $id!='0'){
						$buydeliverynoteline	= $erpBuyDeliveryNotesLinesRepository->find($id);
					}
					if ($buydeliverynoteline==null){
						$buydeliverynoteline = new ERPBuyDeliveryNotesLines();
						$buydeliverynoteline->setActive(1);
						$buydeliverynoteline->setDeleted(0);
						$buydeliverynoteline->setDateadd(new \DateTime());
					}
					// Generales
					$product 					= $erpProductsRepository->find($line['product_id']);
					$variant					= $erpVariantsRepository->find($line['variant_id']);
					$productvariant 	= $erpProducsVariantsRepository->findOneBy(["product"=>$product,"variant"=>$variant,"active"=>"1","deleted"=>"0"]);
					$store 						= $erpStoresRepository->find($line['store_id']);
					$reference				= $erpReferencesRepository->findOneBy(["supplier"=>$supplier,"product"=>$product, "productvariant"=>$productvariant,"active"=>"1","deleted"=>"0"]);
					if ($reference==null)
							$reference= $erpReferencesRepository->findOneBy(["supplier"=>$supplier,"product"=>$product,"active"=>"1","deleted"=>"0"]);
					$buydeliverynoteline->setbuydeliverynote($buydeliverynote);
					$buydeliverynoteline->setProduct($product);
					$buydeliverynoteline->setVariant($variant);
					$buydeliverynoteline->setDateupd(new \DateTime());
					$buydeliverynoteline->setLinenum($line['linenum']);
					$buydeliverynoteline->setCode($line['code']);
					$buydeliverynoteline->setProductname($line['productname']);
					$buydeliverynoteline->setQuantity($line['quantity']);
					$buydeliverynoteline->setPvp($line['pvp']);
					$buydeliverynoteline->setDiscount1($line['discount1']);
					$buydeliverynoteline->setDiscount2($line['discount2']);
					$buydeliverynoteline->setDiscount3($line['discount3']);
					$buydeliverynoteline->setDiscount4($line['discount4']);
					$buydeliverynoteline->setDiscountequivalent($line['discountequivalent']);
					$buydeliverynoteline->setTotaldiscount($line['totaldiscount']);
					$buydeliverynoteline->setShoppingprice($line['shoppingprice']);
					$buydeliverynoteline->setStore($store);
					$buydeliverynoteline->setSubtotal($line['subtotal']);
					$buydeliverynoteline->setTaxperc($line['taxperc']);
					$buydeliverynoteline->setTaxunit($line['taxunit']);
					$buydeliverynoteline->setTotal($line['total']);
					$buydeliverynoteline->setPacking($line['packing']);
					$buydeliverynoteline->setMultiplicity($line['multiplicity']);
					$buydeliverynoteline->setMinimumquantityofbuy($line['minimumquantityofbuy']);
					$buydeliverynoteline->setPurchaseunit($line['purchaseunit']);
					if ($line['dateestimated']!='')
						$buydeliverynoteline->setDateestimated(new \DateTime($line['dateestimated']));
					else
						$buydeliverynoteline->setDateestimated(null);

					$buydeliverynoteline->setWeight($line['weight']);
					$buydeliverynoteline->setPurchasemeasure($line['purchasemeasure']);
					if ($reference!=null)
						$buydeliverynoteline->setSupplierreference($reference->getName());
					else
						$buydeliverynoteline->setSupplierreference('');
					if ($store!=null){
						$buydeliverynoteline->setStorecode($store->getCode());
						$buydeliverynoteline->setStorename($store->getName());
					}else{
						$buydeliverynoteline->setStorecode('');
						$buydeliverynoteline->setStorename('');
					}
					if ($variant!=null && $variant->getVarianttype()!=null){
						$buydeliverynoteline->setVariantname($variant->getVarianttype()->getName());
					}else{
						$buydeliverynoteline->setVariantname('');
					}
					if ($variant!=null){
						$buydeliverynoteline->setVariantvalue($variant->getName());
					}else{
						$buydeliverynoteline->setVariantvalue('');
					}

					$this->getDoctrine()->getManager()->persist($buydeliverynoteline);
					$this->getDoctrine()->getManager()->flush();
				}
			}
			// Las líneas que linenum=0 significa que ya no existen y que hay que eliminarlas
			$erpBuyDeliveryNotesLinesRepository->deleteLinenum($result);
		}

		return new JsonResponse(["result"=>$result]);*/
	}

	/**
	 * @Route("/api/BuyDeliveryNotes/list", name="BuyDeliveryNoteslist")
	 */
	/*public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/BuyDeliveryNotes.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, CustomerGroups::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
		return new JsonResponse($return);
	}*/

	/**
	 * @Route("/api/BuyDeliveryNotes/print/{id}", name="printbuydeliverynote", defaults={"id"=0})
	 */
	 public function printbuydeliverynote($id, Request $request){
		/*$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$reportsUtils = new ERPBuyDeliveryNotesReports();
		$orderRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotes::class);
		$orderLinesRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesLines::class);
		$orderContactsRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesContacts::class);
		$order=$orderRepository->find($id);
		// Decimales
		$erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);
		$config	= $erpConfigurationRepository->findOneBy(["company"=>$user->getCompany()]);
		$ndecimals = 2;
		if ($config != null && $config->getDecimals()!=null)
			$ndecimals = $config->getDecimals();
		// Contactos
		$contactssupplier=$orderContactsRepository->findBy(["buydeliverynote"=>$order, 'type'=>0],['name'=>'ASC']);
		$contactscustomer=$orderContactsRepository->findBy(["buydeliverynote"=>$order, 'type'=>1],['name'=>'ASC']);
		// Líneas
		$lines=$orderLinesRepository->findBy(["buydeliverynote"=>$order],['linenum'=>'ASC']);
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$id, "user"=>$this->getUser(), "order"=>$order, "lines"=>$lines, "decimals"=>$ndecimals, "contactssupplier"=>$contactssupplier, "contactscustomer"=>$contactscustomer];
		$report=$reportsUtils->create($params);
		return new JsonResponse($report);*/
	 }

	 /**
    * @Route("/api/BuyDeliveryNotes/pdf/{id}", name="pdfbuydeliverynote", defaults={"id"=0})
    */
    public function pdfbuydeliverynote($id, Request $request){
			/*$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$reportsUtils = new ERPBuyDeliveryNotesReports();
			$orderRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotes::class);
			$orderLinesRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesLines::class);
			$orderContactsRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesContacts::class);
			$order=$orderRepository->find($id);
			// Decimales
			$erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);
			$config	= $erpConfigurationRepository->findOneBy(["company"=>$user->getCompany()]);
			$ndecimals = 2;
			if ($config != null && $config->getDecimals()!=null)
				$ndecimals = $config->getDecimals();
			// Contactos
			$contactssupplier=$orderContactsRepository->findBy(["buydeliverynote"=>$order, 'type'=>0],['name'=>'ASC']);
			$contactscustomer=$orderContactsRepository->findBy(["buydeliverynote"=>$order, 'type'=>1],['name'=>'ASC']);
			// Líneas
			$lines=$orderLinesRepository->findBy(["buydeliverynote"=>$order],['linenum'=>'ASC']);
			$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$id, "user"=>$this->getUser(), "order"=>$order, "lines"=>$lines, "decimals"=>$ndecimals, "contactssupplier"=>$contactssupplier, "contactscustomer"=>$contactscustomer];
      $tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Email'.DIRECTORY_SEPARATOR;
      if (!file_exists($tempPath) && !is_dir($tempPath)) {
          mkdir($tempPath, 0775, true);
      }
      $pdf=$reportsUtils->create($params,'F',$tempPath.$order->getCode().'.pdf');
      return new JsonResponse(["result"=>$tempPath]);*/
    }

		/**
     * @Route("/api/BuyDeliveryNotes/email/{id}", name="emailbuydeliverynote", defaults={"id"=0})
     */
     public function emailbuydeliverynote($id, Request $request){
 			/*$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 			$user = $this->getUser();
 			$orderRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotes::class);
 			$orderContactsRepository=$this->getDoctrine()->getRepository(ERPBuyDeliveryNotesContacts::class);
 			$order=$orderRepository->find($id);
 			// Contactos
 			$contactssupplier=$orderContactsRepository->findBy(["buydeliverynote"=>$order, 'type'=>0],['name'=>'ASC']);
			$mailaddress = [];
			if($order!=null &&  $order->getEmail()!=null &&  $order->getEmail()!=""){
	 		 $mailadress=$order->getSuppliername()." <".$order->getEmail().">";
	 		 $mailaddress[]=$mailadress;
		 	}
		 	foreach($contactssupplier as $contact){
	 		 if($contact->getEmail()!=""){
	 			 $mailadress=$contact->getName()." <".$contact->getEmail().">";
	 			 $mailaddress[]=$mailadress;
	 		 }
	 	  }
		  return new JsonResponse(["addresses"=>$mailaddress]);*/
		}
}
