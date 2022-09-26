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
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPSupplierOrdersData;
use App\Modules\ERP\Entity\ERPSupplierActivities;
use App\Modules\ERP\Entity\ERPSupplierCommentLines;
use App\Modules\ERP\Entity\ERPProductsSuppliersDiscounts;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSuppliersUtils;
use App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPSuppliersController extends Controller
{
	private $class=ERPSuppliers::class;
	private $module='ERP';
	private $utilsClass=ERPSuppliersUtils::class;
    /**
     * @Route("/{_locale}/admin/global/suppliers", name="suppliers")
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
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Suppliers.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('suppliers', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'suppliersController',
  				'interfaceName' => 'Suppliers',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
	        'forms' => $templateForms,
					'include_post_templates' => ['@ERP/workactivitiesmap.html.twig','@ERP/productlistcategories.html.twig'],
					'include_footer' => [["type"=>"js", "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
															["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
															 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/suppliers/data/{id}/{action}", name="dataSuppliers", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/Suppliers.json";
		 $utils = new GlobaleFormUtils();
		 $obj = new $this->class();
		 //$default= new GlobaleCountries();
		 //$default=$default->findById(64);
		 $defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
		 $default=$defaultCountry->findOneBy(['name'=>"España"]);
		 $obj->setCountry($default);
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
		 $make= $utils->make($id, $this->class, $action, "formSuppliers", "full", "@Globale/form.html.twig", "formSupplier");
		 return $make;
		}


		/**
     * @Route("/{_locale}/ERP/suppliers/form/{id}", name="formSupplier", defaults={"id"=0})
     */
    public function formSupplier($id,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('suppliers');
    	$supplierrRepository=$this->getDoctrine()->getRepository($this->class);

			if($request->query->get('code',null)){
				$obj = $supplierrRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
				if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
				else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}

			$obj = $supplierrRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$entity_name=$obj?$obj->getSocialName():'';
			return $this->render('@Globale/generictabform.html.twig', array(
							'entity_name' => $entity_name,
							'controllerName' => 'SuppliersController',
							'interfaceName' => 'Proveedores',
							'optionSelected' => 'suppliers',
							'menuOptions' =>  $menurepository->formatOptions($userdata),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [
												["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Supplier data", "active"=>true, "route"=>$this->generateUrl("formInfoSuppliers",["id"=>$id])],
											/*	["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Supplier data", "active"=>true, "route"=>$this->generateUrl("dataSuppliers",["id"=>$id])],*/
												["name"=>  "supplierordersdata", "icon"=>"fa fa-money", "caption"=>"Orders Data","route"=>$this->generateUrl("infoSupplierOrdersData",["id"=>$id])],
											/*	["name" => "addresses", "icon"=>"fa fa-location-arrow", "caption"=>"Shipping addresses", "route"=>$this->generateUrl("addresses",["id"=>$id, "type"=>"supplier"])],*/
												["name" => "contacts",  "icon"=>"fa fa-users", "caption"=>"Contacts", "route"=>$this->generateUrl("generictablist",["function"=>"formatList","module"=>"ERP","name"=>"Contacts","id"=>$id])],
												["name" => "bankaccounts", "icon"=>"fa fa-money", "caption"=>"Bank Accounts", "route"=>$this->generateUrl("bankaccounts",["id"=>$id])],
												["name"=>"prices", "icon"=>"fa fa-money", "caption"=>"Shopping Discounts","route"=>$this->generateUrl("generictablist",["module"=>"ERP", "name"=>"productsuppliersdiscountss", "id"=>$id])],
												["name"=>"increments", "icon"=>"fa fa-money", "caption"=>"Increments","route"=>$this->generateUrl("generictablist",["module"=>"ERP", "name"=>"Increments", "id"=>$id])],
												["name" => "incidents", "icon"=>"fa fa-money", "caption"=>"Incidents", "route"=>$this->generateUrl("supplierincidents",["id"=>$id])],
											],

							'include_footer' => [["type"=>"js", "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																	["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
																	["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
																	["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
									));
			}




			/**
			 * @Route("/{_locale}/suppliers/info/{id}", name="formInfoSuppliers", defaults={"id"=0})
			 */
			public function formInfoSuppliers($id,  Request $request){
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
				$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
				$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
				$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
				$breadcrumb=$menurepository->formatBreadcrumb('suppliers');
				array_push($breadcrumb, $new_breadcrumb);
				$template=dirname(__FILE__)."/../Forms/Suppliers.json";
				$formUtils = new GlobaleFormUtils();
				$formUtilsSuppliers = new ERPSuppliersUtils();

				$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsSuppliers->getExcludedForm([]),$formUtilsSuppliers->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$id]));

				$listSuppliersCommentLines = new ERPSupplierCommentLinesUtils();
				$formUtilsSuppliersCommentLines = new GlobaleFormUtils();
				$formUtilsSuppliersCommentLines->initialize($this->getUser(), new ERPSupplierCommentLines(), dirname(__FILE__)."/../Forms/SupplierCommentLines.json", $request, $this, $this->getDoctrine());
				$forms[]=$formUtilsSuppliersCommentLines->formatForm('SupplierCommentLines', true, null, ERPSupplierCommentLines::class);
				$supplierRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
				$supplier=$supplierRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);

				return $this->render('@ERP/supplierform.html.twig', array(
					'controllerName' => 'suppliersController',
					'interfaceName' => 'Clientes',
					'optionSelected' => 'suppliers',
					'userData' => $userdata,
					'id' => $id,
					'id_object' => $id,
					'form' => $formUtils->formatForm('suppliers', true, $id, $this->class, "dataSuppliers"),
					'listSuppliersCommentLines' => $listSuppliersCommentLines->formatListBySupplierType($id,0),
					'forms' => $forms,
					'include_post_templates' => ['@ERP/workactivitiesmap.html.twig','@ERP/supplierlistworkactivities.html.twig'],
					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
															 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
					/*,
					'listSuppliersPrices' => $listCustomersPrices->formatListByCustomer($id),
					'listCustomersCommentLines' => $listCustomersCommentLines->formatListByCustomer($id)
					//'forms' => $forms
					*/
				));

			}


    /**
    * @Route("/api/global/supplier/{id}/get", name="getSupplier")
    */
    public function getSupplier($id){
      $supplier = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$supplier) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($supplier->encodeJson());
    }

  /**
   * @Route("/api/supplier/list", name="supplierlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Suppliers.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSuppliers::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }

	/**
	 * @Route("/api/supplier/listbuyorders", name="listSuppliersBuyOrders")
	 */
	public function indexlistbuyorders(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=[
		 ['name'=> 'id', 'caption'=>''],
		 ['name'=> 'code', 'caption'=>'Código'],
		 ['name'=> 'name', 'caption'=>'Nombre'],
		 ['name'=> 'address', 'caption'=>'Dirección'],
		 ['name'=> 'city', 'caption'=>'Localidad'],
		 ['name'=> 'phone', 'caption'=>'Teléfono']
		];
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSuppliers::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()],["type"=>"and", "column"=>"code", "operator"=>"<>", "value"=>""]]);
		return new JsonResponse($return);
	}

	/**
	* @Route("/{_locale}/admin/global/supplier/{id}/disable", name="disableSupplier")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/supplier/{id}/enable", name="enableSupplier")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/supplier/{id}/delete", name="deleteSupplier")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }


 /**
 * @Route("/{_locale}/ERP/supplier/workactivity/{id}/change/{idworkact}", name="changeSupplierWorkActivity", defaults={"id"=0, "idcat"=0})
 */
 public function changeSupplierWorkActivity($id, $idworkact, Request $request){
	$this->denyAccessUnlessGranted('ROLE_USER');
	$repositorySupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
	$repositoryWorkActivity=$this->getDoctrine()->getRepository(ERPSupplierActivities::class);
	$ids=null;
	if($id!=0){
		$ids=$id;
	}else {
		 $ids=$request->request->get('ids');
	}
	 $ids=explode(",",$ids);
	 foreach($ids as $item){
		 $supplier=$repositorySupplier->findOneBy(["id"=>$item, "company"=>$this->getUser()->getCompany()]);
		 $workactivity=$repositoryWorkActivity->findOneBy(["id"=>$idworkact]);
		 if($supplier && $workactivity){
			 $supplier->setWorkActivity($workactivity);
			 $this->getDoctrine()->getManager()->persist($supplier);
			 $this->getDoctrine()->getManager()->flush();
			 $result=1;
		 }else $result=-1;
	 }
	return new JsonResponse(array('result' => $result));
 }


 /**
* @Route("/api/ERP/supplier/orderinfo/{id}/get", name="getOrderInfo")
*/
public function getOrderInfo($id, RouterInterface $router,Request $request){
 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 $result = [];
 $supplierRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
 $supplierOrdersDataRepository=$this->getDoctrine()->getRepository(ERPSupplierOrdersData::class);
 $supplierCommentLinesRepository=$this->getDoctrine()->getRepository(ERPSupplierCommentLines::class);
 $supplier					=$supplierRepository->find(["id"=>$id]);
 if ($supplier!=null){
	 $result['suppliercode'] 	= $supplier->getCode();
	 if ($supplier->getTheircode()!=null){
		 $result['suppliertheircode'] = $supplier->getTheircode();
	 }else{
	 	 $result['suppliertheircode'] = '';
	 }
	 $result['suppliername'] 	= $supplier->getName();
	 $result['supplieremail'] = $supplier->getEmail();
	 $result['supplierphone'] = $supplier->getPhone();
	 if ($supplier->getPaymentmethod()!=null){
		$result['supplierpaymentmethod'] 	= $supplier->getPaymentmethod()->getId();
	 }else{
		$result['supplierpaymentmethod'] 	= 0;
	 }
	 if ($supplier->getPaymentterms()!=null)
	 	 $result['supplierpaymentterms'] 	= $supplier->getPaymentterms()->getId();
	 else
	 	 $result['supplierpaymentterms'] 	= 0;
 	 $result['suppliercarrier'] 	= 0;
	 // Otros datos
	 $result['supplieraddress'] 	= $supplier->getAddress();
	 $result['supplierpostcode'] 	= $supplier->getPostcode();
	 if ($supplier->getState()!=null)
	 	 $result['supplierstate'] 	= $supplier->getState()->getName();
	 else
	 	 $result['supplierstate'] 	= '';
	 if ($supplier->getCountry()!=null)
		 	$result['suppliercountry'] 	= $supplier->getCountry()->getName();
		 else
		 	$result['suppliercountry'] 	= '';
	 $result['suppliercity'] 	= $supplier->getPostcode();
	 $result['suppliervat'] 	= $supplier->getVat();
	 $supplierordersdata=$supplierOrdersDataRepository->findOneBy(["supplier"=>$supplier]);
	 if ($supplierordersdata!=null){
		 $result['minorder'] 			= $supplierordersdata->getMinorder();
		 $result['freeshipping'] 	= $supplierordersdata->getFreeshipping();
		 $result['estimateddelivery'] 	= $supplierordersdata->getEstimateddelivery();
	 }else{
		 $result['minorder'] 			= 0;
		 $result['freeshipping'] 	= 0;
		 $result['estimateddelivery'] 	= 0;
	 }
	 // Condiciones
	 $supplierCommentLines=$supplierCommentLinesRepository->getCommentByBuyOrder($supplier);
	 $result['suppliercomment'] 			= $supplierCommentLines['suppliercomment'];
	 $result['supplierbuyorder'] 			= $supplierCommentLines['supplierbuyorder'];
	 $result['suppliershipping'] 			= $supplierCommentLines['suppliershipping'];
	 $result['supplierpayment'] 			= $supplierCommentLines['supplierpayment'];
	 $result['supplierspecial'] 			= $supplierCommentLines['supplierspecial'];
 }
 return new JsonResponse($result);
}

}
