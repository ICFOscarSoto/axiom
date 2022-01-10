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
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProductsAttributes;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPInfoStocks;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStoresManagersConsumers;
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\ERP\Entity\ERPStoresManagersUsersStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPVariantsValues;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPWorkList;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersConsumersUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\ERP\Utils\ERPStoresManagersOperationsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersOperationsLinesUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Utils\ERPStoresManagersUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Modules\Globale\Helpers\XLSXWriter\XLSXWriter;
//include "includes/XLSXWriter/XLSXWriter.php";

class ERPStoresManagersOperationsController extends Controller
{
	private $class=ERPStoresManagersOperations::class;
	private $classLines=ERPStoresManagersOperationsLines::class;
	private $utilsClass=ERPStoresManagersOperationsUtils::class;
	private $module='ERP';
	private $prefix='OP';

	/**
	 * @Route("/{_locale}/ERP/storesmanagers/operations/form/{id}", name="operationsForm", defaults={"id"=0}))
	 */
	public function operationsForm($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$customerGroupsrepository=$this->getDoctrine()->getRepository(ERPCustomerGroups::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(ERPSeries::class);
		$documentRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperations::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperationsLines::class);

		if($request->query->get('code',null)){
			$obj = $documentRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		//Search Customers
		$classCustomersUtils="\App\Modules\ERP\Utils\ERPCustomersUtils";
		$customersutils = new $classCustomersUtils();
		$customerslist=$customersutils->formatList($this->getUser());
		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$customerslist["topButtons"]=[];

		//Search Products
		$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

		//Customer groups combo
		$objects=$customerGroupsrepository->findBy(["company"=>$this->getUser()->getCompany(),"active"=>1,"deleted"=>0]);
		$customerGroups=[];
		$option["id"]=0;
		$option["text"]="Grupo Cliente";
		$customerGroups[]=$option;
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$customerGroups[]=$option;
		}

		//Customer payment methods combo
		$objects=$paymentMethodsrepository->findBy(["company"=>$this->getUser()->getCompany(),"active"=>1,"deleted"=>0]);
		$paymentMethods=[];
		$option["id"]=null;
		$option["text"]="Método pago";
		$paymentMethods[]=$option;
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$paymentMethods[]=$option;
		}

		//Series combo
		$objects=$seriesRepository->findBy(["company"=>$this->getUser()->getCompany(),"active"=>1,"deleted"=>0]);
		$series=[];
		$option["id"]=null;
		$option["text"]="Serie";
		$series[]=$option;
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$series[]=$option;
		}
		//Recover document from persistence
		$document=null;
		$line=new $this->classLines();
	//	$line->setTaxperc($config->getDefaulttax()->getTax());

		if($id!=0){
			$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			$documentLines=$documentLinesRepository->findBy(["operation"=>$document, "active"=>1,"deleted"=>0]);
			//$line->setLinenum(count($documentLines)+1);
			array_push($documentLines, $line);
		}
		if($document==null){
			$document=new $this->class();
			$documentLines=[$line];
		}

		$errors=[];
		//Check if the financialyear is open
		if($id==0 && ($config->getFinancialyear()==null || $config->getFinancialyear()->getStatus()==0))
			array_push($errors, "Debe existir un ejercicio fiscal abierto. Puede crear o abrir uno en el menu <a target='_blank' href='".$this->generateUrl("genericindex",["module"=>"ERP", "name"=>"FinancialYears"])."'>\"Ejercicios Fiscales\"</a>, también tiene que estar establecido como el ejercicio en uso en la <a target='_blank' href='".$this->generateUrl("mycompany")."?tab=ERP'>\"configuración del módulo\"</a>.");

		$warnings=[];
		//Check if the budget is expired
		/*if($id!=0 && $document->getDateofferend()<new \Datetime())
			array_push($warnings, "El periodo de validez del presupuesto ha expirado. Considere generar uno nuevo.");
		if($document->getSalesbudget()!=null)
			array_push($warnings, "Este presupuesto ya esta asociado al pedido número <a href='".$this->generateUrl("ERPSalesOrdersForm",["id"=>$document->getInSalesOrder()->getId()])."'>".$document->getInSalesOrder()->getCode()."</a>, puede editar este presupuesto haciendo click <a href=\"javascript:unlockFields();\">aquí</a> aunque se aconseja generar un nuevo presupuesto o editar directamente el pedido.");*/
			$document->code=$id;
			$document->customercode=$document->getManager()->getCustomer()->getCode();
			$document->serie=null;
			$document->paymentmethod=null;
			foreach($documentLines as $key=>$line){
				$line->linenum=$key;
			}


		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','StoresManagersOperations');
		array_push($breadcrumb,$new_breadcrumb);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/storesmanagersoperations.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'categoriesController',
				'interfaceName' => 'Operaciones Gestor',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagersOperations"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'customerslist' => $customerslist,
				'productslist' => $productslist,
				'customerGroups' => $customerGroups,
				'paymentMethods' => $paymentMethods,
				'series' => $series,
				'date' => ($document->getId()==null)?date('d-m-Y'):$document->getDate()->format('d/m/Y'),
				'id' => $id,
				'documentType' => 'manager_operation',
				'documentPrefix' => $this->prefix,
				'document' => $document,
				'documentLines' => $documentLines,
				'documentReadonly' => true,

				'errors' => $errors,
				'warnings' => $warnings,
				'token' => uniqid('sign_').time()
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}

    /**
		 * @Route("/api/erp/storesmanagers/operations/create/{id}", name="createOperations", defaults={"id"=0})
		 */
		 public function createOperations($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
			$managerRepository=$this->getDoctrine()->getRepository(ERPStoresManagers::class);
			$consumerRepository=$this->getDoctrine()->getRepository(ERPStoresManagersConsumers::class);
			$storeRepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			$stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
			$infostocksRepository=$this->getDoctrine()->getRepository(ERPInfoStocks::class);
			$productVariantRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$store=$storeRepository->findOneBy(["user"=>$this->getUser(),"preferential"=>1,"active"=>1,"deleted"=>0]);
			if(!$store) return new JsonResponse(["result"=>-4, "text"=> "El usuario no tiene almacén preferente"]);
			$consumer=$consumerRepository->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
			if(!$consumer) return new JsonResponse(["result"=>-2, "text"=> "El usuario no existe"]);
			if($consumer->getManager()->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(["result"=>-3, "text"=> "Operación no autorizada"]);
			$location=$storeLocationsRepository->findOneBy(["store"=>$store->getStore(), "company"=>$this->getUser()->getCompany(), "active"=>1,"deleted"=>0]);
			if(!$location) return new JsonResponse(["result"=>-4, "text"=> "No existen ubicación en el almacén gestor"]);
			$typesRepository=$this->doctrine->getRepository(ERPTypesMovements::class);
			$type=$typesRepository->findOneBy(["name"=>"Salida gestor"]);
			$worklistProducts=$worklistRepository->findBy(["user"=>$this->getUser(),"deleted"=>0]);
			if(count($worklistProducts)){
					$operation=new ERPStoresManagersOperations();
					$operation->setCompany($this->getUser()->getCompany());
					$operation->setManager($consumer->getManager());
					$operation->setAgent($this->getUser());
					$operation->setConsumer($consumer);
					$operation->setStore($store->getStore());
					$operation->setDate(new \Datetime());
					$operation->setDateadd(new \Datetime());
					$operation->setDateupd(new \Datetime());
					$operation->setActive(true);
					$operation->setDeleted(false);
					$this->getDoctrine()->getManager()->persist($operation);
					$this->getDoctrine()->getManager()->flush();

					foreach($worklistProducts as $item){
						$line=new ERPStoresManagersOperationsLines();
						$line->setOperation($operation);
						$line->setProduct($item->getProduct());
						$line->setQuantity($item->getQuantity());
						$line->setCode($item->getCode());
						$line->setName($item->getName());
						$line->setVariant($item->getVariant());
						$line->setLocation($item->getLocation());
						$line->setDateadd(new \Datetime());
						$line->setDateupd(new \Datetime());
						$line->setActive(true);
						$line->setDeleted(false);
						$this->getDoctrine()->getManager()->persist($line);
						$this->getDoctrine()->getManager()->flush();
						//Discount quantities
						$stock=$stocksRepository->findOneBy(["product"=>$item->getProduct(), "productvariant"=>$item->getVariant(), "company"=>$this->getUser()->getCompany(), "storelocation"=>$location, "active"=>1, "deleted"=>0]);

						$stockHistory=new ERPStockHistory();
						$stockHistory->setProduct($item->getProduct());
						$stockHistory->setLocation($location);
						$stockHistory->setStore($store->getStore());
						$stockHistory->setUser($this->getUser());
						$stockHistory->setPreviousqty($stock->getQuantity());
						$stockHistory->setNewqty($stock->getQuantity()-$item->getQuantity());
						$stockHistory->setNumOperation($operation->getId());
						$stockHistory->setType($type);
						$stockHistory->setDateadd(new \Datetime());
						$stockHistory->setDateupd(new \Datetime());
						$stockHistory->setActive(true);
						$stockHistory->setDeleted(false);
						$this->getDoctrine()->getManager()->persist($stockHistory);
						$this->getDoctrine()->getManager()->flush();

						if($stock!=null){
							$stock->setQuantity($stock->getQuantity()-($item->getQuantity()));


							$stock->setDateupd(new \Datetime());
							$this->getDoctrine()->getManager()->persist($stock);
							$this->getDoctrine()->getManager()->flush();
						}else{
								//Stocks doesnt exist, create it
								$stock=new ERPStocks();
								if($item->getVariant())
									$productvariant=$productVariantRepository->findOneBy(["product"=>$item->getProduct(), "variantvalue"=>$item->getVariant(),"active"=>1, "deleted"=>0]);
									else $productvariant=null;
								$stock->setProduct($item->getProduct());
								$stock->setCompany($this->getUser()->getCompany());
								$stock->setStorelocation($location);
								$stock->setQuantity(0-($item->getQuantity()));
								$stock->setDateadd(new \Datetime());
								$stock->setDateupd(new \Datetime());
								$stock->setActive(true);
								$stock->setDeleted(false);
								$stock->setProductvariant($productvariant);
								$this->getDoctrine()->getManager()->persist($stock);
								$this->getDoctrine()->getManager()->flush();
						}



						//Inform low Stock
					/*	$infostock=$infostocksRepository->findOneBy(["product"=>$item->getProduct(), "store"=>$store->getStore(), "productvariant"=>$item->getVariant(),"active"=>1, "deleted"=>0]);
						if($infostock){
							if($infostock->getMinimumQuantity()>=$stock->getQuantity()){
								//Inform to discotd channel
								$manager=$consumer->getManager();
								if($manager->getDiscordchannel()!=null){
									$channel=$manager->getDiscordchannel();
									$msg="Ref: **".$item->getProduct()->getCode()."** - ".$item->getProduct()->getName()." realizar traspaso a **".$store->getStore()->getName()."** - Cantidad: **".($infostock->getMaximunQuantity()-$stock->getQuantity()." unidades.**");
									file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
								}
							}
						} */
					}

					//Clear worklist
					foreach($worklistProducts as $item){
						$this->getDoctrine()->getManager()->remove($item);
						$this->getDoctrine()->getManager()->flush();
					}

			return new JsonResponse(["result"=>1]);
			}else return new JsonResponse(["result"=>-1, "text"=> "No hay productos para realizar la operación"]);
		}

		/**
	  * @Route("/{_locale}/erp/storesmanagers/operations/{id}/delete", name="deleteOperation")
	  */
	  public function deleteOperation($id){
	 	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	 $entityUtils=new GlobaleEntityUtils();
		 $documentRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperations::class);
		 $documentLinesRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperationsLines::class);
		 $stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
		 $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
		 $operation=$documentRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>false]);
		 if(!$operation) return new JsonResponse(['result' => -1]);
		 $location=$storeLocationsRepository->findOneBy(["store"=>$operation->getStore(), "company"=>$this->getUser()->getCompany(), "active"=>1,"deleted"=>0]);
		 if(!$location) return new JsonResponse(["result"=>-4, "text"=> "No existen ubicación en el almacén gestor"]);
		 $operationsLines=$documentLinesRepository->findBy(["operation"=>$operation, "deleted"=>0]);
		 foreach($operationsLines as $line){
			 $stock=$stocksRepository->findOneBy(["product"=>$line->getProduct(), "productvariant"=>$line->getVariant(), "company"=>$this->getUser()->getCompany(), "storelocation"=>$location, "active"=>1, "deleted"=>0]);
			 if(!$stock) continue;
			 $stock->setQuantity($stock->getQuantity()+($line->getQuantity()));
			 $this->getDoctrine()->getManager()->persist($stock);
			 $this->getDoctrine()->getManager()->flush();
		 }
		 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 	 return new JsonResponse(array('result' => $result));
	  }

		/**
		* @Route("/{_locale}/erp/storesmanagers/operations/exportxls", name="operationsexportxls")
		*/
		public function operationsexportxls(Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$operationsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperations::class);
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$productVariantRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$variantValuesRepository=$this->getDoctrine()->getRepository(ERPVariantsValues::class);
			$ids=$request->query->get('ids');

			//$ids=explode(",",$ids);
			$uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/temp/'.$this->getUser()->getId().'/';
			if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
					mkdir($uploadDir, 0775, true);
			}
			$filename = date("YmdHis").'_'.md5(uniqid()).'.xlsx';
			$errorstyle[] = array('fill'=>"#AA0000");

			$writer = new XLSXWriter();
			$header = array("string","string","string","string");
			$writer->setAuthor($this->getUser()->getName().' '.$this->getUser()->getLastname());
			$writer->writeSheetHeader('Hoja1', $header, $col_options = ['suppress_row'=>true] );
			$writer->writeSheetRow('Hoja1', ["CODIGO DE BARRAS", "CODIGO", "", "", "CANTIDAD","DESCRIPCION","CANTIDAD MIN","ERROR"]);
			$row_number=1;
			if($ids!=null){
				$lines=$operationsRepository->getOperationsProducts($this->getUser(),$ids);
				foreach($lines as $line){
					$error=null;
					if($line["variant_id"]==null)
						$barcode='P.'.str_pad($line["id"],8,'0', STR_PAD_LEFT);
						else{
							 $product=$productRepository->findOneBy(["id"=>$line["id"], "company"=> $this->getUser()->getCompany(),"deleted"=>0]);
							 if(!$product) $barcode='P.'.str_pad($line["id"],8,'0', STR_PAD_LEFT);
							 else{
								 $variantValue=$variantValuesRepository->findOneBy(["id"=>$line["variant_id"], "deleted"=>0]);
								 if(!$variantValue) $barcode='P.'.str_pad($line["id"],8,'0', STR_PAD_LEFT);
								 else{
							 		$variant=$productVariantRepository->findOneBy(["product"=>$product, "variantvalue"=> $variantValue, "deleted"=>0]);
									if(!$variant) $barcode='P.'.str_pad($line["id"],8,'0', STR_PAD_LEFT);
									else $barcode='V.'.str_pad($variant->getId(),8,'0', STR_PAD_LEFT);
								}
						 	 }
						 }
					if($line["qty"]<$line["minimumquantityofsale"]) $error="Cantidad minima";
					$row=[$barcode, $line["code"], "", "", $line["qty"],$line["name"],$line["minimumquantityofsale"],$error];
					if(!$error)	$writer->writeSheetRow('Hoja1', $row);
						else $writer->writeSheetRow('Hoja1', $row, array('fill'=>"#AA0000", "color"=>"#ffffff"));
					$row_number++;
				}
			}

			$writer->writeToFile($uploadDir.$filename);
			$response = new BinaryFileResponse($uploadDir.$filename);
			$response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'exported_operations.xlsx');
			return $response;
		}



		/**
		 * @Route("/{_locale}/erp/storesmanagers/operations/reports/{id}", name="storesManagersOperationsReports", defaults={"id"=0})
		 */
		 public function storesManagersOperationsReports($id, Request $request)
		 {

			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

			 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			 $locale = $request->getLocale();

			 $storeManagerRepository=$this->getDoctrine()->getRepository(ERPStoresManagers::class);
			 $storeManager=$storeManagerRepository->findOneBy(["id"=>$id]);
			 $storemanageruser=null;
			 $storeManagersUsersRepository=$this->getDoctrine()->getRepository(ERPStoresManagersUsers::class);
			 $storemanageruser=$storeManagersUsersRepository->findOneBy(["user"=>$this->getUser(),"manager"=>$storeManager,"isadmin"=>1]);

			 $storesmanagersusersstoresRepository=$this->getDoctrine()->getRepository(ERPStoresManagersUsersStores::class);
			 $store_objects=$storesmanagersusersstoresRepository->findBy(["manageruser"=>$storemanageruser,"active"=>1,"deleted"=>0]);
			 $stores=[];
			 $option=null;
			 $option["id"]=null;
			 $option["text"]="Selecciona Almacén...";
			 $stores[]=$option;
			 $option=null;
			 $option["id"]=-1;
			 $option["text"]="Todos";
			 $stores[]=$option;
			 foreach($store_objects as $item){
				 $option["id"]=$item->getStore()->getId();
				 $option["text"]=$item->getName();
				 $stores[]=$option;
			 }

			 $listConsumersOperationsReports = new ERPStoresManagersOperationsUtils();
			 $listOperationsLinesReports = new ERPStoresManagersOperationsLinesUtils();

			 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
					 return $this->render('@ERP/storesmanagersoperationsreports.html.twig', [
						 'controllerName' => 'storesManagersController',
						 'interfaceName' => 'StoresManagesReports',
						 'optionSelected' => 'genericindex',
						 'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagesOperations"],
						 'userData' => $userdata,
						 'id' => $id,
						 'stores' => $stores,
						 'consumersoperationslist' => $listConsumersOperationsReports->formatConsumersReportsList($id,null,null,null),
						 'productslist' => $listOperationsLinesReports->formatProductsReportsList($id,null,null,null),
						 'detailedconsumerlist'=>	$listOperationsLinesReports->formatConsumersReportsDetailedList(null,null,null,null),
						 'detailedproductlist'=>	$listOperationsLinesReports->formatProductsReportsDetailedList(null,null,null,null)

						 ]);
				 }
				 return new RedirectResponse($this->router->generate('app_login'));
		}

		/**
		 * @Route("/{_locale}/erp/storesmanagers/operations/localreports", name="storesManagersOperationsLocalReports")
		 */
		 public function storesManagersOperationsLocalReports(RouterInterface $router, Request $request)
		 {

			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			 $locale = $request->getLocale();
			 $this->router = $router;

			 $storemanageruser=null;
			 $storeManagersUsersRepository=$this->getDoctrine()->getRepository(ERPStoresManagersUsers::class);
			 $storemanageruser=$storeManagersUsersRepository->findOneBy(["user"=>$this->getUser(),"isadmin"=>1]);
			 if(!$storemanageruser)		 return new RedirectResponse($this->router->generate('app_login'));
			 else $id=$storemanageruser->getManager()->getId();

			 $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
			 $breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','StoresManagesOperations');
			 array_push($breadcrumb,$new_breadcrumb);

			 $storesmanagersusersstoresRepository=$this->getDoctrine()->getRepository(ERPStoresManagersUsersStores::class);
			 $store_objects=$storesmanagersusersstoresRepository->findBy(["manageruser"=>$storemanageruser,"active"=>1,"deleted"=>0]);
			 $stores=[];
			 $option=null;
			 $option["id"]=null;
			 $option["text"]="Selecciona Almacén...";
			 $stores[]=$option;
			 $option=null;
			 $option["id"]=-1;
			 $option["text"]="Todos los almacenes";
			 $stores[]=$option;
			 foreach($store_objects as $item){
				 $option["id"]=$item->getStore()->getId();
				 $option["text"]=$item->getName();
				 $stores[]=$option;
			 }
			 $listConsumersOperationsReports = new ERPStoresManagersOperationsUtils();
			 $listOperationsLinesReports = new ERPStoresManagersOperationsLinesUtils();
			 //$listConsumersOperationsDetailsReports = new ERPStoresManagersOperationsLinesUtils();
			 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
					 return $this->render('@ERP/storesmanagersoperationslocalreports.html.twig', [
						 'controllerName' => 'storesManagersOperationsController',
						 'interfaceName' => 'StoresManagesReports',
						 'optionSelected' => 'genericindex',
						 'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagersOperationsReports"],
						 'menuOptions' =>  $menurepository->formatOptions($userdata),
						 'breadcrumb' =>  $breadcrumb,
						 'userData' => $userdata,
						 'id' => $id,
						 'stores' => $stores,
						 'consumersoperationslist' => $listConsumersOperationsReports->formatConsumersReportsList($id,null,null,null),
						 'productslist' => $listOperationsLinesReports->formatProductsReportsList($id,null,null,null),
						 'detailedconsumerlist'=>	$listOperationsLinesReports->formatConsumersReportsDetailedList(null,null,null,null),
						 'detailedproductlist'=>	$listOperationsLinesReports->formatProductsReportsDetailedList(null,null,null,null)/*,
						 'include_pre_list_templates' => ['@ERP/storesmanagersoperationslocalreports_details.html.twig'],*/

						 ]);
				 }
				 return new RedirectResponse($this->router->generate('app_login'));
		}


		/**
		 * @Route("api/ERP/storesmanagers/operations/getreports/{id}", name="storesManagersOperationsGetReports", defaults={"id"=0})
		 */
		 public function storesManagersOperationsGetReports($id, Request $request)
		 {
					 $start=$request->request->get("start");
					 $end=$request->request->get("end");
					 $store=$request->request->get("store");
					 $start=date_create_from_format('d/m/Y',$start);
					 $end=date_create_from_format('d/m/Y',$end);
					 $operationsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperations::class);
					 $operationLinesRepository=$this->getDoctrine()->getRepository(ERPStoresManagersOperationsLines::class);

					 if($store=="-1"){
						 $array_consumers=$operationsRepository->getOperationsByConsumer($id,$start,$end,null);
						// $array_consumerproducts=$operationLinesRepository->getProductsByConsumer($id,$start,$end,null);
						 $array_bestproducts=$operationLinesRepository->getBestProducts($id,$start,$end,null);
					//	 $array_operations=$operationsRepository->getDailyOperations($id,$start,$end,null);

					 }
					 else{
						 $array_consumers=$operationsRepository->getOperationsByConsumer($id,$start,$end,$store);
					//	 $array_consumerproducts=$operationLinesRepository->getProductsByConsumer($id,$start,$end, $store);
						 $array_bestproducts=$operationLinesRepository->getBestProducts($id,$start,$end,$store);
						// $array_operations=$operationsRepository->getDailyOperations($id,$start,$end,$store);

					 }


					 $managerRepository=$this->getDoctrine()->getRepository(ERPStoresManagers::class);
					// $eanRepostory=$this->getDoctrine()->getRepository(ERPEAN13::class);
					 $manager=$managerRepository->findOneBy(["id"=>$id]);
					 $array=[];
					 /*
					 foreach($array_bestproducts as $best){
						 $ean13=$eanRepostory->getEANByCustomer($manager->getCustomer()->getId(),$best["product_id"]);
						 $best["ean13"]=$ean13;
						 array_push($array,$best);
					 }*/
					 return new JsonResponse(["from"=>$start, "to"=>$end, "consumers"=>$array_consumers, "bestproducts"=>$array_bestproducts]);


		 }




		/**
		 * @Route("/{_locale}/erp/storesmanagers/operations/consumersreportslist", name="consumersreportslist")
		 */
		public function consumersreportslist(RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$id=$request->query->get("id",$request->request->get("id"));
			$start=	$request->query->get("datefrom",$request->request->get("datefrom"));
			$store=	$request->query->get("store",$request->request->get("store"));
			if($store=="-1") $store=null;

			$datefrom=date_create_from_format('d/m/Y',$start);
			$end=	$request->query->get("dateto",$request->request->get("dateto"));
			$dateto=date_create_from_format('d/m/Y',$end);
			if($datefrom)	$start=$datefrom->format("Y-m-d");
			else{
				 $start=new \Datetime();
				 $start->setTimestamp(0);
				 $start=$start->format("Y-m-d");
			}
			if($dateto)	$end=$dateto->format("Y-m-d");
			else{
				 $end=new \Datetime();
				 $end=$end->format("Y-m-d");
			 }

			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsReports.json"),true);

			if($store)
			{
			$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperations::class,['o.consumer_id'=>'id','concat(u.name," ",IFNULL(u.lastname,""))'=>'agent__name_o_agent__lastname','concat(c.name," ",c.lastname)'=>'consumer__name_o_consumer__lastname','c.idcard'=>'consumer__idcard','c.code2'=>'consumer__code2','IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0)'=>'prueba'],
																																	'erpstores_managers_operations o
																																	LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																	LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
																																	LEFT JOIN globale_users u ON u.id=o.agent_id
																																	LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
																																	LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																	LEFT JOIN erpproduct_prices p ON p.id=l.product_id',
																																	'o.active=1 AND o.manager_id='.$id.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'" AND o.store_id='.$store.'
																																	GROUP BY(o.consumer_id)'
																																	);
			}
			else{
				$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperations::class,['o.consumer_id'=>'id','concat(u.name," ",IFNULL(u.lastname,""))'=>'agent__name_o_agent__lastname','concat(c.name," ",c.lastname)'=>'consumer__name_o_consumer__lastname','c.idcard'=>'consumer__idcard','c.code2'=>'consumer__code2','IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0)'=>'prueba'],
																																		'erpstores_managers_operations o
																																		LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																		LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
																																		LEFT JOIN globale_users u ON u.id=o.agent_id
																																		LEFT JOIN erpstores_managers_operations_lines l ON l.operation_id=o.id
																																		LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																		LEFT JOIN erpproduct_prices p ON p.id=l.product_id',
																																		'o.active=1 AND o.manager_id='.$id.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'"
																																		GROUP BY(o.consumer_id)'
																																		);

			}

			return new JsonResponse($return);

		}

		/**
		 * @Route("/{_locale}/erp/storesmanagers/operations/productsreportslist", name="productsreportslist")
		 */
		public function productsreportslist(RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$id=$request->query->get("id",$request->request->get("id"));
			$start=	$request->query->get("datefrom",$request->request->get("datefrom"));
			$store=	$request->query->get("store",$request->request->get("store"));
			if($store=="-1") $store=null;

			$datefrom=date_create_from_format('d/m/Y',$start);
			$end=	$request->query->get("dateto",$request->request->get("dateto"));
			$dateto=date_create_from_format('d/m/Y',$end);
			if($datefrom)	$start=$datefrom->format("Y-m-d");
			else{
				 $start=new \Datetime();
				 $start->setTimestamp(0);
				 $start=$start->format("Y-m-d");
			}
			if($dateto)	$end=$dateto->format("Y-m-d");
			else{
				 $end=new \Datetime();
				 $end=$end->format("Y-m-d");
			 }

			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsReports.json"),true);

			if($store){

			$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperations::class,['l.product_id'=>'id','l.code'=>'code','l.name'=>'name','IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0)'=>'suma'],
																																	'erpstores_managers_operations_lines l
																																	LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																	LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																	LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																	LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																	LEFT JOIN erpproducts pr ON pr.id=p.product_id',
																																	'o.active=1 AND o.manager_id='.$id.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'" AND o.store_id='.$store.'
																																	GROUP BY (l.product_id)',null
																																	);

		  }
			else{

			$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperations::class,['l.product_id'=>'id','l.code'=>'code','l.name'=>'name','IFNULL(ROUND(SUM(IFNULL(of.price,p.price)*l.quantity),2),0)'=>'suma'],
																																		'erpstores_managers_operations_lines l
																																		LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																		LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																		LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																		LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																		LEFT JOIN erpproducts pr ON pr.id=p.product_id',
																																		'o.active=1 AND o.manager_id='.$id.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'"
																																		GROUP BY (l.product_id)',null
																																		);

			}

			return new JsonResponse($return);

		}



		/**
		 * @Route("/{_locale}/erp/storesmanagers/operations/consumersreportsdetailedlist", name="consumersReportsDetailedList")
		 */
		 public function consumersReportsDetailedList(RouterInterface $router,Request $request)
		 {
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $user = $this->getUser();
			 $locale = $request->getLocale();
			 $this->router = $router;
			 $manager = $this->getDoctrine()->getManager();
			 $repository = $manager->getRepository($this->class);
			 $consumerid=$request->query->get("consumerid",$request->request->get("consumerid"));
			 $start=	$request->query->get("datefrom",$request->request->get("datefrom"));
			 $store=	$request->query->get("store",$request->request->get("store"));
			 if($store=="-1") $store=null;

			 $datefrom=date_create_from_format('d/m/Y',$start);
			 $end=	$request->query->get("dateto",$request->request->get("dateto"));
			 $dateto=date_create_from_format('d/m/Y',$end);
			 if($datefrom)	$start=$datefrom->format("Y-m-d");
			 else{
					$start=new \Datetime();
					$start->setTimestamp(0);
					$start=$start->format("Y-m-d");
			 }
			 if($dateto)	$end=$dateto->format("Y-m-d");
			 else{
					$end=new \Datetime();
					$end=$end->format("Y-m-d");
				}

			 $listUtils=new GlobaleListUtils();
			 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumersOperationsDetailedReports.json"),true);

			 if($consumerid)
			 {
					 if($store)
					 {
					 $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																			 'erpstores_managers_operations_lines l
																																			 LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																			 LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																			 LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
 																																			 LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																			 LEFT JOIN erpstores s ON s.id=o.store_id',
																																			 'o.active=1 AND o.consumer_id='.$consumerid.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'" AND o.store_id='.$store,null);
					 }
					 else{
						 $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																				 'erpstores_managers_operations_lines l
																																				 LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																				 LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																				 LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																				 LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																				 LEFT JOIN erpstores s ON s.id=o.store_id',
																																				 'o.active=1 AND o.consumer_id='.$consumerid.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'"',null);

					 }
		 	 }
			else{

				if($store)
				{
				$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																		'erpstores_managers_operations_lines l
																																		LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																		LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																		LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																		LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																		LEFT JOIN erpstores s ON s.id=o.store_id',
																																		'o.active=1 AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'" AND o.store_id='.$store,null);
				}
				else{
					$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																			'erpstores_managers_operations_lines l
																																			LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																			LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																			LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																			LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																			LEFT JOIN erpstores s ON s.id=o.store_id',
																																			'o.active=1 AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'"',null);

				}

			}
			 return new JsonResponse($return);


		 }


		 /**
			* @Route("/{_locale}/erp/storesmanagers/operations/productsreportsdetailedlist", name="productsReportsDetailedList")
			*/
			public function productsReportsDetailedList(RouterInterface $router,Request $request)
			{
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				$user = $this->getUser();
				$locale = $request->getLocale();
				$this->router = $router;
				$manager = $this->getDoctrine()->getManager();
				$repository = $manager->getRepository($this->class);
				$productid=$request->query->get("productid",$request->request->get("productid"));
				$start=	$request->query->get("datefrom",$request->request->get("datefrom"));
				$store=	$request->query->get("store",$request->request->get("store"));
				if($store=="-1") $store=null;

				$datefrom=date_create_from_format('d/m/Y',$start);
				$end=	$request->query->get("dateto",$request->request->get("dateto"));
				$dateto=date_create_from_format('d/m/Y',$end);
				if($datefrom)	$start=$datefrom->format("Y-m-d");
				else{
					 $start=new \Datetime();
					 $start->setTimestamp(0);
					 $start=$start->format("Y-m-d");
				}
				if($dateto)	$end=$dateto->format("Y-m-d");
				else{
					 $end=new \Datetime();
					 $end=$end->format("Y-m-d");
				 }

				$listUtils=new GlobaleListUtils();
				$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProductsOperationsDetailedReports.json"),true);

				if($productid){
						if($store){
						$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','concat(c.name," ",c.lastname)'=>'consumer__name_o_consumer__lastname','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																				'erpstores_managers_operations_lines l
																																				LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																				LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
																																				LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																				LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																				LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																				LEFT JOIN erpstores s ON s.id=o.store_id',
																																				'o.active=1 AND l.product_id='.$productid.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'" AND o.store_id='.$store,null);
						}
						else{
							$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','concat(c.name," ",c.lastname)'=>'consumer__name_o_consumer__lastname','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																					'erpstores_managers_operations_lines l
																																					LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																					LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
																																					LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																					LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																					 LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																					LEFT JOIN erpstores s ON s.id=o.store_id',
																																					'o.active=1 AND l.product_id='.$productid.' AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'"',null);

						}
				}
			 else{

				 if($store){
				 $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','concat(c.name," ",c.lastname)'=>'consumer__name_o_consumer__lastname','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																		 'erpstores_managers_operations_lines l
																																		 LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																		 LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
																																		 LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																		 LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																		 LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																		 LEFT JOIN erpstores s ON s.id=o.store_id',
																																		 'o.active=1 AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'" AND o.store_id='.$store,null);
				 }
				 else{
					 $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,ERPStoresManagersOperationsLines::class,['l.product_id'=>'id','o.date'=>'date','l.code'=>'code','l.name'=>'name','concat(c.name," ",c.lastname)'=>'consumer__name_o_consumer__lastname','s.name'=>'store__name','l.quantity'=>'quantity','IFNULL(of.price,p.price)'=>'price','IFNULL(ROUND(IFNULL(of.price,p.price)*l.quantity,2),0)'=>'total'],
																																			 'erpstores_managers_operations_lines l
																																			 LEFT JOIN erpstores_managers_operations o ON o.id=l.operation_id
																																			 LEFT JOIN erpstores_managers_consumers c ON c.id=o.consumer_id
																																			 LEFT JOIN erpstores_managers m ON m.id=o.manager_id
																																			 LEFT JOIN erpoffer_prices of ON of.id=l.product_id AND of.customer_id=m.customer_id
																																			 LEFT JOIN erpproduct_prices p ON p.id=l.product_id
																																			 LEFT JOIN erpstores s ON s.id=o.store_id',
																																			 'o.active=1 AND o.DATE >= "'.$start.'" AND o.DATE<="'.$end.'"',null);

				 }

			 }
				return new JsonResponse($return);


			}


			/**
			* @Route("/api/ERP/storesmanagers/operations/exportconsumeroperations", name="exportConsumerOperations")
			*/
			public function exportConsumerOperations(RouterInterface $router,Request $request)
			{
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				$template=dirname(__FILE__)."/../Forms/ConsumerOperations.json";

				$start=$request->query->get("start");
				$end=$request->query->get("end");
				$labels=$request->query->get("labels");
				$data=$request->query->get("data");

				$labels_array=explode(",",$labels);
				$data_array=explode(",",$data);
				$count=sizeof($labels_array);

				$result_array=Array();
				for($i=0;$i<$count;$i++){
					$item["trabajador"]=$labels_array[$i];
					$item["Operaciones"]=$data_array[$i];
					$result_array[]=$item;
				}


				$result=$this->csvConsumerOperations($result_array,$template);
				return $result;

			}


			public function csvConsumerOperations($list, $template){
				$this->template=$template;
				$filename='Operaciones.csv';
				$array=$list;
				//exclude tags column, last
				$key='_tags';
				array_walk($array, function (&$v) use ($key) {
				 unset($v[$key]);
				});
			//	 $array=$this->applyFormats($array);

				$fileContent=$this->createCSV($array);
				$response = new Response($fileContent);
				// Create the disposition of the file
					 $disposition = $response->headers->makeDisposition(
							 ResponseHeaderBag::DISPOSITION_ATTACHMENT,
							 $filename
				 );
				// Set the content disposition
				$seconds_to_cache = 0;
				$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
				$response->headers->set("Expires", $ts);
				$response->headers->set("Pragma", "cache");
				$response->headers->set("Cache-Control", "max-age=0, no-cache, must-revalidate, proxy-revalidate");
				$response->headers->set('Content-Type', 'application/force-download');
				$response->headers->set('Content-Type', 'application/octet-stream');
				$response->headers->set('Content-Type', 'application/download');
				$response->headers->set('Content-Disposition', $disposition);
				// Dispatch request
				return $response;

			}

			/**
 	 	 * @Route("/api/ERP/storesmanagers/operations/exportbestproducts", name="exportBestProducts")
 	 	 */
 	 	 public function exportBestProducts(RouterInterface $router,Request $request)
 	 	 {
 	 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 	 		 $template=dirname(__FILE__)."/../Forms/BestProducts.json";

 	 		 $start=$request->query->get("start");
 	 		 $end=$request->query->get("end");
 	 		 $labels=$request->query->get("labels");
 	 		 $data=$request->query->get("data");
 			 $codes=$request->query->get("codes");
 			 $names=$request->query->get("names");

 	 		 $labels_array=explode(",",$labels);
 	 		 $data_array=explode(",",$data);
 			 $codes_array=explode(",",$codes);
 			 $names_array=explode(",",$names);
 	 		 $count=sizeof($labels_array);

 	 		 $result_array=Array();
 	 		 for($i=0;$i<$count;$i++){
 				 $item["Código"]=$codes_array[$i];
 				 $item["Nombre"]=$names_array[$i];
 	 			 $item["Producto"]=$labels_array[$i];
 	 			 $item["Cantidad"]=$data_array[$i];
 	 		   $result_array[]=$item;
 	 		 }


 	 		 $result=$this->csvBestProducts($result_array,$template);
 	 		 return $result;

 	 	 }


 	 	 public function csvBestProducts($list, $template){
 	 		 $this->template=$template;
 	 		 $filename='Productos_mas_utilizados.csv';
 	 		 $array=$list;
 	 		 //exclude tags column, last
 	 		 $key='_tags';
 	 		 array_walk($array, function (&$v) use ($key) {
 	 			unset($v[$key]);
 	 		 });
 	 	 //	 $array=$this->applyFormats($array);

 	 		 $fileContent=$this->createCSV($array);
 	 		 $response = new Response($fileContent);
 	 		 // Create the disposition of the file
 	 				$disposition = $response->headers->makeDisposition(
 	 						ResponseHeaderBag::DISPOSITION_ATTACHMENT,
 	 						$filename
 	 			);
 	 		 // Set the content disposition
 	 		 $seconds_to_cache = 0;
 	 		 $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
 	 		 $response->headers->set("Expires", $ts);
 	 		 $response->headers->set("Pragma", "cache");
 	 		 $response->headers->set("Cache-Control", "max-age=0, no-cache, must-revalidate, proxy-revalidate");
 	 		 $response->headers->set('Content-Type', 'application/force-download');
 	 		 $response->headers->set('Content-Type', 'application/octet-stream');
 	 		 $response->headers->set('Content-Type', 'application/download');
 	 		 $response->headers->set('Content-Disposition', $disposition);
 	 		 // Dispatch request
 	 		 return $response;

 	 	 }

		 private function createCSV(array &$array){

				if (count($array) == 0) {
					return null;
				}
				ob_start();
				$df = fopen("php://output", 'w');
				$delimiter = ';';
				fputcsv($df, array_map("utf8_decode",array_keys(reset($array))), ";");
				foreach ($array as $row) {
					 fputcsv($df, array_values (array_map("utf8_decode", $row )), ";");
				}
				fclose($df);
				return ob_get_clean();
		}

}
