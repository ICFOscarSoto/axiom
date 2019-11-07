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
use App\Modules\ERP\Entity\ERPContacts;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPContactsUtils;


class ERPContactsController extends Controller
{
	private $class=ERPContacts::class;
	private $utilsClass=ERPContactsUtils::class;

    /**
     * @Route("/{_locale}/ERP/{id}/contacts", name="contacts")
     */
    public function index($id, RouterInterface $router,Request $request)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new ERPContactsUtils;
  		$templateLists=$utils->formatListbyEntity($id);
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Contacts.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('contacts', true, $id, $this->class, "dataContacts",["id"=>$id, "action"=>"save"]);
			$entitiesrepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
			$entity=$entitiesrepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/list.html.twig', [
					'listConstructor' => $templateLists,
					'forms' => $templateForms,
					'entity_id' => $id
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }
		
	

		/**
		 * @Route("/{_locale}/contact/data/{id}/{action}/{identity}", name="dataContacts", defaults={"id"=0, "action"="read", "identity"=0})
		 */
		 public function data($id, $action, $identity, Request $request)
		 {
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Contacts.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new $this->utilsClass();
		 if($identity==0) $identity=$request->query->get('entity');
		 $defaultSupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		 $contactRepository=$this->getDoctrine()->getRepository(ERPContacts::class);
		 $obj=new $this->class();
		 if($id==0){
		 	if($identity==0 ) $identity=$request->query->get('entity');
		 	if($identity==0 || $identity==null) $identity=$request->request->get('id-parent',0);
		 	$supplier = $defaultSupplier->find($identity);
		}else $obj = $contactRepository->find($id);
		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "supplier"=>$id==0?$supplier:$obj->getSupplier()];
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
		 												method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		 return $utils->make($id, $this->class, $action, "formIdentities", "modal");
		}
		
    /**
    * @Route("/api/global/contact/{id}/get", name="getContact")
    */
    public function getContact($id){
     $contact = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$contact) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($contact->encodeJson());
    }

  /**
   * @Route("/api/contact/{id}/list", name="contactlist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
		$supplierRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$supplier = $supplierRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPContacts::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Contacts.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPContacts::class,[["column"=>"supplier", "value"=>$supplier]]);
    return new JsonResponse($return);

  }

	/**
	* @Route("/{_locale}/admin/global/contact/{id}/disable", name="disableContact")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/contact/{id}/enable", name="enableContact")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/contact/{id}/delete", name="deleteContact")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
