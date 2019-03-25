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
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPCustomersUtils;

class ERPCustomersController extends Controller
{
	private $class=ERPCustomers::class;
	private $utilsClass=ERPCustomersUtils::class;
    /**
     * @Route("/{_locale}/admin/global/customers", name="customers")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new $this->utilsClass();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Customers.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('customers', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'customersController',
  				'interfaceName' => 'Clientes',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
	        'forms' => $templateForms
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }


		/**
		 * @Route("/{_locale}/admin/ERP/customers/form/{id}", name="formCustomer", defaults={"id"=0})
		 */
		public function formCustomer($id,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/Customers.json";
			dump($this->getUser()->getCompany());
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('customers');
			array_push($breadcrumb, $new_breadcrumb);
			$customer=new ERPCustomers();
			$customer=$this->getDoctrine()->getRepository($this->class)->findOneById($id);
			return $this->render('@Globale/generictabform.html.twig', array(
							'controllerName' => 'CustomersController',
							'interfaceName' => 'Clientes',
							'optionSelected' => 'customers',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "caption"=>"Datos empresa", "active"=>true, "route"=>$this->generateUrl("dataCustomers",["id"=>$id])],
												 ["name" => "moredata", "caption"=>"Mas datos", "active"=>true, "route"=>$this->generateUrl("dataEntities",["id"=>$customer->getEntity()->getId()])],
												 ["name" => "contracts", "caption"=>"Contratos"]
												 //["name" => "clocks", "caption"=>"Fichajes", "route"=>$this->generateUrl("workerClocks",["id"=>$id])],
												 //["name" => "files", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"workers"])]
												]
			));


		}
		/**
		 * @Route("/{_locale}/customers/data/{id}/{action}", name="dataCustomers", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Customers.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),['activity']);
		 return $utils->make($id, $this->class, $action, "formCustomers", "modal");
		}

    /**
    * @Route("/api/global/customer/{id}/get", name="getCustomers")
    */
    public function getCustomers($id){
      $customer = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$customer) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($customer->encodeJson());
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
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, Customers::class);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/customer/{id}/disable", name="disableCustomer")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $customerUtils=new ERPCustomerUtils();
	 $result=$customerUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/customer/{id}/enable", name="enableCustomer")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $customerUtils=new ERPCustomerUtils();
	 $result=$customerUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/customer/{id}/delete", name="deleteCustomer")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $customerUtils=new ERPCustomerUtils();
	 $result=$customerUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
