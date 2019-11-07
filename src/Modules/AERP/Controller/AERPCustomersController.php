<?php

namespace App\Modules\AERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\AERP\Entity\AERPCustomers;
use App\Modules\AERP\Entity\AERPCustomerGroups;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\AERP\Utils\AERPCustomersUtils;
use App\Modules\Security\Utils\SecurityUtils;

class AERPCustomersController extends Controller
{
	private $module='AERP';
	private $class=AERPCustomers::class;
	private $utilsClass=AERPCustomersUtils::class;

/**
 * @Route("/{_locale}/AERP/customer/form/{id}", name="formAERPCustomer", defaults={"id"=0})
 */
public function formAERPCustomer($id,Request $request)
{
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  $locale = $request->getLocale();
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $contactRepository=$this->getDoctrine()->getRepository($this->class);
  $obj = $contactRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
  $entity_name=$obj?$obj->getName():'';
	$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
	$breadcrumb=$menurepository->formatBreadcrumb('genericindex','AERP','Customers');
	array_push($breadcrumb,$new_breadcrumb);
  return $this->render('@Globale/generictabform.html.twig', array(
          'entity_name' => $entity_name,
          'controllerName' => 'CustomersController',
          'interfaceName' => 'Clientes',
					'optionSelected' => 'genericindex',
			    'optionSelectedParams' => ["module"=>"AERP", "name"=>"Customers"],
          'menuOptions' =>  $menurepository->formatOptions($userdata),
          'breadcrumb' => $breadcrumb,
          'userData' => $userdata,
          'id' => $id,
          'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
          'tabs' => [	["name" => "data", "icon"=>"fa-address-card-o", "caption"=>"Datos cliente", "active"=>true, "route"=>$this->generateUrl("dataAERPCustomers",["id"=>$id])],
											["name"=>"customercontacts", "icon"=>"fa fa-users", "caption"=>"Contactos","route"=>$this->generateUrl("generictablist",["module"=>"AERP", "name"=>"CustomerContacts", "id"=>$id])],
											["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"customers"])]
                  	],
              ));
  }

  /**
   * @Route("/{_locale}/AERP/customers/data/{id}/{action}", name="dataAERPCustomers", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $template=dirname(__FILE__)."/../Forms/Customers.json";
   $utils = new GlobaleFormUtils();
	 $repository=$this->getDoctrine()->getRepository($this->class);
	 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 if($id!=0 && $obj==null){
			 return $this->render('@Globale/notfound.html.twig',[]);
	 }
	 /*if($obj==null){
		 $obj=new $this->class();
		 $defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
	   $default=$defaultCountry->findOneBy(['name'=>"España"]);
	   $obj->setCountry($default);
		 $obj->setAgentassign($this->getUser());
	 }*/
	 $classUtils=new AERPCustomersUtils();
	 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
   $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),$classUtils->getExcludedForm($params),$classUtils->getIncludedForm($params));
   $make = $utils->make($id, $this->class, $action, "formCustomers", "full", "@Globale/form.html.twig", "formAERPCustomer");
   return $make;
  }


}
