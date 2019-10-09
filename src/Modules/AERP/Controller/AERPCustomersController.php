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

class AERPCustomersController extends Controller
{
	private $class=AERPCustomers::class;
	private $utilsClass=AERPCustomersUtils::class;

/**
 * @Route("/{_locale}/AERP/customer/form/{id}", name="formAERPCustomer", defaults={"id"=0})
 */
public function formAERPCustomer($id,Request $request)
{
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $breadcrumb=$menurepository->formatBreadcrumb('clientes');
  $contactrRepository=$this->getDoctrine()->getRepository($this->class);
  $obj = $contactrRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
  $entity_name=$obj?$obj->getSocialName():'';
  return $this->render('@Globale/generictabform.html.twig', array(
          'entity_name' => $entity_name,
          'controllerName' => 'CustomersController',
          'interfaceName' => 'Clientes',
          'optionSelected' => $request->attributes->get('_route'),
          'menuOptions' =>  $menurepository->formatOptions($userdata),
          'breadcrumb' => $breadcrumb,
          'userData' => $userdata,
          'id' => $id,
          'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
          'tabs' => [["name" => "data", "icon"=>"fa fa-headphones", "caption"=>"Datos cliente", "active"=>true, "route"=>$this->generateUrl("dataAERPCustomers",["id"=>$id])]
                    //["name" => "addresses", "icon"=>"fa fa-headphones", "caption"=>"direcciones", "route"=>$this->generateUrl("addresses",["id"=>$id, "type"=>"contact"])]
                    //["name" => "contacts", "icon"=>"fa fa-headphones", "caption"=>"contactos" , "route"=>$this->generateUrl("contacts",["id"=>$id])],
                    //["name" => "bankaccounts", "icon"=>"fa fa-headphones", "caption"=>"Cuentas bancarias", "route"=>$this->generateUrl("bankaccounts",["id"=>$id])]
                  ],
              ));
  }

  /**
   * @Route("/{_locale}/AERP/customers/data/{id}/{action}", name="dataAERPCustomers", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/Customers.json";
   $utils = new GlobaleFormUtils();
   $obj = new $this->class();
   $defaultCountry=$this->getDoctrine()->getRepository(GlobaleCountries::class);
   $default=$defaultCountry->findOneBy(['name'=>"España"]);
   $obj->setCountry($default);
	 $obj->setAgent($this->getUser());
   $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
   $make = $utils->make($id, $this->class, $action, "formCustomers", "full", "@Globale/form.html.twig", "formCustomer");
   return $make;
  }


}
