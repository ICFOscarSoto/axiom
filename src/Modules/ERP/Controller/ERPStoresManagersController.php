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
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStoresManagersConsumers;
use App\Modules\ERP\Entity\ERPStoresManagersProducts;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannelsReplenishment;
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPStoresManagersUsersStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
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
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Utils\ERPStoresManagersUtils;
use \DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


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
				["name" => "storesmanagersoperationsreports", "caption"=>"Reports", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("storesManagersOperationsReports",["id"=>$id])]
			];
			$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$obj_name=$obj?$obj->getName():'';
			/*$tabs=array_merge($tabs,[["name" => "ean13",  "icon"=>"fa fa-users", "caption"=>"EAN13", "route"=>$this->generateUrl("listEAN13",["id"=>$id])],
			["name" => "references",  "icon"=>"fa fa-users", "caption"=>"References", "route"=>$this->generateUrl("listReferences",["id"=>$id])],
			["name"=>  "productPrices", "icon"=>"fa fa-money", "caption"=>"Prices","route"=>$this->generateUrl("infoProductPrices",["id"=>$id])],
			["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("infoStocks",["id"=>$id])],
			["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]]);*/

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
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
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
	$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
	$formUtils->initialize($this->getUser(), new ERPStoresManagersProducts(), dirname(__FILE__)."/../Forms/StoresManagersProducts.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
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
		$return=$listUtils->getRecords($user,$repositoryConsumers,$request,$manager,$listFields, ERPStoresManagersProducts::class,[["type"=>"and", "column"=>"manager", "value"=>$obj]]);
		return new JsonResponse($return);
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
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersVendingMachines.json"),true);
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
		/*$repositoryStoresManagersUsers = $manager->getRepository(ERPStoresManagersUsers::class);
		$managerUser=$repositoryStoresManagersUsers->findOneBy(["user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
		if(!$managerUser) return new JsonResponse(array('result' => -3, 'text'=>"Usuario no asignado a gestor"));*/
		if($nfcid!=-1)
			$obj=$repositoryConsumers->findOneBy(["active"=>1, "deleted"=>0, "nfcid"=>$nfcid]);
			else $obj=$repositoryConsumers->findOneBy(["active"=>1, "deleted"=>0, "id"=>$request->request->get('id',-1)]);
			/*$obj=$repositoryConsumers->findOneBy(["active"=>1, "manager"=> $managerUser,"deleted"=>0, "nfcid"=>$nfcid]);
		else $obj=$repositoryConsumers->findOneBy(["active"=>1, "manager"=> $managerUser, "deleted"=>0, "id"=>$request->request->get('id',-1)]);*/

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
			$stockHistory= new ERPStockHistory();
			$stockHistory->setProduct($channel->getProduct());
			if ($channel->getVendingmachine()->getStorelocation()!=null) {
					$stockHistory->setLocation($channel->getVendingmachine()->getStorelocation());
					$stockHistory->setStore($channel->getVendingmachine()->getStorelocation()->getStore());
				}
				else {
					$locationRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
					$storeLocation=$locationRepository->findOneBy(["name"=>"EXPEND ALM"]);
					$stockHistory->setLocation($storeLocation);
					$stockHistory->setStore($storeLocation->getStore());
			}
			$stockHistory->setUser($this->getUser());
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
				$stock=$repositoryStocks->findOneBy(["product"=>$channel->getProduct(), "storelocation"=>$channel->getVendingmachine()->getStorelocation(), "active"=>1, "deleted"=>0]);
				if($stock){
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
}
