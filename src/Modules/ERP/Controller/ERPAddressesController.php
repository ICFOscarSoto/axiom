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
use App\Modules\ERP\Entity\ERPAddresses;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPAddressesUtils;


class ERPAddressesController extends Controller
{
	private $class=ERPAddresses::class;
	private $utilsClass=ERPAddressesUtils::class;

    /**
     * @Route("/{_locale}/ERP/{id}/{type}/addresses", name="addresses")
     */
    public function index($id, $type, RouterInterface $router,Request $request)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new ERPAddressesUtils;
  		$templateLists=$utils->formatListbyEntity($id);
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Addresses.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('addresses', true, $id, $this->class, "dataAddresses",["id"=>$id, "type"=>$type, "action"=>"save"]);
			$entitiesrepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
			$entity=$entitiesrepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/list.html.twig', [
					'listConstructor' => $templateLists,
					'forms' => $templateForms,
					'entity_id' => $id,
					'type_entity' =>$type
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/address/data/{id}/{action}/{type}/{identity}", name="dataAddresses", defaults={"id"=0, "action"="read", "identity"=0, "type"="supplier"})
		 */
		 public function data($id, $action, $type, $identity, Request $request)
		 {
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Addresses.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new $this->utilsClass();
		 $defaultSupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $defaultCustomer=$this->getDoctrine()->getRepository(ERPCustomers::class);
		 $addressRepository=$this->getDoctrine()->getRepository(ERPAddresses::class);
		 $obj=new $this->class();
		 if($id==0){
		 	if($identity==0 ) $identity=$request->query->get('entity');
		 	if($identity==0 || $identity==null) $identity=$request->request->get('id-parent',0);
		 	if($type=="supplier") $object = $defaultSupplier->find($identity);
			if($type=="customer") $object = $defaultCustomer->find($identity);
		}else $obj = $addressRepository->find($id);

			dump($object);
			$defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
			$country=$defaultCountry->findOneBy(['name'=>"España"]);
			$obj->setCountry($country);

		 /*$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "supplier"=>$id==0?$supplier:$obj->getSupplier()];
		 ,method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]
		 */

		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),["supplier", "customer"]);
		 $utils->preParams=["type"=>$type, "obj"=>$object];
		 return $utils->make($id, $this->class, $action, "formIdentities", "modal");
		}

    /**
    * @Route("/api/global/address/{id}/get", name="getAddress")
    */
    public function getAddress($id){
     $address = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$address) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($address->encodeJson());
    }

  /**
   * @Route("/api/address/{id}/list", name="addresslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
		$supplierRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$supplier = $supplierRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPAddresses::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Addresses.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPAddresses::class,[["type"=>"and", "column"=>"supplier", "value"=>$supplier]]);
    return new JsonResponse($return);

  }

	/**
	* @Route("/{_locale}/admin/global/address/{id}/disable", name="disableAddress")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/address/{id}/enable", name="enableAddress")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/address/{id}/delete", name="deleteAddress")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
