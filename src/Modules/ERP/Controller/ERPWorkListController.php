<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleTaxes;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSalesOrdersUtils;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPVariantsValues;
use App\Modules\ERP\Entity\ERPWorkList;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\Globale\Entity\GlobaleUsersWidgets;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Security\Utils\SecurityUtils;


class ERPWorkListController extends Controller
{
	private $module='ERP';
	private $class=ERPWorkList::class;
	private $utilsClass=ERPWorkListUtils::class;

  /**
	 * @Route("/{_locale}/ERP/worklist", name="worklist", defaults={"id"=0}))
	 */
	public function index(RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if($this->getUser()!=null) $id=$this->getUser()->getId();
	//	else return $this->redirectToRoute();
		$usersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$user=$usersRepository->findOneBy(["id"=>$id]);
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);

    if($request->query->get('code',null)){
			$obj = $worklistRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'active'=>1, 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

    //Search Products
		$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-plus-circle", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    $breadcrumb=$menurepository->formatBreadcrumb('worklist');
    array_push($breadcrumb,$new_breadcrumb);

    $worklist=null;
    if($id!=0){
			$worklist=$worklistRepository->findBy(["user"=>$user, "active"=>true,"deleted"=>false]);
			//	$worklist=$worklistRepository->findByUser($id);
		}


    if($worklist==null){
			$worklist=new $this->class();
		}


		//stores
		$store_objects=$storesRepository->findBy(["active"=>1,"deleted"=>0]);
		$stores=[];
		$option=null;
		$option["id"]=null;
		$option["text"]="Selecciona Almacén...";
		$stores[]=$option;
		foreach($store_objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$stores[]=$option;
		}

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@ERP/worklist.html.twig', [
      /*  'moduleConfig' => $config,*/
        'controllerName' => 'worklistController',
        'interfaceName' => 'WorkList',
        'optionSelected' => 'genericindex',
        'optionSelectedParams' => ["module"=>"ERP", "name"=>"WorkList"],
        'menuOptions' =>  $menurepository->formatOptions($userdata),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'worklistLines' => $worklist,
        'productslist' => $productslist,
				'stores' => $stores,
        'id' => $id
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));

  }


  /**
   * @Route("/{_locale}/ERP/worklist/data/{id}", name="dataERPWorkList", defaults={"id"=0}))
   */
  public function data($id, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
    $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$variantsRepository=$this->getDoctrine()->getRepository(ERPVariantsValues::class);
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);


    $worklist=$worklistRepository->findAll(["user"=>$this->getUser()->getCompany(),"deleted"=>0]);

    //Get content of the json reques
		$data=$request->request->get('data', null); //Try getting post data (for PDAs)
		if(!$data) $data=$request->getContent();
		$fields=json_decode($data); //if no post data var, get content of header directly

		$linenumIds=[];
		$products=[];
    foreach ($fields->lines as $key => $value) {
			$variant=null;
			if($value->code!=null){
	      $product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$value->code, "deleted"=>0]);
				if(isset($value->variant) AND $value->variant!="-1"){
					$variant=$variantsRepository->findOneBy(["name"=>$value->variant]);
					$line=$worklistRepository->findOneBy(["product"=>$product,"user"=>$this->getUser(),"variant"=>$variant,"deleted"=>0]);
				}
				else{
	      	$line=$worklistRepository->findOneBy(["product"=>$product,"user"=>$this->getUser(),"deleted"=>0]);
				}

	      //if(!$product) continue;
	      if(!$line ){
	        $line=new ERPWorkList();
	        $line->setUser($this->getUser());
	        $line->setActive(1);
	        $line->setDeleted(0);
	        $line->setDateadd(new \DateTime());
	      }
			  	$line->setLinenum($value->linenum);
	        $line->setProduct($product);
	        $line->setCode($value->code);
	        $line->setName($value->name);
	        $line->setQuantity(floatval($value->quantity));
				//	dump($value->variant);
					if(isset($value->variant) AND $value->variant!="-1"){
						 $variant=$variantsRepository->findOneBy(["name"=>$value->variant]);
						  $line->setVariant($variant);
					 }
					 if(isset($value->store)){
							$store=$storesRepository->findOneBy(["id"=>$value->store]);
							 $line->setStore($store);
						}
					if(isset($value->location)){
							 $location=$storeLocationsRepository->findOneBy(["id"=>$value->location]);
								$line->setLocation($location);
						}
	        if($value->deleted){
	          $line->setActive(0);
	          $line->setDeleted(1);
	        }
	        $line->setDateupd(new \DateTime());
	        $this->getDoctrine()->getManager()->persist($line);
	        $this->getDoctrine()->getManager()->flush();
					$linenumIds[]=["linenum"=>$value->linenum, "id"=>$line->getId()];
					$product_item=[];
					$product_item["id"]=$line->getId();
					$product_item["id_product"]=$product->getId();
					$product_item["code"]=$product->getCode();
					$product_item["name"]=$product->getName();
					$product_item["variant_id"]=$variant?$variant->getId():0;
					$product_item["variant_name"]=$variant?$variant->getVariantname()->getName():"";
					$product_item["variant_value"]=$variant?$variant->getName():"";
					$product_item["variant_active"]=$variant?$variant->getActive():true;
					$product_item["stock"]=$line->getQuantity();
					$product_item["provider"]=$product->getSupplier()?$product->getSupplier()->getName():"";
					$product_item["eans"]=[];
					$product_item["active"]=$product->getActive();
					$products[]=$product_item;
			}
    }
    return new JsonResponse(["result"=>1,"data"=>["id"=>$this->getUser()->getId(), "lines"=>$linenumIds],"products"=>$products]);
    //return new JsonResponse(["result"=>1]);
  }

	/**
   * @Route("/{_locale}/ERP/worklist/empty/{id}", name="dataERPEmptyWorkList", defaults={"id"=0}))
   */
  public function empty($id, RouterInterface $router,Request $request){
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
			$worklistRepository->emptyList($id);
			return new JsonResponse(["result"=>1]);
	}

	/**
 * @Route("/api/ERP/worklist/locations/{store}/{product}/{variant}/get", name="getProductLocations", defaults={"variant"=0}))
 */
 public function getProductLocations($store, $product, $variant, RouterInterface $router,Request $request){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
  $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
	$variantsRepository=$this->getDoctrine()->getRepository(ERPVariantsValues::class);
	$product_object=$productsRepository->findOneBy(["code"=>$product]);
	if($variant!=0 AND $variant!="-1")
	{
		$variant_object=$variantsRepository->findOneBy(["name"=>$variant]);
		$storeLocations=$stocksRepository->findLocationsByStoreProduct($store, $product_object->getId(),$variant_object->getId());

	}
	else {
		$storeLocations=$stocksRepository->findLocationsByStoreProduct($store, $product_object->getId(),null);

	}

	$response=Array();

	foreach($storeLocations as $location){
		$item['id']=$location['id'];
		$item['name']=$location['name'];
		$response[]=$item;
	}

	return new JsonResponse(["locations"=>$response]);

 }

 /**
* @Route("/api/ERP/worklist/stores/get", name="getWorkListStores")
*/
public function getWorkListStores(RouterInterface $router,Request $request){
 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 $storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
 $stores=$storesRepository->getStoresInfo();

 $response=Array();

 foreach($stores as $store){
	 $item['id']=$store['id'];
	 $item['name']=$store['name'];
	 $response[]=$item;
 }

 return new JsonResponse(["stores"=>$response]);

}

/**
* @Route("/api/erp/worklist/getproducts", name="getWorkListProducts")
*/
public function getWorkListProducts(Request $request){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$EAN13repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
	$Productrepository=$this->getDoctrine()->getRepository(ERPProducts::class);
	$Variantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
	$Stocksrepository=$this->getDoctrine()->getRepository(ERPStocks::class);
	//$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
	$StoreLocationsrepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
	$Storesrepository=$this->getDoctrine()->getRepository(ERPStores::class);
	$worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
	$obj=null;
	$variant=null;

	$products=$worklistRepository->findBy(["user"=>$this->getUser(),"deleted"=>0]);
	$array_products=[];
	foreach($products as $item){
		$obj=$item->getProduct();
		$variant=$item->getVariant();
		//$stocks=$Stocksrepository->findBy(["product"=>$obj, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		$eans=$EAN13repository->findBy(["product"=>$obj, "productvariant"=>$variant?$variant:null, "active"=>1, "deleted"=>0]);
		$result_prod["id"]=$item->getId();
		$result_prod["id_product"]=$obj->getId();
		$result_prod["code"]=$obj->getCode();
		$result_prod["variant_id"]=$variant?$variant->getId():0;
		$result_prod["variant_name"]=$variant?$variant->getVariantname()->getName():"";
		$result_prod["variant_value"]=$variant?$variant->getName():"";
		$result_prod["variant_active"]=$variant?$variant->getActive():true;
		$result_prod["stock"]=$item->getQuantity();
		$result_prod["code"]=$obj->getCode();
		$result_prod["name"]=$obj->getName();
		$result_prod["provider"]=$obj->getSupplier()?$obj->getSupplier()->getName():"";
		$result_prod["eans"]=[];
		foreach($eans as $ean){
			$ean_item["id"]=$ean->getId();
			$ean_item["barcode"]=$ean->getName();
			$ean_item["type"]=$ean->getType()==null?0:$ean->getType();
			if($ean->getSupplier()){
				$ean_item["supplierId"]=$ean->getSupplier()->getId();
				$ean_item["supplierName"]=$ean->getSupplier()->getName();
			}else{
				$ean_item["supplierId"]=0;
				$ean_item["supplierName"]='';
			}
			if($ean->getCustomer()){
				$ean_item["customerId"]=$ean->getCustomer()->getId();
				$ean_item["customerName"]=$ean->getCustomer()->getName();
			}else{
				$ean_item["customerId"]=0;
				$ean_item["customerName"]='';
			}
			$result_prod["eans"][]=$ean_item;
		}

		$result_prod["active"]=$obj->getActive();
		$array_products[]=$result_prod;
 }
 $result["products"]=$array_products;

	return new JsonResponse($result);
}


/**
* @Route("/api/erp/worklist/qtylinechange/{id}/{qty}", name="qtyLineChange", defaults={"qty"=1})
*/
public function qtyLineChange($id, $qty, Request $request){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
	$line=$worklistRepository->findOneBy(["id"=>$id,"user"=>$this->getUser(),"deleted"=>0]);
	if(!$line) return new JsonResponse(["result"=>-1, "text"=> "Linea no encontrada"]);
	$line->setQuantity($qty);
	$line->setDateupd(new \DateTime());
	$this->getDoctrine()->getManager()->persist($line);
	$this->getDoctrine()->getManager()->flush();
	return new JsonResponse(["result"=>1]);
}

/**
* @Route("/api/erp/worklist/removeline/{id}", name="removeLine")
*/
public function removeLine($id, Request $request){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
	$line=$worklistRepository->findOneBy(["id"=>$id,"user"=>$this->getUser(),"deleted"=>0]);
	if(!$line) return new JsonResponse(["result"=>-1, "text"=> "Linea no encontrada"]);
	$line->setDeleted(1);
	$line->setActive(0);
	$line->setDateupd(new \DateTime());
	$this->getDoctrine()->getManager()->persist($line);
	$this->getDoctrine()->getManager()->flush();
	return new JsonResponse(["result"=>1]);
}


}
