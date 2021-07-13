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
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPWorkList;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersConsumersUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Utils\ERPStoresManagersUtils;

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
						$infostock=$infostocksRepository->findOneBy(["product"=>$item->getProduct(), "store"=>$store->getStore(), "productvariant"=>$item->getVariant(),"active"=>1, "deleted"=>0]);
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
						}
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

}
