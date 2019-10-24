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
use App\Modules\AERP\Entity\AERPCustomerGroups;
use App\Modules\AERP\Entity\AERPCustomerGroupsPrices;
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
											["name" => "files", "icon"=>"fa fa-money", "caption"=>"Precios grupos", "route"=>$this->generateUrl("AERPProductsPrices",["id"=>$id])],
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

	/**
   * @Route("/{_locale}/AERP/products/{id}/prices", name="AERPProductsPrices")
   */
   public function AERPProductsPrices($id, Request $request){
	   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $repository=$this->getDoctrine()->getRepository(AERPProducts::class);
		 $repositoryGroups=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		 $repositoryCustomerGroupsPrices=$this->getDoctrine()->getRepository(AERPCustomerGroupsPrices::class);
		 $groups=$repositoryGroups->findBy(["company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 $product=$repository->findOneBy(["id"=>$id,"company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

		 if(!$product) return $this->render('@Globale/notfound.html.twig',[]);
		 $groupsArray=[];
		 foreach($groups as $group){
			 $price=$repositoryCustomerGroupsPrices->findOneBy(["company"=>$this->getUser()->getCompany(), "product"=>$product, "customergroup"=>$group, "active"=>1, "deleted"=>0]);
			 if($price) $groupsArray[$group->getName()]=["id"=>$group->getId(), "discount"=>$price->getDisccount(), "profit"=>$price->getProfit(), "fixed"=>$price->getFixed(), "total"=>$price->getTotal()];
			 else $groupsArray[$group->getName()]=["id"=>$group->getId(), "discount"=>"", "profit"=>"", "fixed"=>"", "total"=>$product->getPrice()];
		 }
	   return $this->render('@AERP/product_prices.html.twig',[
			 'id'=>$id,
			 'groups'=>$groupsArray,
			 'product'=>$product
		 ]);
  }

	/**
   * @Route("/{_locale}/AERP/products/{id}/setGroupPrices", name="productSetGroupPrices")
   */
   public function productSetGroupPrices($id, Request $request){
	   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $repository=$this->getDoctrine()->getRepository(AERPProducts::class);
		 $repositoryGroups=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		 $repositoryCustomerGroupsPrices=$this->getDoctrine()->getRepository(AERPCustomerGroupsPrices::class);
		 $product=$repository->findOneBy(["id"=>$id,"company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 if(!$product) new JsonResponse(["result"=>-1]);
		 $prices=json_decode($request->getContent());
		 foreach($prices as $price){
			 $group=$repositoryGroups->findOneBy(["id"=>$price->group, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			 if(!$group) continue;
			 $groupprice=$repositoryCustomerGroupsPrices->findOneBy(["company"=>$this->getUser()->getCompany(), "product"=>$product, "customergroup"=>$group, "active"=>1, "deleted"=>0]);
			 $total=null;
			 if($price->fixed!="") $total=$price->fixed;
			 	else if($price->discount!=""){
					$total=$product->getPrice()-($product->getPrice()*$price->discount/100);
				}else if($price->profit!=""){
					$total=$product->getPurchasePrice()+($product->getPurchasePrice()*$price->profit/100);
				}
				if($total===null) continue;
				if($price!=null){
					 $groupprice = new AERPCustomerGroupsPrices();
					 $groupprice->setCompany($this->getUser()->getCompany());
					 $groupprice->setCustomergroup($group);
					 $groupprice->setProduct($product);
					 $groupprice->setActive(1);
					 $groupprice->setDeleted(0);
					 $groupprice->setDateadd(new \DateTime());
				}
				$groupprice->setDisccount($price->discount);
				$groupprice->setProfit($price->profit);
				$groupprice->setFixed($price->fixed);
				$groupprice->setTotal(round($total,2));
				$groupprice->setDateupd(new \DateTime());
				$this->getDoctrine()->getManager()->persist($groupprice);
				$this->getDoctrine()->getManager()->flush();
		 }
	   return new JsonResponse(["result"=>1]);
  }

	/**
	 * @Route("/api/AERP/product/search/{field}/{query}/{group}", name="productsearch", defaults={"query"="", "group"=0})
	 */
	 public function genericsearch($field, $group, $query, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $fields=json_decode($request->getContent());
		 $user = $this->getUser();
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository(AERPProducts::class);
		 if(!property_exists($this->class, $field)) return new JsonResponse(["result"=>0]);
		 $obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), $field => $query]);
		 if(!$obj) return new JsonResponse(["result"=>0]);
		 $result=[];
		 foreach($fields as $field){
			 //TODO: Check if user has permissions in this fields
			 if(method_exists($obj, "get".ucfirst($field))){
				 $result[$field]=$obj->{"get".ucfirst($field)}();
				 if(is_object($result[$field])) $result[$field]=$result[$field]->getId();
			 }
		 }
		 //get product price
		 $result["price"]=$repository->getProductPrice($obj->getId(), $group, $this->getUser()->getCompany()->getId());
		 $result["tax"]=$obj->getTax()?$obj->getTax()->getTax():0;
		 $result["surcharge"]=$obj->getTax()?$obj->getTax()->getSurcharge():0;
		 return new JsonResponse(["result"=>1, "data"=>$result]);
	 }


}
