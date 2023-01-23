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
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProductsAttributes;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStoresManagersConsumers;
use App\Modules\ERP\Entity\ERPStoresManagersProducts;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesLogs;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannelsReplenishment;
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPStoresManagersUsersStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\ERP\Controller\ERPStocksController;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersConsumersUtils;
use App\Modules\ERP\Utils\ERPStoresManagersProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersUsersUtils;
use App\Modules\ERP\Utils\ERPStoresManagersVendingMachinesUtils;
use App\Modules\ERP\Utils\ERPStoresManagersVendingMachinesChannelsUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\ERP\Utils\ERPStoresManagersVendingMachinesLogsUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Reports\ERPPrintQR;
use App\Modules\ERP\Utils\ERPStoresManagersUtils;
use App\Modules\IoT\Entity\IoTSensors;
use App\Modules\IoT\Entity\IoTData;
use \DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Helpers\XLSXWriter\XLSXWriter;
use App\Modules\Navision\Entity\NavisionTransfers;


class ERPStoresManagersController extends Controller
{
	private $class=ERPStoresManagers::class;
	private $utilsClass=ERPStoresManagersUtils::class;
	private $module='ERP';

    /**
		 * @Route("/{_locale}/erp/storesmanagers/form/{id}", name="formStoresManagers", defaults={"id"=0})
		 */
		 public function formProduct($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/StoresManagers.json";
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','StoresManagers');
			array_push($breadcrumb, $new_breadcrumb);
			$repository=$this->getDoctrine()->getRepository($this->class);

			$tabs=[
				["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Manager data", "active"=>true, "route"=>$this->generateUrl("dataStoresManagers",["id"=>$id])],
				["name" => "storesmanagersvendingmachines", "caption"=>"Expendedoras", "icon"=>"fa-th","route"=>$this->generateUrl("listStoresManagersVendingMachines",["id"=>$id])],
				["name" => "storesmanagersproducts", "caption"=>"Products", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("listStoresManagersProducts",["id"=>$id])],
				["name" => "storesmanagersusers", "caption"=>"Users", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("listStoresManagersUsers",["id"=>$id])],
				["name" => "storesmanagersconsumers", "caption"=>"Consumidores", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("listStoresManagersConsumers",["id"=>$id])],
				//["name" => "storesmanagersoperationsreports", "caption"=>"Reports", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("storesManagersOperationsReports",["id"=>$id])],
				["name" => "transfers", "caption"=>"Transfers", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("generictablist",["function"=>"formatList","module"=>"Navision","name"=>"Transfers"])],
				["name" => "loads", "caption"=>"Loads List", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("listStoresManagersReplenishment",["id"=>$id])],
				["name" => "historyVM", "caption"=>"historyVendingMachines", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("listStocksHistoryVM",["manager"=>$id])],
				["name" => "history", "caption"=>"historyManager", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("listStocksHistoryManager",["manager"=>$id])]
			];
			$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$obj_name=$obj?$obj->getName():'';

				return $this->render('@Globale/generictabform.html.twig', array(
									'entity_name' => $obj_name,
									'controllerName' => 'StoresManagersController',
									'interfaceName' => 'Gestores',
									'optionSelected' => 'genericindex',
									'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagers"],
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
	 * @Route("/{_locale}/erp/storesmanagers/data/{id}/{action}", name="dataStoresManagers", defaults={"id"=0, "action"="read"})
	 */
	 public function dataStoresManagers($id, $action, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $template=dirname(__FILE__)."/../Forms/StoresManagers.json";
	 $utils = new GlobaleFormUtils();
	 $utilsObj=new ERPStoresManagersUtils();

	 $repository=$this->getDoctrine()->getRepository($this->class);
	 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 if($id!=0 && $obj==null){
			return $this->render('@Globale/notfound.html.twig',[]);
	 }
	 $classUtils=new ERPStoresManagersUtils();
	 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
	 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),$classUtils->getExcludedForm($params),$classUtils->getIncludedForm($params));
	 $make = $utils->make($id, $this->class, $action, "formStoresManagers", "full", "@Globale/form.html.twig", "formStoresManagers");
	 return $make;
	}


	/**
	 * @Route("/{_locale}/erp/storesmanagers/{id}/products", name="listStoresManagersProducts")
	 */
	public function listStoresManagersProducts($id,RouterInterface $router,Request $request)
	{
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
	$locale = $request->getLocale();
	$this->router = $router;
	$repository=$this->getDoctrine()->getRepository($this->class);
	$repositoryProducts=$this->getDoctrine()->getRepository(ERPStoresManagersProducts::class);
	$obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	$utils = new ERPStoresManagersUtils();
	$templateLists=$utils->formatProductsList($id);
	$formUtils=new GlobaleFormUtils();
	$utilsObj=new ERPStoresManagersProductsUtils();
	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj,	"product"=>null, "productvariant"=>null];
	$formUtils->initialize($this->getUser(), new ERPStoresManagersProducts(), dirname(__FILE__)."/../Forms/StoresManagersProducts.json",
	$request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
	method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	$templateForms[]=$formUtils->formatForm('StoresManagersProducts', true, $id, ERPStoresManagersProducts::class);

	return $this->render('@Globale/list.html.twig', [
			'id' => $id,
			'listConstructor' => $templateLists,
			'forms' => $templateForms,
			'userData' => $userdata,
			]);

	return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	* @Route("/{_locale}/erp/storesmanagersproducts/{id}/list", name="StoresManagersProductslist")
	*/
	public function StoresManagersProductslist($id, RouterInterface $router,Request $request)
	{
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$user = $this->getUser();
	$locale = $request->getLocale();
	$this->router = $router;
	$manager = $this->getDoctrine()->getManager();
	$repository = $manager->getRepository($this->class);
	$repositoryConsumers = $manager->getRepository(ERPStoresManagersProducts::class);
	$listUtils=new GlobaleListUtils();
	$obj=$repository->findBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "id"=>$id]);
	$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersProducts.json"),true);
	//$user,$repository,$request,$manager,$listFields,$classname,$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null)
	$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields, ERPStocks::class,
																			['pm.id'=>'id','p.name'=>'name','p.code'=>'code', 'pm.quantitytoserve'=>'quantitytoserve'],
																			'erpstores_managers_products pm
																			LEFT JOIN erpproducts_variants pv ON pv.id=pm.productvariant_id
																			LEFT JOIN erpproducts p ON p.id=pv.product_id',
																			'pm.manager_id='.$id.' and pm.deleted=0',
																			null,
																			'pm.id',
																		);
	return new JsonResponse($return);
	}


	/**
	 * @Route("/{_locale}/erp/storesmanagers/{id}/vendingmachines", name="listStoresManagersVendingMachines")
	 */
	public function listStoresManagersVendingMachines($id,RouterInterface $router,Request $request)
	{
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
	$locale = $request->getLocale();
	$this->router = $router;

	$repository=$this->getDoctrine()->getRepository($this->class);
	$obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);

	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	$utils = new ERPStoresManagersUtils();

	$templateLists=$utils->formatVendingMachinesList($id);
	$formUtils=new GlobaleFormUtils();

	$utilsObj=new ERPStoresManagersVendingMachinesUtils();
	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
	$formUtils->initialize($this->getUser(), new ERPStoresManagersVendingMachines(), dirname(__FILE__)."/../Forms/StoresManagersVendingMachines.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	$templateForms[]=$formUtils->formatForm('StoresManagersVendingMachines', true, $id, ERPStoresManagersVendingMachines::class);

		return $this->render('@Globale/list.html.twig', [
			'id' => $id,
			'listConstructor' => $templateLists,
			'forms' => $templateForms,
			'userData' => $userdata,
			]);

	return new RedirectResponse($this->router->generate('app_login'));
	}


	/**
	 * @Route("/{_locale}/erp/vendingmachinesbyuser/{idUser}", name="listStoresManagersVendingMachinesByUser")
	 */
	public function listStoresManagersVendingMachinesByUser($idUser,RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;
    $repository=$this->getDoctrine()->getRepository($this->class);
    $obj=$repository->findOneBy(["id"=>$idUser, "deleted"=>0]);
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $utils=new ERPStoresManagersVendingMachinesUtils();
    $templateLists=$utils->formatListbyUser($idUser);
    $templateForms=[];
    return $this->render('@Globale/list.html.twig', [
      'id' => $idUser,
      'listConstructor' => $templateLists,
      'forms' => $templateForms,
      'userData' => $userdata,
      ]);
    return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
   * @Route("/{_locale}/erp/vendingmachinesbyuser/{idUser}/list", name="StoresManagersVendingMachinesByUserlist")
   *
   */
  public function StoresManagersVendingMachinesByUserlist($idUser, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $userrepository= $manager->getRepository(GlobaleUsers::class);
    $user = $userrepository->findOneBy(["id"=>$idUser, "active"=>1, "deleted"=>0]);
    $repository = $manager->getRepository(ERPStocksHistory::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersVendingMachinesTab.json"),true);
    //$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null)
    $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields, ERPStocksHistory::class,
                                    ['vm.id'=>'id', 'vm.name'=>'name', 'vm.brand'=>'brand', 'vm.model'=>'model', 'vm.serial'=>'serial', 'vm.vpnip'=>'vpnip', 'vm.lastcheck'=>'lastcheck', 'vm.active'=>'active'],
                                    'erpstores_managers_vending_machines vm
                                    LEFT JOIN erpstore_locations sl ON sl.id=vm.storelocation_id
                                    LEFT JOIN erpstores st ON st.id=sl.store_id
                                    LEFT JOIN erpstores_users su ON su.user_id='.$idUser,
                                    'vm.active=1 and vm.deleted=0 and st.id=su.store_id',
                                    50,
                                    'vm.id',
                                  );
    return new JsonResponse($return);
  }


	/**
	 * @Route("/{_locale}/erp/storesmanagers/{id}/consumers", name="listStoresManagersConsumers")
	 */
	public function listStoresManagersConsumers($id,RouterInterface $router,Request $request)
	{
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
	$locale = $request->getLocale();
	$this->router = $router;

	$repository=$this->getDoctrine()->getRepository($this->class);
	$repositoryConsumers=$this->getDoctrine()->getRepository(ERPStoresManagersConsumers::class);
	$obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);

	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	$utils = new ERPStoresManagersUtils();

	$templateLists=$utils->formatConsumersList($id);
	$formUtils=new GlobaleFormUtils();

	$utilsObj=new ERPStoresManagersConsumersUtils();
	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
	$formUtils->initialize($this->getUser(), new ERPStoresManagersConsumers(), dirname(__FILE__)."/../Forms/StoresManagersConsumers.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	$templateForms[]=$formUtils->formatForm('StoresManagersConsumers', true, $id, ERPStoresManagersConsumers::class);

		return $this->render('@Globale/list.html.twig', [
			'id' => $id,
			'listConstructor' => $templateLists,
			'forms' => $templateForms,
			'userData' => $userdata,
			]);

	return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
		* @Route("/{_locale}/erp/storesmanagers/{id}/loads", name="storesManagersLoadReports")
		*/
		public function storesManagersLoadReports($id,RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$locale = $request->getLocale();
			$this->router = $router;
	  	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$machinesRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			return $this->render('@ERP/storesManagersLoadReports.html.twig', [
				'vendingmachines' => $machinesRepository->findBy(["active"=>1,"deleted"=>0, "manager"=>$id],["name"=>"ASC"]),
			]);

		}
	/**
	 * @Route("/{_locale}/erp/storesmanagersconsumers/{id}/list", name="StoresManagersConsumerslist")
	 */
	public function StoresManagersConsumerslist($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$repositoryConsumers = $manager->getRepository(ERPStoresManagersConsumers::class);
		$listUtils=new GlobaleListUtils();
		$obj=$repository->findBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "id"=>$id]);
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersConsumers.json"),true);
		$return=$listUtils->getRecords($user,$repositoryConsumers,$request,$manager,$listFields, ERPStoresManagersConsumers::class,[["type"=>"and", "column"=>"manager", "value"=>$obj]]);
		return new JsonResponse($return);
	}

	/**
	 * @Route("/{_locale}/erp/storesmanagersvendingmachines/{id}/list", name="StoresManagersVendingMachineslist")
	 */
	public function StoresManagersVendingMachineslist($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
		$listUtils=new GlobaleListUtils();
		$obj=$repository->findBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "id"=>$id]);
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersVendingMachinesTab.json"),true);
		$return=$listUtils->getRecords($user,$repositoryVendingMachines,$request,$manager,$listFields, ERPStoresManagersVendingMachines::class,[["type"=>"and", "column"=>"manager", "value"=>$obj]]);
		return new JsonResponse($return);
	}


	/**
	 * @Route("/{_locale}/erp/storesmanagers/{id}/users", name="listStoresManagersUsers")
	 */
	public function listStoresManagersUsers($id,RouterInterface $router,Request $request)
	{
	$childClass=ERPStoresManagersUsers::class;
	$utils = new ERPStoresManagersUtils();
	$utilsObj=new ERPStoresManagersUsersUtils();
	$templateLists=$utils->formatUsersList($id);
	$formName='StoresManagersUsers';
	$formJson=dirname(__FILE__)."/../Forms/StoresManagersUsers.json";

	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
	$locale = $request->getLocale();
	$this->router = $router;
	$repository=$this->getDoctrine()->getRepository($this->class);
	$repositoryChild=$this->getDoctrine()->getRepository($childClass);
	$obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
	$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

	$formUtils=new GlobaleFormUtils();
	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
	$formUtils->initialize($this->getUser(), new $childClass(), $formJson, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	$templateForms[]=$formUtils->formatForm($formName, true, $id, $childClass);

		return $this->render('@Globale/list.html.twig', [
			'id' => $id,
			'listConstructor' => $templateLists,
			'forms' => $templateForms,
			'userData' => $userdata,
			]);

	return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/{_locale}/erp/storesmanagersusers/{id}/list", name="StoresManagersUserslist")
	 */
	public function StoresManagersUserslist($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$repositoryConsumers = $manager->getRepository(ERPStoresManagersUsers::class);
		$listUtils=new GlobaleListUtils();
		$obj=$repository->findBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "id"=>$id]);
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersUsers.json"),true);
		$return=$listUtils->getRecords($user,$repositoryConsumers,$request,$manager,$listFields, ERPStoresManagersUsers::class,[["type"=>"and", "column"=>"manager", "value"=>$obj]]);
		return new JsonResponse($return);
	}

	/**
	 * @Route("/api/ERP/storesmanagers/consumers/get/{nfcid}", name="getStoresManagerConsumer", defaults={"nfcid"=-1})
	 */
	public function getStoresManagerConsumer($nfcid, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$repositoryConsumers = $manager->getRepository(ERPStoresManagersConsumers::class);
		$repositoryStoresManagersUsers = $manager->getRepository(ERPStoresManagersUsers::class);
		$managerUser=$repositoryStoresManagersUsers->findOneBy(["user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
		if(!$managerUser) return new JsonResponse(array('result' => -3, 'text'=>"Usuario no asignado a gestor"));
		if($nfcid!=-1)
			$obj=$repositoryConsumers->findOneBy(["active"=>1, "manager"=> $managerUser->getManager(),"deleted"=>0, "nfcid"=>$nfcid]);
		else
			$obj=$repositoryConsumers->findOneBy(["active"=>1, "manager"=> $managerUser->getManager(), "deleted"=>0, "id"=>$request->request->get('id',-1)]);

		if(!$obj) return new JsonResponse(array('result' => -1, 'text'=>"No existe este usuario"));
		if($obj->getManager()->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(array('result' => -2, 'text'=>"No existe este usuario"));

		$result["id"]=$obj->getId();
		$result["name"]=$obj->getName();
		$result["lastname"]=$obj->getLastname();
		$result["nfcid"]=$obj->getNfcid();
		$result["idcard"]=$obj->getIdcard();
		$result["code2"]=$obj->getCode2();
		$result["active"]=$obj->getActive();

		return new JsonResponse($result);

	}

	/**
	* @Route("/api/erp/storesmanagers/consumers/search", name="searchConsumer")
	*/
	public function searchConsumer(Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$search=$request->request->get('s',null);
		$repositoryUsers=$this->getDoctrine()->getRepository(ERPStoresManagersUsers::class);
		$usermanager=$repositoryUsers->findOneBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0]);
		if(!$usermanager) return new JsonResponse(array('result' => -1, 'text'=>"Usuario no asignado a gestor"));
		$repository=$this->getDoctrine()->getRepository(ERPStoresManagersConsumers::class);
		$result=$repository->search($search, $usermanager->getManager());
		return new JsonResponse($result);
	}

	/**
	* @Route("/api/erp/storesmanagers/consumers/changenfc/{id}/{nfcid}", name="changeconsumernfc", defaults={"nfcid"=-1})
	*/
	public function changeconsumernfc($id, $nfcid, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if($nfcid=="" || $nfcid==-1) return new JsonResponse(array('result' => -5, 'text'=>"El codigo nfc no puede ser nulo"));
		$repositoryUsers=$this->getDoctrine()->getRepository(ERPStoresManagersUsers::class);
		$usermanager=$repositoryUsers->findOneBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0]);
		if(!$usermanager) return new JsonResponse(array('result' => -1, 'text'=>"Usuario no asignado a gestor"));
		$repository=$this->getDoctrine()->getRepository(ERPStoresManagersConsumers::class);
		$consumer=$repository->findOneBy(["id"=>$id, "deleted"=>0]);
		if(!$consumer) return new JsonResponse(array('result' => -2, 'text'=>"Trabajador no encontrado"));
		if($consumer->getManager()!=$usermanager->getManager()) return new JsonResponse(array('result' => -3, 'text'=>"Trabajador no encontrado"));
		$obj=$repository->findOneBy(["nfcid"=>$nfcid,"deleted"=>0]);
		if($obj!=null && $obj->getId()!=$consumer->getId() && $nfcid!=null) return new JsonResponse(array('result' => -4, 'text'=>"Tarjeta ya asignada a otro trabajador"));

		$consumer->setNfcid($nfcid);
		$consumer->setDateupd(new \Datetime());
		$this->getDoctrine()->getManager()->persist($consumer);
		$this->getDoctrine()->getManager()->flush();
		return new JsonResponse(["result"=>1]);
	}


	/**
	 * @Route("/{_locale}/ERP/storesmanagers/vendingmachines/{id}/channels", name="StoresManagersVendingMachinesChannels", defaults={"id"=0})
	 */
		public function StoresManagersVendingMachinesChannels($id, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$locale = $request->getLocale();
			$this->router = $router;
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$utils = new ERPStoresManagersVendingMachinesChannelsUtils();
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new ERPStoresManagersVendingMachinesChannels(), dirname(__FILE__)."/../Forms/StoresManagersVendingMachinesChannels.json", $request, $this, $this->getDoctrine());
			$templateLists[]=$utils->formatList($id);

			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP', 'StoresManagers');
			array_push($breadcrumb, ["rute"=>null, "name"=>"Canales Expendedora", "icon"=>"fa fa-calendar-check-o"], $new_breadcrumb);

			$repository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			$obj = $repository->findOneBy(['id'=>$id, 'deleted'=>0]);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[
						"status_code"=>404,
						"status_text"=>"Objeto no encontrado"
					]);
			}
			$entity_name=$obj?$obj->getName():'';


			$templateForms[]=$formUtils->formatForm('StoresManagersVendingMachinesChannels', true, $id, ERPStoresManagersVendingMachinesChannels::class, null);
			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				return $this->render('@Globale/genericlist.html.twig', [
					'entity_name' => $entity_name,
					'controllerName' => 'ERPStoresManagersController',
					'interfaceName' => 'Canales máquina expendedora',
					'optionSelected' => 'genericindex',
					'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagers"],
					'menuOptions' =>  $menurepository->formatOptions($userdata),
					'breadcrumb' =>  $breadcrumb,
					'userData' => $userdata,
					'lists' => $templateLists,
					'forms' => $templateForms,
					'entity_id' => $id,
					'shift' => $id,
					'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
					]);
			}
			return new RedirectResponse($this->router->generate('app_login'));
		}

		/**
			* @Route("/{_locale}/ERP/storesmanagers/vendingmachines/{id}/lacks", name="StoresManagersVendingMachineLacks", defaults={"id"=0})
		*/
		public function ListStoresManagersVendingMachineLacks($id, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$locale = $request->getLocale();
			$this->router = $router;
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	 		$utils = new ERPStoresManagersVendingMachinesChannelsUtils();
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new ERPStoresManagersVendingMachinesChannels(), dirname(__FILE__)."/../Forms/StoresManagersVendingMachinesChannels.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('StoresManagersVendingMachinesChannels', true, $id, ERPStoresManagersVendingMachinesChannels::class, null);
			$templateLists[]=$utils->formatListLacks($id);

			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP', 'StoresManagers');
			array_push($breadcrumb, ["rute"=>null, "name"=>"Canales Expendedora", "icon"=>"fa fa-calendar-check-o"], $new_breadcrumb);

			$repository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
				$obj = $repository->findOneBy(['id'=>$id, 'deleted'=>0]);
				if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[
						"status_code"=>404,
						"status_text"=>"Objeto no encontrado"
					]);
				}
				$entity_name=$obj?$obj->getName():'';
				if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
					return $this->render('@Globale/genericlist.html.twig', [
						'entity_name' => $entity_name,
						'controllerName' => 'ERPStoresManagersController',
						'interfaceName' => 'Faltas máquina expendedora',
						'optionSelected' => 'genericindex',
						'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagers"],
						'menuOptions' =>  $menurepository->formatOptions($userdata),
						'breadcrumb' =>  $breadcrumb,
						'userData' => $userdata,
						'lists' => $templateLists,
						'forms' => $templateForms,
						'entity_id' => $id,
						'shift' => $id,
						'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
						'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															 	["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
					  ]);
					}
				return new RedirectResponse($this->router->generate('app_login'));
		}

		/**
		 * @Route("/api/ERP/storesmanagers/vendingmachines/channels/{id}/list", name="vendingmachinechannels")
		*/
		public function vendingmachinechannels($id, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$shiftsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			$shift = $shiftsRepository->find($id);
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersVendingMachinesChannels.json"),true);
			$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields,ERPStoresManagersVendingMachines::class,[["type"=>"and", "column"=>"vendingmachine", "value"=>$shift]]);
			return new JsonResponse($return);
		}

		/**
			* @Route("/api/ERP/storesmanagers/vendingmachines/channels/{id}/listlacks", name="vendingmachinechannelslacks")
		*/
		public function vendingmachinechannelslacks($id, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$shiftsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			$channelsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$shift = $shiftsRepository->find($id);
			$lacks=$channelsRepository->getLacks($shift);
			$result["recordsTotal"]=count($lacks);
			$result["recordsFiltered"]=count($lacks);
			$result["data"]=$lacks;
			return new JsonResponse($result);
		}


		/**
		 * @Route("/{_locale}/ERP/storesmanagers/vendingmachines/channels/data/{id}/{action}/{idvendingmachine}", name="dataVendingmachinechannels", defaults={"id"=0, "action"="read", "idvendingmachine"=0})
		 */
		 public function dataVendingmachinechannels($id, $idvendingmachine, $action, Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $template=dirname(__FILE__)."/../Forms/StoresManagersVendingMachinesChannels.json";
			 $utils = new GlobaleFormUtils();
			 $utilsObj=new ERPStoresManagersVendingMachinesChannelsUtils();
			 $repositoryVendingMachinesChannels=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			 $repositoryVendingMachines=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);

			 if($id==0){
				 if($idvendingmachine==0 ) $idvendingmachine=$request->query->get('vendingmachine');
				 if($idvendingmachine==0 || $idvendingmachine==null) $idvendingmachine=$request->request->get('form',[])["vendingmachine"];
				 $vendingmachine = $repositoryVendingMachines->find($idvendingmachine);
			 }	else $obj = $repositoryVendingMachinesChannels->find($id);

			 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "vendingmachine"=>$id==0?$vendingmachine:$obj->getVendingmachine()];
			 $utils->initialize($this->getUser(), new ERPStoresManagersVendingMachinesChannels(), $template, $request, $this, $this->getDoctrine(),
													method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
			 if($id==0) $utils->values(["vendingmachine"=>$vendingmachine]);
			 return $utils->make($id, ERPStoresManagersVendingMachinesChannels::class, $action, "StoresManagersVendingMachinesChannels", "modal");
		}


		/**
		 * @Route("/api/ERP/storesmanagers/vendingmachines/channel/get/{id}/{channel}", name="getStoresManagerVendingMachineChannel", defaults={"channel"=-1})
		 */
		public function getStoresManagerVendingMachineChannel($id, $channel, RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
			$repositoryVendingMachinesChannels = $manager->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
			if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
			if(strlen($channel)!=2) return new JsonResponse(array('result' => -1, 'text'=>"Canal incorrecto"));
			//$channel=$repositoryVendingMachinesChannels->findOneBy(["vendingmachine"=>$vendingmachine,"row"=>substr($channel,0,1),"col"=>substr($channel,1,1),"active"=>1,"deleted"=>0]);
			$channel=$repositoryVendingMachinesChannels->findOneBy(["vendingmachine"=>$vendingmachine,"channel"=>$channel,"active"=>1,"deleted"=>0]);
			if(!$channel) return new JsonResponse(array('result' => -1, 'text'=>"Canal no configurado"));
			if(!$channel->getProduct()) return new JsonResponse(array('result' => -1, 'text'=>"Canal sin producto configurado"));
			//Modificación BABCOCK 14/11/2022 -- No autorizar operacion cuando no hay stock
			//-------------------------------------------------------------------------------------------------
			if($channel->getQuantity()<=0){
				$date=new \DateTime();
	 			$description="Operación no autorizada por falta de stock en canal ".$channel->getName();
 				$vendingMachineLog= new ERPStoresManagersVendingMachinesLogs();
 				$vendingMachineLog->setVendingmachine($vendingmachine);
 				$vendingMachineLog->setType(2);
 				$vendingMachineLog->setDescription($description);
 				$vendingMachineLog->setDateadd($date);
 				$vendingMachineLog->setDateupd($date);
 				$vendingMachineLog->setActive(1);
 				$vendingMachineLog->setDeleted(0);
 				$this->getDoctrine()->getManager()->persist($vendingMachineLog);
 				$this->getDoctrine()->getManager()->flush();
				if($vendingmachine->getAlertnotifyaddress())
 					file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$vendingmachine->getAlertnotifyaddress().'&msg='.urlencode('Máquina '.$vendingmachine->getName().': '.$description));
				return new JsonResponse(array('result' => -1, 'text'=>"Canal sin stock en el sistema"));
			}
			//-------------------------------------------------------------------------------------------------
			$result["id"]=$channel->getId();
			$result["name"]=$channel->getName();
			$result["product_code"]=$channel->getProduct()->getCode();
			$result["product_name"]=$channel->getProduct()->getName();
			return new JsonResponse($result);

		}


		/**
	 * @Route("/{_locale}/ERP/storesmanagers/vendingmachines/vendingmachines/replenishment/{id}", name="replenishmentManagerVendingMachine",  defaults={"id"=0})
	 */
	 public function salesCommissions($id, RouterInterface $router,Request $request){
		 // El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
			 return $this->redirect($this->generateUrl('unauthorized'));

		 $globaleMenuOptionsRepository			= $this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

		 // Datos de usuario
		 $userdata				= $this->getUser()->getTemplateData($this, $this->getDoctrine());

		 // Miga
		 $nbreadcrumb=["rute"=>null, "name"=>"Configuración máquina", "icon"=>"fa fa-edit"];
		 $breadcrumb=$globaleMenuOptionsRepository->formatBreadcrumb('genericindex','ERP','StoresManagersVendingMachines');
		 array_push($breadcrumb,$nbreadcrumb);

		 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				 return $this->render('@ERP/storesmanagersreplenishmentvendingmachine.html.twig', [
					 'controllerName' => 'salesOrdersController',
					 'interfaceName' => 'Reaprovisionamiento Expendedoras',
					 'optionSelected' => 'genericindex',
					 'optionSelectedParams' => ["module"=>'ERP',"name"=>'StoresManagersVendingMachines'],
					 'menuOptions' =>  $globaleMenuOptionsRepository->formatOptions($userdata),
					 'breadcrumb' =>  $breadcrumb,
					 'userData' => $userdata,
					 'id' => $id,
					 'include_header' => []
					 ]);
			 }
			 return new RedirectResponse($this->router->generate('app_login'));

	 }



		/**
		 * @Route("/api/ERP/storesmanagers/vendingmachines/replenishment/channels/get/{id}", name="replenishmentManagerVendingMachineGetChannel", defaults={"id"=0})
		 */
		public function replenishmentManagerVendingMachineGetChannel($id, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$manager = $this->getDoctrine()->getManager();
			$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
			$repositoryVendingMachinesChannels = $manager->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
			if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
			$channels=$repositoryVendingMachinesChannels->findBy(["vendingmachine"=>$vendingmachine,"deleted"=>0],["row"=>"ASC", "col"=>"ASC"]);
			$maxCol=0;
			$result["result"]="";
			$result["maxCol"]="";
				foreach($channels as $channel){
					$channelData=[];
					$channelData["id"]=$channel->getId();
					$channelData["row"]=$channel->getRow();
					$channelData["col"]=$channel->getCol();
					$channelData["channel"]=$channel->getChannel();
					$channelData["name"]=$channel->getName();
					$channelData["product_id"]=$channel->getProduct()?$channel->getProduct()->getId():"0";
					$channelData["product_code"]=$channel->getProduct()?$channel->getProduct()->getCode():"";
					$channelData["product_name"]=$channel->getProduct()?$channel->getProduct()->getName():"";
					$channelData["quantity"]=$channel->getQuantity()/($channel->getMultiplier()?$channel->getMultiplier():1);
					$channelData["minquantity"]=$channel->getMinquantity();
					$channelData["maxquantity"]=$channel->getMaxquantity();
					$channelData["multiplier"]=$channel->getMultiplier()?$channel->getMultiplier():1;

					$color="#e94646"; //Rojo
					if($channel->getActive() && $channel->getProduct() && $channel->getQuantity()>$channel->getMinquantity()){
						$color="#589b4b"; //Verde
					}else{
						if($channel->getQuantity()<=$channel->getMinquantity() && $channel->getActive() && $channel->getProduct()){
								$color="#fba234"; //Naranja
						}else{
							if(!$channel->getActive() || !$channel->getProduct()){
								$color="#a1a1a1"; //Gris
							}
						}
					}
					$channelData["color"]=$color;
					$result["data"][]=$channelData;
					if($channel->getCol()>$maxCol) $maxCol=$channel->getCol();
				}
			$result["result"]=1;
			$result["maxCol"]=$maxCol;
			return new JsonResponse($result);

		}

		/**
		 * @Route("/api/ERP/storesmanagers/vendingmachines/replenishment/channels/add/{id}/{qty}", name="addReplenishmentManagerVendingMachineGetChannel", defaults={"id"=0})
		 */
		public function addReplenishmentManagerVendingMachineGetChannel($id,$qty, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$repositoryVendingMachinesChannels = $this->getDoctrine()->getManager()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$repositoryVendingMachinesChannelsReplenishment = $this->getDoctrine()->getManager()->getRepository(ERPStoresManagersVendingMachinesChannelsReplenishment::class);
			$repositoryStocks = $this->getDoctrine()->getManager()->getRepository(ERPStocks::class);
			$repositoryProductVariant = $this->getDoctrine()->getManager()->getRepository(ERPProductsVariants::class);

			$channel=$repositoryVendingMachinesChannels->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
			if(!$channel) return new JsonResponse(["result"=>-1, "text"=>"Canal incorrecto"]);
			if($channel->getProduct()==null && $channel->getProductcode()==null) return new JsonResponse(["result"=>-2, "text"=>"Canal no configurado"]);
			//Crear entidad de reaprovisionamiento
			$replenishment = new ERPStoresManagersVendingMachinesChannelsReplenishment();
			$replenishment->setChannel($channel);
			$replenishment->setProduct($channel->getProduct());
			$replenishment->setProductcode($channel->getProductcode());
			$replenishment->setProductname($channel->getProductname());
			$replenishment->setQuantity($qty*($channel->getMultiplier()?$channel->getMultiplier():1));
			$replenishment->setDateadd(new \Datetime());
			$replenishment->setDateupd(new \Datetime());
			$replenishment->setActive(1);
			$replenishment->setDeleted(0);
			$this->getDoctrine()->getManager()->persist($replenishment);
			$this->getDoctrine()->getManager()->flush();
			//Añadimos la carga al histórico de operaciones
			$typesRepository=$this->getDoctrine()->getRepository(ERPTypesMovements::class);
			$type=$typesRepository->findOneBy(["name"=>"Carga expendedora"]);
			$stockHistory= new ERPStocksHistory();
			$productvariant = $repositoryProductVariant->findOneBy(["product"=>$channel->getProduct(),"variant"=>null]);
			$stockHistory->setProductcode($productvariant->getProduct()->getCode());
			$stockHistory->setProductname($productvariant->getProduct()->getName());
			$stockHistory->setProductvariant($productvariant);
			if ($channel->getVendingmachine()->getStorelocation()!=null) {
					$stockHistory->setLocation($channel->getVendingmachine()->getStorelocation());
				}
				else {
					$locationRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
					$storeLocation=$locationRepository->findOneBy(["name"=>"EXPEND ALM"]);
					$stockHistory->setLocation($storeLocation);
			}
			$stockHistory->setVendingmachinechannel($channel);
			$stockHistory->setUser($this->getUser());
			$stockHistory->setCompany($this->getUser()->getCompany());
			$stockHistory->setPreviousqty($channel->getQuantity());
			$stockHistory->setNewqty($channel->getQuantity()+($qty*($channel->getMultiplier()?$channel->getMultiplier():1)));
			$stockHistory->setType($type);
			$stockHistory->setComment($channel->getVendingmachine()->getName());
			$stockHistory->setQuantity($qty*($channel->getMultiplier()?$channel->getMultiplier():1));
			$stockHistory->setActive(1);
			$stockHistory->setDeleted(0);
			$stockHistory->setDateupd(new \DateTime());
			$stockHistory->setDateadd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($stockHistory);
			$this->getDoctrine()->getManager()->flush();
			//Incrementar el stock en la maquina
			$channel->setQuantity($channel->getQuantity()+($qty*($channel->getMultiplier()?$channel->getMultiplier():1)));
			$this->getDoctrine()->getManager()->persist($channel);
			$this->getDoctrine()->getManager()->flush();
			//Decrementar el stock en la ubicacion asociada a la maquina si esta existe y el producto esta en ella para evitar errores pero...:
			//TODO: A futuro deberiamos no permitir la recarga si esta información no esta disponible
			//TODO: Añadir soporte para variantes
			if($channel->getVendingmachine()->getStorelocation()){
				$stock=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$channel->getVendingmachine()->getStorelocation(), "active"=>1, "deleted"=>0]);
				if($stock){
					$stockHistory= new ERPStocksHistory();
					$productvariant = $repositoryProductVariant->findOneBy(["product"=>$channel->getProduct(),"variant"=>null]);
	        $stockHistory->setProductcode($productvariant->getProduct()->getCode());
	        $stockHistory->setProductname($productvariant->getProduct()->getName());
					$stockHistory->setProductvariant($productvariant);
					if ($channel->getVendingmachine()->getStorelocation()!=null) {
							$stockHistory->setLocation($channel->getVendingmachine()->getStorelocation());
						}
						else {
							$locationRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
							$storeLocation=$locationRepository->findOneBy(["name"=>"EXPEND ALM"]);
							$stockHistory->setLocation($storeLocation);
					}
					$stockHistory->setUser($this->getUser());
					$stockHistory->setCompany($this->getUser()->getCompany());
					$stockHistory->setPreviousqty($stock->getQuantity());
					$stockHistory->setNewqty($stock->getQuantity()-($qty*($channel->getMultiplier()?$channel->getMultiplier():1)));
					$stockHistory->setType($type);
					$stockHistory->setQuantity(-($qty*($channel->getMultiplier()?$channel->getMultiplier():1)));
					$stockHistory->setActive(1);
					$stockHistory->setDeleted(0);
					$stockHistory->setDateupd(new \DateTime());
					$stockHistory->setDateadd(new \DateTime());
					$this->getDoctrine()->getManager()->persist($stockHistory);
					$this->getDoctrine()->getManager()->flush();
					$stock->setQuantity($stock->getQuantity()-($qty*($channel->getMultiplier()?$channel->getMultiplier():1)));
					$this->getDoctrine()->getManager()->persist($stock);
					$this->getDoctrine()->getManager()->flush();
				}
			}

			return new JsonResponse(["result"=>1]);
		}

		/**
		 * @Route("/api/ERP/storesmanagers/vendingmachines/consumers/get/{id}/{nfcid}", name="getStoresManagerVendingMachineConsumer", defaults={"id"=0, "nfcid"=-1})
		 */
		public function getStoresManagerVendingMachineConsumer($id, $nfcid, RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
			$repositoryConsumers = $manager->getRepository(ERPStoresManagersConsumers::class);
			$vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
			if(!$vendingmachine) return new JsonResponse(['result' => -1, 'text'=>"Error de acceso"]);
			$obj=$repositoryConsumers->findOneBy(["active"=>1, "deleted"=>0, "nfcid"=>$nfcid, "manager"=>$vendingmachine->getManager()]);
			if(!$obj) return new JsonResponse(['result' => -1, 'text'=>"No existe este usuario"]);
			$result["id"]=$obj->getId();
			$result["name"]=$obj->getName();
			$result["lastname"]=$obj->getLastname();
			$result["nfcid"]=$obj->getNfcid();
			$result["idcard"]=$obj->getIdcard();
			$result["code2"]=$obj->getCode2();
			$result["active"]=$obj->getActive();
			return new JsonResponse($result);
		}

		/**
		 * @Route("/api/ERP/storesmanagers/vendingmachines/config/get/{id}", name="getStoresManagerVendingMachineConfig", defaults={"id"=0})
		 */
		 public function getStoresManagerVendingMachineConfig($id, RouterInterface $router,Request $request)
		 {
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $manager = $this->getDoctrine()->getManager();
			 $repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
 			 $repositoryVendingMachinesChannels = $manager->getRepository(ERPStoresManagersVendingMachinesChannels::class);
 			 $vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
 			 if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
			 $channels=$repositoryVendingMachinesChannels->findBy(["vendingmachine"=>$vendingmachine, "active"=>1, "deleted"=>0]);
			 $result=["result"=>1, "data"=>[]];
			 foreach($channels as $channel){
				 if($channel->getProduct()!=null || $channel->getProductcode()!=null){
					 $item["id"]=$channel->getId();
					 $item["name"]=$channel->getName();
					 $item["channel"]=$channel->getChannel();
					 $item["multiplier"]=$channel->getMultiplier();
					 $item["minquantity"]=$channel->getMinquantity();
					 $item["maxquantity"]=$channel->getMaxquantity();
					 $item["gaps"]=$channel->getGaps();
					 $result["data"][]=$item;
			 	 }
			 }
			 return new JsonResponse($result);
		 }



		 /**
	  * @Route("/{_locale}/ERP/storesmanagers/vendingmachines/status/{id}", name="statusManagerVendingMachine",  defaults={"id"=0})
	  */
	  public function statusManagerVendingMachine($id, RouterInterface $router,Request $request){
	 	 // El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	 	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
	 		 return $this->redirect($this->generateUrl('unauthorized'));

	 	 $globaleMenuOptionsRepository			= $this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		 $repositoryVendingMachines 				= $this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);

	 	 // Datos de usuario
	 	 $userdata				= $this->getUser()->getTemplateData($this, $this->getDoctrine());

	 	 // Miga
	 	 $nbreadcrumb=["rute"=>null, "name"=>"Estado máquina", "icon"=>"fa fa-edit"];
	 	 $breadcrumb=$globaleMenuOptionsRepository->formatBreadcrumb('genericindex','ERP','StoresManagersVendingMachines');
	 	 array_push($breadcrumb,$nbreadcrumb);

		 //Obtener datos de la Expendedora
		 $vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
		 if(!$vendingmachine) {
			 	//TODO: Dirigir a pagina de error
			}

			$utils = new ERPStoresManagersVendingMachinesLogsUtils();
  		$logList=$utils->formatList($this->getUser(), $vendingmachine->getId());
			$formUtils=new GlobaleFormUtils();
			$template=dirname(__FILE__)."/../Forms/StoresManagersVendingMachinesLogs.json";
			$formUtils->initialize($this->getUser(), new ERPStoresManagersVendingMachinesLogsUtils(), $template, $request, $this, $this->getDoctrine(),$utils->getExcludedForm([]),$utils->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$id]));
			$templateForms[]=$formUtils->formatForm('StoresManagersVendingMachinesLogs', true, null, ERPStoresManagersVendingMachinesLogs::class);
			$logList["topButtonReload"]=false;

	 	 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
	 			 return $this->render('@ERP/storesmanagersstatusvendingmachine.html.twig', [
	 				 'controllerName' => 'salesOrdersController',
	 				 'interfaceName' => 'Estado Expendedora',
	 				 'optionSelected' => 'genericindex',
	 				 'optionSelectedParams' => ["module"=>'ERP',"name"=>'StoresManagersVendingMachines'],
	 				 'menuOptions' =>  $globaleMenuOptionsRepository->formatOptions($userdata),
	 				 'breadcrumb' =>  $breadcrumb,
	 				 'userData' => $userdata,
	 				 'id' => $id,
					 'vendingmachine' => $vendingmachine,
					 'listConstructor' => $logList,
					 'forms' => $templateForms,
	 				 'include_header' => []
	 				 ]);
	 		 }
	 		 return new RedirectResponse($this->router->generate('app_login'));

	  }


		/**
		 * @Route("/{_locale}/erp/storesmanagers/vendingmachines/logs/{id}/list", name="StoresManagersVendingMachineLogslist")
		 */
		public function StoresManagersVendingMachineLogslist($id, RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$locale = $request->getLocale();
			$this->router = $router;

			$repositoryVendingMachines 				= $this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			$repositoryVendingMachinesLogs 		= $this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesLogs::class);
			//Obtener datos de la Expendedora
	 	  $vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
	 	  if(!$vendingmachine) {
	 			 return new JsonResponse([]);
	 		}
			//TODO: Comprobar si la expendedora pertenece a la empresa del usuario actual

			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersVendingMachinesLogs.json"),true);
			$return=$listUtils->getRecords($user,$repositoryVendingMachinesLogs,$request,$this->getDoctrine()->getManager(),$listFields, ERPStoresManagersProducts::class,[["type"=>"and", "column"=>"vendingmachine", "value"=>$vendingmachine]]);
			return new JsonResponse($return);
		}


		/**
		 * @Route("/{_locale}/erp/storesmanagers/vendingmachines/logs/data/{id}/{action}", name="dataVendingmachinelogs", defaults={"id"=0, "action"="read"})
		 */
		 public function dataVendingmachinelogs($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/StoresManagersVendingMachinesLogs.json";
		 $utils = new GlobaleFormUtils();

		 $repository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesLogs::class);
		 $obj = $repository->findOneBy(['id'=>$id, 'deleted'=>0]);
		 if($id!=0 && $obj==null){
				return $this->render('@Globale/notfound.html.twig',[]);
		 }
		 $classUtils=new ERPStoresManagersVendingMachinesLogsUtils();
		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),$classUtils->getExcludedForm($params),$classUtils->getIncludedForm($params));
		 $make = $utils->make($id, ERPStoresManagersVendingMachinesLogs::class, $action, "StoresManagersVendingMachinesLogs", "modal");
		 return $make;
		}



		 /**
		* @Route("/api/ERP/storesmanagers/vendingmachines/command/{command}/{id}", name="commandManagerVendingMachine",  defaults={"id"=0})
		*/
		public function commandManagerVendingMachine($id, $command, RouterInterface $router,Request $request){
			// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$manager = $this->getDoctrine()->getManager();
			$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
			$vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
			if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
			if(!$vendingmachine->getVpnip()) return new JsonResponse(array('result' => -2, 'text'=>"Máquina expendedora no configurada correctamente"));
			$response=shell_exec("ssh -p 2222 root@".$vendingmachine->getVpnip()." \"python3 /etc/vendingmachine/commands/".$command.".py\"");
			$response_json=json_decode($response, true);
			if(json_last_error() === JSON_ERROR_NONE){
				return new JsonResponse(["result"=>1,"data"=>$response_json]);
			}else return new JsonResponse(["result"=>-1]);

		//	return new JsonResponse(["result"=>1, "data"=> json_decode('{"R1": "1", "R2": 0, "READER": 1, "LED": "GREEN", "T1": "29.56", "C1": "1", "C2": "1", "REPLENISHMENT_IFACE": 0, "SERVICE": 1, "TCORE0": 50.0, "OPERATOR": "O2", "NETTYPE": "FDD LTE", "SIGNAL": "23asu (-67dBm)", "TIMEON": "0 d\u00edas, 15:04:56"}', true)]);
		}


		/**
	 * @Route("/api/ERP/storesmanagers/vendingmachines/keepalive/{id}", name="keepaliveManagerVendingMachine",  defaults={"id"=0})
	 */
	 public function keepaliveManagerVendingMachine($id,RouterInterface $router,Request $request){
		 // El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $manager = $this->getDoctrine()->getManager();
		 $repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
		 $vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
		 if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
		 $vendingmachine->setLastcheck(new \DateTime());

		 //Notificar reestablecimiento de la comunicacion si procede
 		 if($vendingmachine->getConnectionlostnotified()){
 			 $vendingmachine->setConnectionlostnotified(false);
 			 $date=new \DateTime();
 			 $description='Conexión reestablecida el '.$date->format('d/m/Y').' a las '.$date->format('H:i:s');
 			 $vendingMachineLog= new ERPStoresManagersVendingMachinesLogs();
 			 $vendingMachineLog->setVendingmachine($vendingmachine);
 			 $vendingMachineLog->setType(2);
 			 $vendingMachineLog->setDescription($description);
 			 $vendingMachineLog->setDateadd($date);
 			 $vendingMachineLog->setDateupd($date);
 			 $vendingMachineLog->setActive(1);
 			 $vendingMachineLog->setDeleted(0);
 			 $this->getDoctrine()->getManager()->persist($vendingMachineLog);
 			 $this->getDoctrine()->getManager()->flush();
 			 if($vendingmachine->getAlertnotifyaddress()!=null){
 			 	file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$vendingmachine->getAlertnotifyaddress().'&msg='.urlencode('Máquina '.$vendingmachine->getName().': '.$description));
 			 }
 		 }

		 $this->getDoctrine()->getManager()->persist($vendingmachine);
		 $this->getDoctrine()->getManager()->flush();
		 return new JsonResponse(["result"=>1]);
	 }


	 /**
	 	* @Route("/api/ERP/storesmanagers/vendingmachines/sensors/{id}", name="sensorsVendingMachine",  defaults={"id"=0})
	 	*/
	 	public function sensorsManagerVendingMachine($id,RouterInterface $router,Request $request){
	 		// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	 		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 		$manager = $this->getDoctrine()->getManager();
	 		$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
	 		$repositoryIotSensors = $manager->getRepository(IoTSensors::class);
	 		$vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
	 		if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
	 		$sensorid=$request->request->get('sensor');
	 		$value=$request->request->get('value');
	 		$iotdevice=$vendingmachine->getIotdevice();
	 		if(!$iotdevice) return new JsonResponse(["result"=>-2]);
	 		$iotsensor=$repositoryIotSensors->findOneBy(["device"=>$iotdevice, "name"=> $sensorid, "active"=>1, "deleted"=>0]);
	 		if(!$iotsensor) return new JsonResponse(["result"=>-3]);
	 		$iotdata = new IoTData();
	 		$iotdata->setSensor($iotsensor);
	 		$iotdata->setData($value);
	 		$iotdata->setCounter(1);
	 		$iotdata->setDateadd(new \DateTime());
	 		$iotdata->setDateupd(new \DateTime());
	 		$iotdata->setActive(1);
	 		$iotdata->setDeleted(1);
	 		$this->getDoctrine()->getManager()->persist($iotdata);
	 		$this->getDoctrine()->getManager()->flush();
	 		if($sensorid=="C2"){ //Puerta de controlador
	 			$notify=false;
	 			if($value!=$vendingmachine->getOpencontrollerdoornotified(0)){
	 				$notify=true;
	 				$vendingmachine->setOpencontrollerdoornotified(intval($value));
	 				$this->getDoctrine()->getManager()->persist($vendingmachine);
	 				$this->getDoctrine()->getManager()->flush();
	 			}
	 			$date=new \DateTime();
	 			if($value==0){ //Puerta cerrada
	 				$description="Puerta de controlador cerrada el ".$date->format('d/m/Y').' a las '.$date->format('H:i:s');
	 			}else{ //Puerta abierta
	 				$description="Puerta de controlador abierta el ".$date->format('d/m/Y').' a las '.$date->format('H:i:s');
	 			}
	 			if($notify){
	 				$vendingMachineLog= new ERPStoresManagersVendingMachinesLogs();
	 				$vendingMachineLog->setVendingmachine($vendingmachine);
	 				$vendingMachineLog->setType(2);
	 				$vendingMachineLog->setDescription($description);
	 				$vendingMachineLog->setDateadd($date);
	 				$vendingMachineLog->setDateupd($date);
	 				$vendingMachineLog->setActive(1);
	 				$vendingMachineLog->setDeleted(0);
	 				$this->getDoctrine()->getManager()->persist($vendingMachineLog);
	 				$this->getDoctrine()->getManager()->flush();
	 				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$vendingmachine->getAlertnotifyaddress().'&msg='.urlencode('Máquina '.$vendingmachine->getName().': '.$description));
	 			}
	 		}
	 		return new JsonResponse(["result"=>1]);
	 	}


	 /**
 	* @Route("/api/erp/storesmanagers/vendingmachines/logs/add/{id}", name="addLogsManagerVendingMachine")
 	*/
 	public function addLogsManagerVendingMachine($id, Request $request){
 		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$manager = $this->getDoctrine()->getManager();
		$repositoryVendingMachines = $manager->getRepository(ERPStoresManagersVendingMachines::class);
		$vendingmachine=$repositoryVendingMachines->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
		if(!$vendingmachine) return new JsonResponse(array('result' => -1, 'text'=>"Máquina expendedora incorrecta"));
 		$type=$request->request->get('type',0);
		$description=$request->request->get('description','');
		$vendingMachineLog= new ERPStoresManagersVendingMachinesLogs();
		$vendingMachineLog->setVendingmachine($vendingmachine);
		$vendingMachineLog->setType($type);
		$vendingMachineLog->setDescription($description);
		$vendingMachineLog->setDateadd(new \DateTime());
		$vendingMachineLog->setDateupd(new \DateTime());
		$vendingMachineLog->setActive(1);
		$vendingMachineLog->setDeleted(0);
		$this->getDoctrine()->getManager()->persist($vendingMachineLog);
		$this->getDoctrine()->getManager()->flush();
		if($type==2){
				if($vendingmachine->getAlertnotifyaddress()!=null){
					$msg=$vendingmachine->getName().": ".$description;
					file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$vendingmachine->getAlertnotifyaddress().'&msg='.urlencode($msg));
					sleep(1);
				}
		}


 		return new JsonResponse(["result"=>1]);
 	}

	/**
	* @Route("/{_locale}/erp/storesmanagers/vendingmachines/exportchannels", name="exportchannels")
	*/
	public function exportchannels(Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$channelsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
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
		$writer->writeSheetRow('Hoja1', ["NOMBRE CANAL", "CANAL", "PRODUCTO", "CANTIDAD", "FALTAS", "MINIMO","MAXIMO"]);
		$row_number=1;
		if($ids!=null){
			$lines=$channelsRepository->getChannels($ids);
			foreach($lines as $line){
				if ($line["lacks"]>=0) $row=[$line["name"], $line["channel"], $line["productname"], $line["quantity"], '',  $line["minquantity"], $line["maxquantity"]];
				else $row=[$line["name"], $line["channel"], $line["productname"], $line["quantity"],  -$line["lacks"], $line["minquantity"],$line["maxquantity"]];
				$writer->writeSheetRow('Hoja1', $row);
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
		* @Route("/api/erp/getloads/{id}", name="getLoads")
		*/
	public function getLoads($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$loadsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
		$machineRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
		$objects=$loadsRepository->getLoadsMachine($id);
		$loads=[];
		foreach ($objects as $object){
			$load["vendingmachine"]=$machineRepository->findOneBy(["id"=>$id, "deleted"=>0])->getName();
			$load["date"]=$object["date"];
			$loads[]=$load;
		}
		return new JsonResponse(["loads"=>$loads]);
	}

	/**
		* @Route("/{_locale}/erp/storesmanagers/{id}/loadslist", name="storesManagersLoadLists")
		*/
	public function storesManagersLoadLists($id,RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$loadsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
		$machinesRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
		$machines=$machinesRepository->findBy(["active"=>1,"deleted"=>0, "manager"=>$id],["name"=>"ASC"]);
		$loads=[];
		foreach ($machines as $machine) {
			$dates=$loadsRepository->getLoadsMachine($machine->getId());
			foreach ($dates as $date) {
				$load["machine"]=$machine->getName();
				$load["date"]=$date["date"];
				$load["loads"]=$machinesRepository->getLoadsList($machine->getId(), $date["date"]);
				$loads[]=$load;
			}
		}
		$index=[];
		$machine=[];
		$date=[];
		$loadsss=[];
		foreach ($loads as $row) {
			$index[] = $row;
			$machine[]=$row['machine'];
			$date[]=$row['date'];
			$loadsss[]=$row['loads'];
		}
		array_multisort(
			$date, SORT_DESC,
			$machine,
			$index,
			$loadsss,
			$loads
		);
		return $this->render('@ERP/storesManagersLoadLists.html.twig', [
			'vendingmachines' => $machines,
			'date' => $dates,
			'loads' => $loads,
		]);
	}

		/**
		 	* @Route("/api/ERP/downloadLoads/{name}/{date}", name="downloadLoads")
			*/
		 public function downloadLoads($name, $date, RouterInterface $router,Request $request){
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  $new_item=json_decode($request->getContent());
			$loadRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$machineRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			$params["rootdir"]= $this->get('kernel')->getRootDir();
			$params["user"]=$this->getUser();
			$params["machine"]=$name;
			$params["date"]=$date;
			$params["lines"]=$loadRepository->getLoadsMachineDate($machineRepository->findOneBy(["name"=>$name, "deleted"=>0])->getId(),$date);
			$printQRUtils = new ERPPrintQR();
	 		$pdf=$printQRUtils->loadMachine($params);
	 		return new Response("", 200, array('Content-Type' => 'application/pdf'));
	  }

	/**
		* @Route("/api/ERP/storesmanagers/{id}/transferLists", name="storesManagersTransferLists")
		*/
	public function storesManagersTransferLists($id,RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$locationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
		$machinesRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
		$transfersRepository=$this->getDoctrine()->getRepository(NavisionTransfers::class);
		$productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$channelsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
		$lines=[];
		$trans=[];
		$transfers=$transfersRepository->getTransfersManageds();
		foreach ($transfers as $transfer){
			$store=$storesRepository->findOneBy(["id"=>$transfer["store"]]);
			$location=$locationsRepository->findOneBy(["store"=>$store]);
			$machine=$machinesRepository->findOneBy(["storelocation"=>$location]);
			$header["name"]=$transfer["name"];
			$header["date"]=$transfer["send"];
			$header["store"]=$store->getName();
			$products=$transfersRepository->getTransferLines($transfer);
			foreach ($products as $product) {
				$obj=$productsRepository->findOneBy(["id"=>$product["product_id"]]);
				$line["product"]=$obj->getCode();
				$line["name"]=$obj->getName();
				$line["quantity"]=$product["quantity"];
				if ($machine!=null) {
					$channel=$channelsRepository->findOneBy(["vendingmachine"=>$machine, "product"=>$obj]);
					if ($channel!=null) {
						$line["load"]=intval($product["quantity"]/$channel->getMultiplier());
						$line["multiplier"]=$channel->getMultiplier();
					}
				} else {
					$line["load"]='-';
					$line["multiplier"]='-';
				}
				$lines[]=$line;
			}
			$header["lines"]=$lines;
			$trans[]=$header;
			$lines=[];
		}
		return $this->render('@ERP/storesManagersTransferLists.html.twig', [
			'transfers' => $trans,
		]);
	}

	/**
		* @Route("/api/ERP/downloadTransfer/{name}", name="downloadTransfer")
		*/
	public function downloadTransfer($name,RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$new_item=json_decode($request->getContent());
		$channelsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
		$machineRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
		$transfersRepository=$this->getDoctrine()->getRepository(NavisionTransfers::class);
		$locationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
		$storeRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$location=$locationsRepository->findOneBy(["store"=>$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getDestinationstore()]);
		$machine=$machineRepository->findOneBy(["storelocation"=>$location]);
		$params["rootdir"]= $this->get('kernel')->getRootDir();
		$params["user"]=$this->getUser();
		$transfers=$transfersRepository->findBy(["name"=>$name, "active"=>1, "deleted"=>0]);
		$params["machine"]=$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getDestinationstore()->getName();
		$params["date"]=$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getDateadd()->format('Y-m-d');
		$lines=[];
		foreach ($transfers as $transfer) {
			$line['productcode']=$transfer->getProduct()->getCode();
			$line['productname']=$transfer->getProduct()->getName();
			$line['quantity']=$transfer->getQuantity();
			if ($machine!=null) {
				$channel=$channelsRepository->findOneBy(["vendingmachine"=>$machine, "product"=>$transfer->getProduct()]);
				$line["upload"]=intval($transfer->getQuantity()/$channel->getMultiplier());
				$line["multiplier"]=$channel->getMultiplier();
			} else {
				$line["upload"]='-';
				$line["multiplier"]='-';
			}
			$lines[]=$line;
		}
		$params["lines"]=$lines;
		$printQRUtils = new ERPPrintQR();
		$pdf=$printQRUtils->loadMachine($params);
		return new Response("", 200, array('Content-Type' => 'application/pdf'));
	}



		/**
			* @Route("/api/ERP/dataStoresManagersProducts/{id}/{action}", name="dataStoresManagersProducts", defaults={"id"=0, "action"="read"})
			*/
		public function dataStoresManagersProducts($id,$action, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$template=dirname(__FILE__)."/../Forms/StoresManagersProducts.json";
			$utils = new GlobaleFormUtils();
			$repository=$this->getDoctrine()->getRepository(ERPStoresManagersProducts::class);
			$obj = $repository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
	 	 	$utilsObj = new ERPStoresManagersProductsUtils();
			$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),
			"product"=>$obj?$obj->getProductvariant()->getProduct():null,
			"productvariant"=>$obj?$obj->getProductvariant():null];
			$utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
	                           method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
	                           method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
			$make = $utils->make($obj?$obj->getId():0, ERPStoresManagersProducts::class, $action, "StoresManagersProducts", "modal");
			return $make;
		}

		/**
			* @Route("/api/ERP/formStoresManagersProducts/{id}", name="formStoresManagersProducts", defaults={"id"=0})
			*/
		public function formStoresManagersProducts($id,RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$this->router = $router;
			$repositoryManagersProducts=$this->getDoctrine()->getRepository(ERPStoresManagersProducts::class);
			$managersProducts=$repositoryManagersProducts->findOneBy(["id"=>$id, "deleted"=>0]);
			$repositoryStocks=$this->getDoctrine()->getRepository(ERPStocks::class);
			$controllerStocks= new ERPStocksController;
			//$listStocks=$repositoryStocks->getManagersStocksByProduct($managersProducts->getManager()->getId(), $managersProducts->getProductvariant()->getId());
	    $manager = $this->getDoctrine()->getManager();
	    $listUtils=new ERPStocksUtils();
	    $listStocks=$listUtils->formatListProductsManagers($id,$managersProducts->getManager()->getId());

			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), null, dirname(__FILE__)."/../Forms/Stocks.json",$request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('stocks', true, $id, $this->class, 'dataStocks', ["id"=>$id, "action"=>"save"]);

			return $this->render('@Globale/list.html.twig', [
				'forms' => $templateForms,
				'listConstructor' => 	$listStocks
			]);
		}

		/**
			* @Route("/api/ERP/downloadTransfers", name="downloadTransfers")
			*/
		public function downloadTransfers(RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$channelsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
			$machineRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
			$transfersRepository=$this->getDoctrine()->getRepository(NavisionTransfers::class);
			$locationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			$storeRepository=$this->getDoctrine()->getRepository(ERPStores::class);
			$ids=$request->query->get('ids');
			if ($ids==null) return new Response();
			$ids=explode(",",$ids);
			$names=[];
			$params=[];
			$params["rootdir"]= $this->get('kernel')->getRootDir();
			$params["user"]=$this->getUser();
			foreach($ids as $id){
				$lineTransfer=$transfersRepository->findOneBy(["id"=>$id]);
				if ($lineTransfer==null) continue;
				$name=$lineTransfer->getName();
				if (array_search($name,$names)) continue;
				$names[]=$name;
				$location=$locationsRepository->findOneBy(["store"=>$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getDestinationstore()]);
				$machine=$machineRepository->findOneBy(["storelocation"=>$location]);
				$transfers=$transfersRepository->findBy(["name"=>$name, "active"=>1, "deleted"=>0]);
				$transferInfo["name"]=$name;
				$transferInfo["origin"]=$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getOriginstore()->getName();
				$transferInfo["destination"]=$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getDestinationstore()->getName();
				$transferInfo["datesend"]=$transfersRepository->findOneBy(["name"=>$name, "active"=>1, "deleted"=>0])->getDateadd()->format('d-m-Y');
				$lines=[];
				foreach ($transfers as $transfer) {
					$line['productcode']=$transfer->getProduct()->getCode();
					$line['productname']=$transfer->getProduct()->getName();
					$line['quantity']=$transfer->getQuantity();
					if ($machine!=null) {
						$channel=$channelsRepository->findOneBy(["vendingmachine"=>$machine, "product"=>$transfer->getProduct()]);
						if ($channel!=null) {
							$line["upload"]=intval($transfer->getQuantity()/$channel->getMultiplier());
							$line["multiplier"]=$channel->getMultiplier();
						} else {
							$line["upload"]='-';
							$line["multiplier"]='-';
						 }
					} else {
						$line["upload"]='-';
						$line["multiplier"]='-';
					}
					$lines[]=$line;
				}
				$transferInfo["lines"]=$lines;
				$params["transfers"][]=$transferInfo;
			}
			$printQRUtils = new ERPPrintQR();
			$pdf=$printQRUtils->downloadTransfers($params);
			return new Response("", 200, array('Content-Type' => 'application/pdf'));
		}

}
