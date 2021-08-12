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
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPStoresManagersUsersStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersConsumersUtils;
use App\Modules\ERP\Utils\ERPStoresManagersUsersUtils;
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
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','HR','Meetings');
			array_push($breadcrumb, $new_breadcrumb);
			$repository=$this->getDoctrine()->getRepository($this->class);

			$tabs=[
				["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Managers data", "active"=>true, "route"=>$this->generateUrl("dataStoresManagers",["id"=>$id])],
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
		if($nfcid!=-1)
			$obj=$repositoryConsumers->findOneBy(["active"=>1, "deleted"=>0, "nfcid"=>$nfcid]);
		else $obj=$repositoryConsumers->findOneBy(["active"=>1, "deleted"=>0, "id"=>$request->request->get('id',-1)]);

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


}
