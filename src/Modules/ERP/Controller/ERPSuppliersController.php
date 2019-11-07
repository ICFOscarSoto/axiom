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
use App\Modules\ERP\Entity\ERPShoppingDiscounts;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSuppliersUtils;
use App\Modules\ERP\Utils\ERPShoppingDiscountsUtils;
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
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
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
	        'forms' => $templateForms
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/suppliers/data/{id}/{action}", name="dataSuppliers", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
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
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('suppliers');
    	$supplierrRepository=$this->getDoctrine()->getRepository($this->class);
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
							'tabs' => [["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Supplier data", "active"=>true, "route"=>$this->generateUrl("dataSuppliers",["id"=>$id])],
												["name" => "addresses", "icon"=>"fa fa-location-arrow", "caption"=>"Shipping addresses", "route"=>$this->generateUrl("addresses",["id"=>$id, "type"=>"supplier"])],
												["name" => "contacts",  "icon"=>"fa fa-users", "caption"=>"Contacts", "route"=>$this->generateUrl("generictablist",["function"=>"formatList","module"=>"ERP","name"=>"Contacts","id"=>$id])],
												["name" => "bankaccounts", "icon"=>"fa fa-money", "caption"=>"Bank Accounts", "route"=>$this->generateUrl("bankaccounts",["id"=>$id])],
												["name"=>"prices", "icon"=>"fa fa-money", "caption"=>"Shopping Discounts","route"=>$this->generateUrl("generictablist",["module"=>"ERP", "name"=>"ShoppingDiscounts", "id"=>$id])],
												["name"=>"increments", "icon"=>"fa fa-money", "caption"=>"Increments","route"=>$this->generateUrl("generictablist",["module"=>"ERP", "name"=>"Increments", "id"=>$id])]
											],
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
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, Suppliers::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
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

}
