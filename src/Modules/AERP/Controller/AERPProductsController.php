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
use App\Modules\AERP\Entity\AERPProducts;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\AERP\Utils\AERPProductsUtils;
use App\Modules\Security\Utils\SecurityUtils;

class AERPProductsController extends Controller
{
	private $module='AERP';
	private $class=AERPProducts::class;
	private $utilsClass=AERPProductsUtils::class;

/**
 * @Route("/{_locale}/AERP/product/form/{id}", name="formAERPProduct", defaults={"id"=0})
 */
public function formAERPProduct($id,Request $request)
{
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $repository=$this->getDoctrine()->getRepository($this->class);
  $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
  $entity_name=$obj?$obj->getName():'';
	$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
	$breadcrumb=$menurepository->formatBreadcrumb('genericindex','AERP','Products');
	array_push($breadcrumb,$new_breadcrumb);
  return $this->render('@Globale/generictabform.html.twig', array(
          'entity_name' => $entity_name,
          'controllerName' => 'ProductsController',
          'interfaceName' => 'Productos',
					'optionSelected' => 'genericindex',
			    'optionSelectedParams' => ["module"=>"AERP", "name"=>"Products"],
          'menuOptions' =>  $menurepository->formatOptions($userdata),
          'breadcrumb' => $breadcrumb,
          'userData' => $userdata,
          'id' => $id,
          'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
          'tabs' => [	["name" => "data", "icon"=>"fa-address-card-o", "caption"=>"Datos productos", "active"=>true, "route"=>$this->generateUrl("dataAERPProducts",["id"=>$id])],
											["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]
                  	],
              ));
  }

  /**
   * @Route("/{_locale}/AERP/products/data/{id}/{action}", name="dataAERPProducts", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $template=dirname(__FILE__)."/../Forms/Products.json";
   $utils = new GlobaleFormUtils();
	 $repository=$this->getDoctrine()->getRepository($this->class);
	 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 if($id!=0 && $obj==null){
			 return $this->render('@Globale/notfound.html.twig',[]);
	 }
	 $classUtils=new AERPProductsUtils();
	 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
   $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),$classUtils->getExcludedForm($params),$classUtils->getIncludedForm($params));
   $make = $utils->make($id, $this->class, $action, "formProducts", "full", "@Globale/form.html.twig", "formAERPProduct");
   return $make;
  }


}
