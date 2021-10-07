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
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPStoresUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPStoresController extends Controller
{
	private $class=ERPStores::class;
	private $module='ERP';
	private $utilsClass=ERPStoresUtils::class;

    /**
     * @Route("/{_locale}/admin/global/stores", name="stores")
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
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Stores.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('stores', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'storesController',
  				'interfaceName' => 'Stores',
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
		 * @Route("/{_locale}/ERP/store/form/{id}", name="formERPStore", defaults={"id"=0})
		 */
		public function formERPStore($id,Request $request)
		{
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		  $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		  $locale = $request->getLocale();
		  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		  $repository=$this->getDoctrine()->getRepository($this->class);
		  $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
		  $entity_name=$obj?$obj->getName():'';
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
			$breadcrumb=$menurepository->formatBreadcrumb('stores');
			array_push($breadcrumb,$new_breadcrumb);
		  return $this->render('@Globale/generictabform.html.twig', array(
		          'entity_name' => $entity_name,
		          'controllerName' => 'StoresController',
		          'interfaceName' => 'Almacenes',
							'optionSelected' => 'genericindex',
					    'optionSelectedParams' => ["module"=>"ERP", "name"=>"Products"],
		          'menuOptions' =>  $menurepository->formatOptions($userdata),
		          'breadcrumb' => $breadcrumb,
		          'userData' => $userdata,
		          'id' => $id,
		          'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
		          'tabs' => [	["name" => "data", "icon"=>"fa-address-card-o", "caption"=>"Datos almacén", "active"=>true, "route"=>$this->generateUrl("dataStores",["id"=>$id])],
													["name"=>"users", "icon"=>"fa fa-users", "caption"=>"Usuarios Asignados","route"=>$this->generateUrl("generictablist",["module"=>"ERP", "name"=>"StoresUsers", "id"=>$id, "parent"=>$id])],
													["name"=>"locations", "icon"=>"fa fa-users", "caption"=>"Ubicaciones","route"=>$this->generateUrl("listLocations",["id"=>$id])],
												],
		              ));
		  }


		/**
		 * @Route("/{_locale}/stores/data/{id}/{action}", name="dataStores", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/Stores.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formStores", "modal");
		}

    /**
    * @Route("/api/global/store/{id}/get", name="getStore")
    */
    public function getStore($id){
     $store = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$store) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($store->encodeJson());
    }

  /**
   * @Route("/api/store/list", name="storelist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStores::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Stores.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPStores::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }

	/**
	* @Route("/{_locale}/admin/global/store/{id}/disable", name="disableStore")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/store/{id}/enable", name="enableStore")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/store/{id}/delete", name="deleteStore")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }


 /**
  * @Route("/api/ERP/stores/get", name="getUserStores")
  */
 public function getUserStores(RouterInterface $router,Request $request)
 {
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $storesUsersRepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
	 $stores=$storesUsersRepository->findBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0]);

 	$arrayStores=[];
 	foreach($stores as $store){
		if(!$store->getStore()->getActive() || !$store->getStore()->getDeleted) continue;
 		$item["id"]			  =$store->getStore()->getId();
		$item["code"]		  =$store->getStore()->getCode();
		$item["name"]		  =$store->getStore()->getName();
		$item["address"]  =$store->getStore()->getAddress();
		$item["city"]		  =$store->getStore()->getCity();
		$item["postcode"]	=$store->getStore()->getPostcode();
		$item["phone"]		=$store->getStore()->getPhone();
 		$arrayStores[]=$item;
 	}
 	return new JsonResponse($arrayStores);
 }

 /**
  * @Route("/api/ERP/stores/getstoreinfo/{id}", name="getStoreInfo", defaults={"id"=0})
  */
	public function getStoreInfo($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$store=$storesRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
		$item["name"]=$store->getName();
		$item["address"]=$store->getAddress();
		$item["city"]=$store->getCity();
		$item["postcode"]=$store->getPostcode();
		$item["phone"]=$store->getPhone();
		$item["stateid"]=3;
		$item["statename"]="Albacete";
		$item["countryid"]=64;
		$item["countryname"]="España";
		return new JsonResponse(["infostore"=>$item]);

	}



}
