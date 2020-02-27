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
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPCustomersPrices;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPCustomersUtils;
use App\Modules\ERP\Utils\ERPCustomersPricesUtils;
use App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPCustomersController extends Controller
{
	private $class=ERPCustomers::class;
	private $module='ERP';
	private $utilsClass=ERPCustomersUtils::class;
    /**
     * @Route("/{_locale}/admin/global/customers", name="customers")
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
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Customers.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('contacts', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'customersController',
  				'interfaceName' => 'Customers',
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
		 * @Route("/{_locale}/customers/data/{id}/{action}", name="dataCustomers", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Customers.json";
		 $utils = new GlobaleFormUtils();
		 $obj = new $this->class();
		 //$default= new GlobaleCountries();
		 //$default=$default->findById(64);
		 $defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
		 $default=$defaultCountry->findOneBy(['name'=>"España"]);
		 $defaultCountry2=$this->getDoctrine()->getRepository(ERPCustomerGroups::class);
		 $default2=$defaultCountry2->findOneBy(['name'=>"Grupo1"]);
		 $obj->setCountry($default);
		 $obj->setCustomergroup($default2);
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
		 $make= $utils->make($id, $this->class, $action, "formCustomers", "full", "@ERP/customerform.html.twig", "formCustomer");
		 return $make;
		}

		/**
     * @Route("/{_locale}/ERP/customer/form/{id}", name="formCustomer", defaults={"id"=0})
     */
    public function formCustomer($id,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('customers');
    	$contactrRepository=$this->getDoctrine()->getRepository($this->class);
			$obj = $contactrRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$entity_name=$obj?$obj->getSocialName():'';
			return $this->render('@Globale/generictabform.html.twig', array(
							'entity_name' => $entity_name,
							'controllerName' => 'CustomersController',
							'interfaceName' => 'Clientes',
							'optionSelected' => 'customers',
							'menuOptions' =>  $menurepository->formatOptions($userdata),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "icon"=>"fa fa-headphones", "caption"=>"Datos cliente", "active"=>true, "route"=>$this->generateUrl("formInfoCustomer",["id"=>$id])],
												["name" => "contacts",  "icon"=>"fa fa-users", "caption"=>"Contacts", "route"=>$this->generateUrl("generictablist",["function"=>"formatListCustomers","module"=>"ERP","name"=>"Contacts","id"=>$id])],
												["name" => "offerprices",  "icon"=>"fa fa-money", "caption"=>"Precios Específicos", "route"=>$this->generateUrl("generictablist",["function"=>"formatListByCustomer","module"=>"ERP","name"=>"OfferPrices","id"=>$id])],
												//["name"=>"offerprices", "icon"=>"fa fa-money", "caption"=>"Precios Específicos","route"=>$this->generateUrl("infoCustomerOfferPrices",["module"=>"ERP", "name"=>"OfferPrices", "id"=>$id])]
												//["name" => "addresses", "icon"=>"fa fa-headphones", "caption"=>"direcciones", "route"=>$this->generateUrl("addresses",["id"=>$id, "type"=>"contact"])],
												//["name" => "contacts", "icon"=>"fa fa-headphones", "caption"=>"contactos" , "route"=>$this->generateUrl("contacts",["id"=>$id])],
												//["name" => "bankaccounts", "icon"=>"fa fa-headphones", "caption"=>"Cuentas bancarias", "route"=>$this->generateUrl("bankaccounts",["id"=>$id])]
											],


									'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																			["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
									'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
																			 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
									));
			}

			/**
			 * @Route("/{_locale}/customers/info/{id}", name="formInfoCustomer", defaults={"id"=0})
			 */
			public function formInfoCustomer($id,  Request $request){
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
				$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
				$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
				$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
				$breadcrumb=$menurepository->formatBreadcrumb('customers');
				array_push($breadcrumb, $new_breadcrumb);
				$template=dirname(__FILE__)."/../Forms/Customers.json";
				$formUtils = new GlobaleFormUtils();
				$formUtilsCustomers = new ERPCustomersUtils();
				$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsCustomers->getExcludedForm([]),$formUtilsCustomers->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$id]));
				$listCustomersPrices = new ERPCustomersPricesUtils();
				$listCustomersCommentLines = new ERPCustomerCommentLinesUtils();
			  $formUtilsCustomersPrices = new GlobaleFormUtils();
			  $formUtilsCustomersPrices->initialize($this->getUser(), new ERPCustomersPrices(), dirname(__FILE__)."/../Forms/CustomersPrices.json", $request, $this, $this->getDoctrine());
				$forms[]=$formUtilsCustomersPrices->formatForm('CustomersPrices', true, null, ERPCustomersPrices::class);


				$customerRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
				$customer=$customerRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);
		
				return $this->render('@ERP/customerform.html.twig', array(
					'controllerName' => 'customersController',
					'interfaceName' => 'Clientes',
					'optionSelected' => 'customers',
					'userData' => $userdata,
					'id' => $id,
					'id_object' => $id,
					'form' => $formUtils->formatForm('customers', true, $id, $this->class, "dataCustomers"),
					'listCustomersPrices' => $listCustomersPrices->formatListByCustomer($id),
					'listCustomersCommentLines' => $listCustomersCommentLines->formatListByCustomer($id),
					'forms' => $forms
				));

			}


    /**
    * @Route("/api/global/customer/{id}/get", name="getCustomer")
    */
    public function getCustomer($id){
      $contact = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$contact) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($contact->encodeJson());
    }

  /**
   * @Route("/api/customer/list", name="customerlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Customers.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPCustomers::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/customer/{id}/disable", name="disableCustomer")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/customer/{id}/enable", name="enableCustomer")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/customer/{id}/delete", name="deleteCustomer")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

 /**
	* @Route("/{_locale}/ERP/customers/get/emailaddres/{id}", name="getERPEmailAddress", defaults={"id"=0})
	*/
	public function getEmailAddress($id, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $customerRepository=$this->getDoctrine()->getRepository($this->class);
	 $customerContactsRepository=$this->getDoctrine()->getRepository(ERPContacts::class);
	 $customer=$customerRepository->findOneBy(['code'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 $contacts=$customerContactsRepository->findBy(['customer'=>$customer, 'invoice'=>1, 'active'=>1, 'deleted'=>0]);
	 $mailadresses=[];
	 //First mail company

	 if($customer!=NULL && $customer->getEmail()!=""){
		 $mailadress=$customer->getName()." <".$customer->getEmail().">";
		 $mailadresses[]=$mailadress;
	 }
	 foreach($contacts as $contact){
		 if($contact->getEmail()!=""){
			 $mailadress=$contact->getName()." ".$contact->getLastname()." <".$contact->getEmail().">";
			 $mailadresses[]=$mailadress;
		 }
	 }
	 return new JsonResponse(["adresses"=>$mailadresses]);
	}

}
