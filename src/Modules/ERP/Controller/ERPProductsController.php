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
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProductsAttributes;
use App\Modules\ERP\Entity\ERPVariantsTypes;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPProductsSuppliersDiscounts;
use App\Modules\ERP\Entity\ERPProductsSuppliersPrices;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\ERP\Entity\ERPStoresManagersUsersStores;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPProductsVariantsUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Reports\ERPPrintQR;
use App\Modules\ERP\Utils\ERPPrestashopUtils;
use App\Modules\Navision\Entity\NavisionTransfers;

class ERPProductsController extends Controller
{
	private $class=ERPProducts::class;
	private $utilsClass=ERPPrestashopUtils::class;
	private $module='ERP';
  private $url="http://192.168.1.250:9000/";
	//private $utilsClass=ERPProductsUtils::class;
    /**
     * @Route("/{_locale}/admin/global/products", name="products")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new ERPProductsUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
			//$formUtils=new GlobaleFormUtils();
			//$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Products.json", $request, $this, $this->getDoctrine());
			//$templateForms[]=$formUtils->formatForm('products', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'productsController',
  				'interfaceName' => 'Productos',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
					'include_pre_list_templates' => ['@ERP/product_barcode_search.html.twig'],
					'include_post_templates' => ['@ERP/categoriesmap.html.twig','@ERP/productlistcategories.html.twig'],
					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
															 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/products/navcategory", name="navcategory")
		 */
		public function getCategoryNav(){
			$json=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-getCategoryNavs.php');
      $objects=json_decode($json, true);
      $objects=$objects[0]["class"];
			return new JsonResponse($objects);
		}

		/**
		 * @Route("/{_locale}/products/navfamily/{category}", name="navfamily")
		 */
		public function getFamilyNav($category){
			$json=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-getFamilyNavs.php?category='.$category);
			$objects=json_decode($json, true);
			$objects=$objects[0]["class"];
			return new JsonResponse($objects);
		}

		/**
		 * @Route("/{_locale}/products/data/{id}/{action}", name="dataProduct", defaults={"id"=0, "action"="read"})
		 */
		 public function dataProduct($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/Products.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new ERPProductsUtils();
		 $manufacturerRepository= $this->getDoctrine()->getRepository(ERPManufacturers::class);
		 $manufacturer=$manufacturerRepository->findOneBy(["name"=>"Prueba"]);
		 //$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 $repository=$this->getDoctrine()->getRepository($this->class);
		 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
		 if($id!=0 && $obj==null){
		 		return $this->render('@Globale/notfound.html.twig',[]);
		 }
		 $classUtils=new ERPProductsUtils();
		 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
		 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),$classUtils->getExcludedForm($params),$classUtils->getIncludedForm($params));
		 $make = $utils->make($id, $this->class, $action, "formProduct", "full", "@Globale/form.html.twig", "formProduct");
		 return $make;
		}


		/**
		 * @Route("/{_locale}/admin/global/product/form/{id}", name="formProduct", defaults={"id"=0})
		 */
		 public function formProduct($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/Products.json";
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('products');
			array_push($breadcrumb, $new_breadcrumb);
			$productRepository=$this->getDoctrine()->getRepository($this->class);

			if($request->query->get('code',null)){
				$obj = $productRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
				if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
				else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
			}

			$tabs=[["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Products data", "active"=>true, "route"=>$this->generateUrl("dataProduct",["id"=>$id])]];
			$obj = $productRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$product_name=$obj?$obj->getName():'';
			if ($obj && $obj->getGrouped()) {
				$tabs[]=["name" => "variants", "icon"=>"fa fa-id-card", "caption"=>"Variants", "route"=>$this->generateUrl("listProductsVariants",["id"=>$id])];
			}
			$tabs=array_merge($tabs,[["name" => "ean13",  "icon"=>"fa fa-users", "caption"=>"EAN13", "route"=>$this->generateUrl("listEAN13",["id"=>$id])],
			["name" => "references",  "icon"=>"fa fa-users", "caption"=>"References", "route"=>$this->generateUrl("listReferences",["id"=>$id])],
			["name"=>  "rates", "icon"=>"fa fa-money", "caption"=>"Rates","route"=>$this->generateUrl("infoProductRates",["id"=>$id])],
			["name"=>  "productPrices", "icon"=>"fa fa-money", "caption"=>"Prices","route"=>$this->generateUrl("infoProductPrices",["id"=>$id])],
			["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("istock",["id"=>$id])],
			["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]]);
			if ($obj && $obj->getCheckweb()) {
				$tabs[]=["name" => "c", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("infoWebProducts",["id"=>$id])];
			//	$tabs[]=["name" => "webproduct", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("generictablist",["function"=>"formatListByProduct","module"=>"ERP","name"=>"WebProducts","id"=>$id])];
		}
				return $this->render('@Globale/generictabform.html.twig', array(
									'entity_name' => $product_name,
									'controllerName' => 'ProductsController',
									'interfaceName' => 'Productos',
									'optionSelected' => 'products',
									'menuOptions' =>  $menurepository->formatOptions($userdata),
									'breadcrumb' => $breadcrumb,
									'userData' => $userdata,
									'id' => $id,
									'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
									'tabs' => $tabs,
									'include_tab_post_templates' => ['@ERP/categoriesmap.html.twig', '@ERP/categoriesmapproduct.html.twig', '@ERP/triggerwebproduct.html.twig'],
									'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																			["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
									'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
												 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
																			 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
					));


	}

		/**
		 * @Route("/{_locale}/products/info/{id}", name="formInfoProduct", defaults={"id"=0})
		 */
		public function formInfoProduct($id,  Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('products');
			array_push($breadcrumb, $new_breadcrumb);
			$template=dirname(__FILE__)."/../Forms/Products.json";
			$formUtils = new GlobaleFormUtils();
			$formUtilsProducts = new ERPProductsUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsProducts->getExcludedForm([]),$formUtilsProducts->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$id]));

			return $this->render('@ERP/productform.html.twig', array(
				'controllerName' => 'productsController',
				'interfaceName' => 'Productos',
				'optionSelected' => 'products',
				'userData' => $userdata,
				'id' => $id,
				'id_object' => $id,
				'form' => $formUtils->formatForm('products', false, $id, $this->class, "dataProduct")
			));

		}

		/**
    * @Route("/api/erp/product/search", name="searchProduct")
    */
    public function searchProduct(Request $request){
			$search=$request->request->get('s',null);
			$Productrepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$result=$Productrepository->searchProduct($search);
			return new JsonResponse($result);
		}

    /**
    * @Route("/api/erp/product/get/{id}", name="getProduct", defaults={"id"=0})
    */
    public function getProduct($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$EAN13repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
			$Productrepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$ProductsVariantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$Stocksrepository=$this->getDoctrine()->getRepository(ERPStocks::class);
			$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$StoreLocationsrepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			$Storesrepository=$this->getDoctrine()->getRepository(ERPStores::class);
			$obj=null;
			$productvariant=null;
			if($id!=0){
				$obj = $this->getDoctrine()->getRepository($this->class)->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				$productvariant=$ProductsVariantsrepository->findOneBy(["product"=>$obj, "variant"=>null, "deleted"=>0]);
			}else{
				if($request->request->get('barcode',null)){
						// Busqueda de producto por su ID de base de datos (P.)
						if(substr(strtoupper($request->request->get('barcode')),0,2)=="P."){
							$product=$Productrepository->findOneBy(["id"=>intval(substr($request->request->get('barcode'),2)), "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
							$obj=$product;
							$productvariant=$ProductsVariantsrepository->findOneBy(["product"=>$product, "variant"=>null, "deleted"=>0]);
						}else{
							// Busqueda de producto por su ID de productvariant de base de datos (V.)
							if(substr(strtoupper($request->request->get('barcode')),0,2)=="V."){
								$productvariant=$ProductsVariantsrepository->findOneBy(["id"=>intval(substr($request->request->get('barcode'),2)), "deleted"=>0]);
								if($productvariant) $obj=$productvariant->getProduct();
							}else{
								$EAN13=$EAN13repository->findOneBy(["name"=>$request->request->get('barcode',null), "deleted"=>0]);
								if($EAN13){
									$productvariant=$EAN13->getProductVariant();
									if ($productvariant)
										$obj=$EAN13->getProductVariant()->getProduct();
								}else{
									//Try with a lead 0 at start of $barcode
									$EAN13=$EAN13repository->findOneBy(["name"=>'0'.$request->request->get('barcode',null), "deleted"=>0]);
									if($EAN13){
										$productvariant=$EAN13->getProductVariant();
										if ($productvariant)
											$obj=$EAN13->getProductVariant()->getProduct();
									}
								}
							}
						}

				}
			}
			if($obj){
				$stocks=$Stocksrepository->findBy(["productvariant"=>$productvariant, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				$eans=$EAN13repository->findBy(["productvariant"=>$productvariant,  "active"=>1, "deleted"=>0]);

				$result["id"]=$obj->getId();
				$result["code"]=$obj->getCode();
				$result["variant_id"]=$productvariant?($productvariant->getVariant()?$productvariant->getId():0):0;
				$result["variant_name"]=$productvariant?($productvariant->getVariant()?($productvariant->getVariant()->getVarianttype()?$productvariant->getVariant()->getVarianttype()->getName():""):""):"";
				$result["variant_value"]=$productvariant?($productvariant->getVariant()?$productvariant->getVariant()->getName():""):"";
				$result["variant_active"]=$productvariant?$productvariant->getActive():true;

				$result["code"]=$obj->getCode();
				$result["name"]=$obj->getName();
				$result["provider"]=$obj->getSupplier()?$obj->getSupplier()->getName():"";
				$result["eans"]=[];
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
					$result["eans"][]=$ean_item;
				}

				$productsvariants=$ProductsVariantsrepository->findBy(["product"=>$obj, "active"=>1, "deleted"=>0]);
				$result["variants"]=[];
				foreach($productsvariants as $productvariant){
					if (!$productvariant->getVariant()) continue;
					$variant_item["id"]=$productvariant?($productvariant->getVariant()?$productvariant->getId():0):0;
					$variant_item["name"]=$productvariant?($productvariant->getVariant()?($productvariant->getVariant()->getVarianttype()?$productvariant->getVariant()->getVarianttype()->getName():""):""):"";
					$variant_item["value"]=$productvariant?($productvariant->getVariant()?$productvariant->getVariant()->getName():""):"";
					$eans=$EAN13repository->findBy(["productvariant"=>$productvariant, "active"=>1, "deleted"=>0]);
					$variant_item["eans"]=[];
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
						$variant_item["eans"][]=$ean_item;
					}

					$result["variants"][]=$variant_item;
				}
				usort($result["variants"], function($a, $b) {
						return $a['value'] <=> $b['value'];
				});

				$stock_items=[];
				foreach($stocks as $stock){
					$storeUser=$StoreUsersrepository->findOneBy(["user"=>$this->getUser(), "store"=>$stock->getStorelocation()->getStore(), "active"=>1, "deleted"=>0]);
					if($storeUser){
						$stock_item["id"]=$stock->getId();
						$stock_item["variant_id"]=!$stock->getProductVariant()?0:$stock->getProductVariant()->getId();
						$stock_item["warehouse_code"]=$stock->getStorelocation()->getStore()->getCode();
						$stock_item["warehouse"]=$stock->getStorelocation()->getStore()->getName();
						$stock_item["warehouse_id"]=$stock->getStorelocation()->getStore()->getId();
						$stock_item["warehouse_preferential"]=$storeUser->getPreferential();
						$stock_item["location"]=$stock->getStorelocation()->getName();
						$stock_item["location_id"]=$stock->getStorelocation()->getId();
						$stock_item["quantity"]=!$stock->getQuantity()?0:$stock->getQuantity();
						$stock_item["pendingserve"]=!$stock->getPendingserve()?0:$stock->getPendingserve();
						$stock_item["pendingreceive"]=!$stock->getPendingreceive()?0:$stock->getPendingreceive();
						$stock_item["minstock"]=!$stock->getMinstock()?0:$stock->getMinstock();
						$stock_items[]=$stock_item;
					}
				}
				usort($stock_items, function($a, $b) {
				    return $a['warehouse_id'] <=> $b['warehouse_id'];
				});
				$result["stock"]=$stock_items;
				$result["web"]=$obj->getCheckweb()!==null?$obj->getCheckweb():false;
				$result["active"]=$obj->getActive();

				return new JsonResponse($result);
			}
			return new JsonResponse(["result"=>-1]);
    }



		/**
    * @Route("/api/erp/product/getdescription/{id}", name="getProductDescription", defaults={"id"=0})
    */
    public function getProductDescription($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$EAN13repository=$this->getDoctrine()->getRepository(ERPEAN13::class);

			$obj=null;
			if($id!=0){
				$obj = $this->getDoctrine()->getRepository($this->class)->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			}else{
				if($request->request->get('barcode',null)){
						$EAN13=$EAN13repository->findOneBy(["name"=>$request->request->get('barcode',null), "active"=>1, "deleted"=>0]);
						if($EAN13) $obj=$EAN13->getProductvariant()->getProduct();
				}
			}
			if($obj){

				$result["id"]=$obj->getId();
				$result["description"]=$obj->getDescription();
				return new JsonResponse($result);
			}
			return new JsonResponse(["result"=>-1]);
    }

		/**
		* @Route("/api/erp/product/uploadwebimages/{id}", name="uploadWebImages", defaults={"id"=0})
		*/
		public function uploadWebImages($id,Request $request){
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
				$product=$productRepository->findOneBy(["id"=>$id]);

				$rootDir=$this->get('kernel')->getRootDir();
				$prestashopUtils= new ERPPrestashopUtils();
				$return=$prestashopUtils->uploadProductImages($product,$rootDir);

				if($return) return new JsonResponse(["result"=>1]);
				else return new JsonResponse(NULL);
		}



		/**
		* @Route("/api/prestashop/erp/product/get/{id}", name="prestashopGetProduct", defaults={"id"=0})
		*/
		public function prestashopGetProduct($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
			$context = stream_context_create([
			    "http" => ["header" => "Authorization: Basic $auth"]
			]);
			$array=[];
			try{
				$xml_string=file_get_contents("https://ferreteriacampollano.com/api/products/?filter[reference]=".$id, false, $context);
				$xml = simplexml_load_string($xml_string);
				$json = json_encode($xml);
				$array = json_decode($json,TRUE);
			}catch(Exception $e){}
			if(isset($array["products"]["product"]["@attributes"]["id"])){
				$idpresta=$array["products"]["product"]["@attributes"]["id"];
				//GET PRODUCT INFO
				try{
					$xml_string=file_get_contents("https://ferreteriacampollano.com/api/products/".$idpresta, false, $context);
					$xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
					$json = json_encode($xml);
					$array = json_decode($json,TRUE);
					$description=$array["product"]["description"]["language"];
					$product=$productRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$id, "deleted"=>0]);
					if($product){
						$product->setDescription($description);
						$this->getDoctrine()->getManager()->persist($product);
						$this->getDoctrine()->getManager()->flush();
						return new JsonResponse(["result"=>1]);
					}

				 }catch(Exception $e){}

			}
			return new JsonResponse(["result"=>-1]);
		}


  /**
   * @Route("/{_locale}/admin/global/product/list", name="productlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Products.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]],[],null,"id",$this->getDoctrine());
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/product/{id}/disable", name="disableProduct")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/product/{id}/enable", name="enableProduct")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/product/{id}/delete", name="deleteProduct")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

 /**
 * @Route("/{_locale}/admin/ERP/product/printLabel/{id}/{type}", name="printeanlabel", defaults={"type"=1})
 */
 public function printeanlabel($id, $type){
	 $repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
	 $repositoryProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
	 $repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
	 $code="";
	 $barcode="0000000000000";
	 $name="";
	 $noValidate=false;
	 if($type==1){
		 $ean=$repository->findOneBy(["id"=>$id]);
		 if($ean){
			 $code=$ean->getProductvariant()->getProduct()->getCode();
			 $barcode=$ean->getName();
			 $name=$ean->getProductvariant()->getProduct()->getName();
			 if ($ean->getCustomer())
			  	if ($ean->getCustomer()->getCode()=='C01448') $noValidate=true;
					else $noValidate=false;
			else $noValidate=false;
		 }
		 $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser(), "noValidate"=>$noValidate];
 	 }else if($type==2){
		 $product=$repositoryProduct->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
	 		if($product){
	 			$code=$product->getCode();
	 			$barcode='P.'.str_pad($product->getId(),8,'0', STR_PAD_LEFT);
	 			$name=$product->getName();
			}
			$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser(), "noValidate"=>$noValidate];
	 }
	 $reportsUtils = new ERPEan13Reports();
	 $pdf=$reportsUtils->create($params);
	 return new Response("", 200, array('Content-Type' => 'application/pdf'));
 }

 /**
 * @Route("/{_locale}/admin/ERP/product/printLabel/{id}/{printer}/{copies}/{type}", name="printDirectly", defaults={"copies"=1,"type"=1})
 */
 public function printDirectly($id, $printer, $copies, $type){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$repositoryEAN=$this->getDoctrine()->getRepository(ERPEAN13::class);
		$repositoryProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
		$repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		$code="";
		$barcode="0000000000000";
		$name="";
		$noValidate=false;
		if($type==1){  //Product id barcode
			$product=$repositoryProduct->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
			if($product){
				$code=$product->getCode();
				$barcode='P.'.str_pad($product->getId(),8,'0', STR_PAD_LEFT);
				$name=$product->getName();
			}
		}else
			if($type==2){  //Product EAN barcode
				$ean=$repositoryEAN->findOneBy(["id"=>$id]);
				if($ean){
					$code=$ean->getProductvariant()->getProduct()->getCode();
					$barcode=$ean->getName();
					$name=$ean->getProductvariant()->getProduct()->getName();
					if ($ean->getCustomer())
	 			  	if ($ean->getCustomer()->getCode()=='C01448') $noValidate=true;
	 					else $noValidate=false;
	 			else $noValidate=false;
				}
			} else {
				if($type==3){  //Variant id barcode
					$productvariant=$repositoryVariants->findOneBy(["id"=>$id]);
					if($productvariant && $productvariant->getProduct() && $productvariant->getVariant()){
						$code=$productvariant->getProduct()->getCode();
						$barcode='V.'.str_pad($productvariant->getId(),8,'0', STR_PAD_LEFT);
						$name=$productvariant->getProduct()->getName().' - '.$productvariant->getVariant()-getVarianttype()->getName().' '.$productvariant->getVariant()->getName();
					}
				}
			}
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser(), "noValidate"=>$noValidate];

		$reportsUtils = new ERPEan13Reports();
		$tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'printers'.DIRECTORY_SEPARATOR.$printer.DIRECTORY_SEPARATOR;

		if (!file_exists($tempPath) && !is_dir($tempPath)) {
				mkdir($tempPath, 0775, true);
		}
		for($i=0; $i<$copies; $i++){
			$pdf=$reportsUtils->create($params,'F',$tempPath.$barcode.'-'.$i.'.pdf');
		}
		return new JsonResponse(["result"=>1]);
 }


 /**
 * @Route("/{_locale}/ERP/product/category/{id}/change/{idcat}", name="changeProductCategory", defaults={"id"=0, "idcat"=0})
 */
 public function changeProductCategory($id, $idcat, Request $request){
	 $this->denyAccessUnlessGranted('ROLE_USER');
	 $repositoryProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
	 $repositoryCategory=$this->getDoctrine()->getRepository(ERPCategories::class);
	 $ids=null;
	 if($id!=0){
		 $ids=$id;
	 }else {
			$ids=$request->request->get('ids');
	 }
		$ids=explode(",",$ids);
		foreach($ids as $item){
			$product=$repositoryProduct->findOneBy(["id"=>$item, "company"=>$this->getUser()->getCompany()]);
			$category=$repositoryCategory->findOneBy(["id"=>$idcat, "company"=>$this->getUser()->getCompany()]);
			if($product && $category){
				$product->setCategory($category);
				$this->getDoctrine()->getManager()->persist($product);
				$this->getDoctrine()->getManager()->flush();
				$result=1;
			}else $result=-1;
		}
	 return new JsonResponse(array('result' => $result));
 }

 /**
 * @Route("/api/ERP/product/stocks/{sku}", name="stocksInfo")
 */
 public function stocksInfo($sku){
	 //Stocks and locations for Navision
	 $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
	 $product=$productRepository->findOneBy(["code"=>$sku]);
	 $stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
	 if($product!=NULL){

		 	$isgrouped=$product->getGrouped();
			if($isgrouped)
			{
				$variants=$productRepository->getVariants($product->getId());
				$stockCampollano=[];
				$stockRomica=[];
				foreach ($variants as $variant) {
					$stockCampollano[$variant["id"]]=$stocksRepository->getStocksByProduct($product->getId(),$variant["id"],1);
					$stockRomica[$variant["id"]]=$stocksRepository->getStocksByProduct($product->getId(),$variant["id"],2);
				}

				return $this->render('@ERP/stocksNavision.html.twig', [
				'controllerName' => 'ERPProductsController',
				'interfaceName' => 'Stock Navision',
				'optionSelected' => "stock",
				'producto' => $product->getName(),
				'sku_producto' => $product->getCode(),
				'variantes' => $variants,
				'agrupado' => $isgrouped,
				'stockCampollano' => $stockCampollano,
				'stockRomica' => $stockRomica,
				]);

			}
			else{
				$stockCampollano=$stocksRepository->getStocksByProduct($product->getId(),null,1);
		 		$stockRomica=$stocksRepository->getStocksByProduct($product->getId(),null,2);
				return $this->render('@ERP/stocksNavision.html.twig', [
				'controllerName' => 'ERPProductsController',
				'interfaceName' => 'Stock Navision',
				'optionSelected' => "stock",
				'producto' => $product->getName(),
				'variantes' => null,
				'stockCampollano' => $stockCampollano,
				'stockRomica' => $stockRomica,
				]);

			}

 		}
		else{
			$stockCampollano=null;
			$stockRomica=null;

			return $this->render('@ERP/stocksNavision.html.twig', [
			'controllerName' => 'ERPProductsController',
			'interfaceName' => 'Stock Navision',
			'optionSelected' => "stock",
			'producto' => 'Este producto no existe',
			'variantes' => null,
			'stockCampollano' => $stockCampollano,
			'stockRomica' => $stockRomica,
			]);
		}


 }

 /**
	* @Route("/api/ERP/product/locate/{id}/{type}", name="addProductLocation", defaults={"type"=1})
	*/
	public function addProductLocation($id, $type, RouterInterface $router,Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

		 $repositoryProducts=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
		 $repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
		 $repositoryStocks=$this->getDoctrine()->getRepository(ERPStocks::class);
		 $repositoryProductsVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		 $location=$request->request->get('loc',null);
		 //Get Store ID
		 $storeId=1;
		 $locationArray=explode('+',$location);
		 $location=$locationArray[0];
		 if(isset($locationArray[1]))	$storeId=intval(substr($locationArray[1],4));
		 if($storeId==0) $storeId=1;

		 //$storeId=$request->request->get('store',null);
		 		 $store=$repositoryStores->findOneBy(["id"=>$storeId, "company"=>$this->getUser()->getCompany()]);
		 if($store==null) return new JsonResponse(["result"=>-4, "text"=> "Almacén no encontrado"]);

		 $productvariant=null;
		 if($type==1){
			 	$product=$repositoryProducts->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			 	if($product==null || $location==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
				$productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product, "variant"=>null, "deleted"=>0]);
		 }else{
				$productvariant=$repositoryProductsVariants->findOneBy(["id"=>$id, "deleted"=>0]);
				if($productvariant==null || $location==null) return new JsonResponse(["result"=>-2, "text"=> "Variante no encontrada"]);
				$product=$productvariant->getProduct();
				if($product->getCompany()!=$this->getUser()->getCompany()) $product=null;
			 	if($product==null || $location==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
		 }

		 $storelocation=$repositoryStoreLocations->findOneBy(["name"=>$location, "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 if($storelocation==null) return new JsonResponse(["result"=>-3, "text"=> "Ubicación no encontrada"]);

		 $stock=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

		 if(!$stock){
			 //Try to find in generic ALM01 or ALM02
			 if($storeId==1)
			 	$genericALM=$repositoryStoreLocations->findOneBy(["name"=>"ALM01", "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				else $genericALM=$repositoryStoreLocations->findOneBy(["name"=>"ALM02", "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

				$stock=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$genericALM, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

				 //return new JsonResponse(["result"=>0, "text"=> $product->getId()]);
		 }

		 if(!$stock){
			 $stock=new ERPStocks();
			 $stock->setDateadd(new \DateTime);
			 $stock->setLastinventorydate(new \DateTime);
			 $stock->setCompany($this->getUser()->getCompany());
			 $stock->setProductvariant($productvariant);
			 $stock->setQuantity(0);
			 $stock->setActive(1);
			 $stock->setDeleted(0);
		 }
		 	 $stock->setAuthor($this->getUser());
		 	 $stock->setDateupd(new \DateTime);
			 $stock->setStorelocation($storelocation);

			 $this->getDoctrine()->getManager()->persist($stock);
			 $this->getDoctrine()->getManager()->flush();
			 return new JsonResponse(["result"=>1, "text"=> ""]);
 	 }

	 /**
	  * @Route("/api/ERP/product/locate/move/{id}/{type}", name="moveProductLocation", defaults={"type"=1})
	  */
	  public function moveProductLocation($id, $type, RouterInterface $router,Request $request){
	 		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 		$repositoryProducts=$this->getDoctrine()->getRepository(ERPProducts::class);
			$repositoryProductsVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
	 		$repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
	 		$repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
	 		$repositoryStocks=$this->getDoctrine()->getRepository(ERPStocks::class);
	 		$locationSource=$request->request->get('locsrc',null);
			$locationDestination=$request->request->get('locdst',null);
			$qty=$request->request->get('qty',null);

			//Get Store ID
			$storeId=1;
			$locationArray=explode('+',$locationSource);
			$locationSource=$locationArray[0];
			if(isset($locationArray[1]))	$storeId=intval(substr($locationArray[1],4));

			$locationArray=explode('+',$locationDestination);
			$locationDestination=$locationArray[0];

			if($storeId==0) $storeId=1;

	 		//$storeId=$request->request->get('store',null);


			$store=$repositoryStores->findOneBy(["id"=>$storeId, "company"=>$this->getUser()->getCompany()]);
			if($store==null) return new JsonResponse(["result"=>-4, "text"=> "Almacén no encontrado"]);

			$productvariant=null;
			if($type==1){
				 $product=$repositoryProducts->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
				 if($product==null || $locationSource==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
				 	$productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product, "variant"=>null, "deleted"=>0]);
			}else{
				 $productvariant=$repositoryProductsVariants->findOneBy(["id"=>$id, "deleted"=>0]);
				 if($productvariant==null || $locationSource==null) return new JsonResponse(["result"=>-2, "text"=> "Variante no encontrada"]);
				 $product=$productvariant->getProduct();
				 if($product->getCompany()!=$this->getUser()->getCompany()) $product=null;
				 if($product==null || $locationSource==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
			}

			$storelocation=$repositoryStoreLocations->findOneBy(["name"=>$locationSource, "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			if($storelocation==null) return new JsonResponse(["result"=>-3, "text"=> "Ubicación origen no encontrada"]);

			$storedstlocation=$repositoryStoreLocations->findOneBy(["name"=>$locationDestination, "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			if($storedstlocation==null) return new JsonResponse(["result"=>-4, "text"=> "Ubicación destino no encontrada"]);

			if($storelocation==$storedstlocation) return new JsonResponse(["result"=>1, "text"=> "No se han realizado cambios"]);

		  $stock=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storelocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			$stockdst=$repositoryStocks->findOneBy(["productvariant"=>$productvariant, "storelocation"=>$storedstlocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

			if($stock){

				if(!$stockdst){
						if($stock->getQuantity()-$qty==0){
							//change de location
						 	$stock->setAuthor($this->getUser());
			 		 	 	$stock->setDateupd(new \DateTime);
			 			 	$stock->setStorelocation($storedstlocation);

							$StockHistory= new ERPStocksHistory();
							$StockHistory->setProductVariant($productvariant);
							$StockHistory->setLocation($storelocation);
							$StockHistory->setUser($this->getUser());
			        $StockHistory->setCompany($this->getUser()->getCompany());
							$StockHistory->setPreviousqty($stock->getQuantity());
							$StockHistory->setNewqty(0);
							$StockHistory->setActive(1);
							$StockHistory->setDeleted(0);
							$StockHistory->setDateupd(new \DateTime());
							$StockHistory->setDateadd(new \DateTime());

							$this->getDoctrine()->getManager()->persist($StockHistory);
							$this->getDoctrine()->getManager()->persist($stock);
			 			  $this->getDoctrine()->getManager()->flush();

						}else if($stock->getQuantity()-$qty>0){
							//Split location
							$stock->setDateupd(new \DateTime);
							$stock->setQuantity($stock->getQuantity()-$qty);

							$newStock=new ERPStocks();
				 			$newStock->setDateadd(new \DateTime);
				 			$newStock->setLastinventorydate(new \DateTime);
				 			$newStock->setCompany($this->getUser()->getCompany());
				 			$newStock->setProductvariant($productvariant);
				 			$newStock->setQuantity($qty);
				 			$newStock->setActive(1);
				 			$newStock->setDeleted(0);
				 		  $newStock->setAuthor($this->getUser());
				 		  $newStock->setDateupd(new \DateTime);
				 		  $newStock->setStorelocation($storedstlocation);

							$this->getDoctrine()->getManager()->persist($stock);
							$this->getDoctrine()->getManager()->persist($newStock);
			 			  $this->getDoctrine()->getManager()->flush();

						}else return new JsonResponse(["result"=>-6, "text"=> "No hay stock suficiente para realizar el movimiento"]);
				}else{
					if($stock->getQuantity()-$qty==0){
						$stockdst->setDateupd(new \DateTime);
						$stockdst->setQuantity($stockdst->getQuantity()+$qty);

						$StockHistory= new ERPStocksHistory();
						$StockHistory->setProductVariant($productvariant);
						$StockHistory->setLocation($storelocation);
						$StockHistory->setUser($this->getUser());
						$StockHistory->setCompany($this->getUser()->getCompany());
						$StockHistory->setPreviousqty($stock->getQuantity());
						$StockHistory->setNewqty(0);
						$StockHistory->setActive(1);
						$StockHistory->setDeleted(0);
						$StockHistory->setDateupd(new \DateTime());
						$StockHistory->setDateadd(new \DateTime());

						$this->getDoctrine()->getManager()->persist($StockHistory);
						$this->getDoctrine()->getManager()->persist($stockdst);
						$this->getDoctrine()->getManager()->remove($stock);
						$this->getDoctrine()->getManager()->flush();

					}else if($stock->getQuantity()-$qty>0){
						//Split location
						$stock->setDateupd(new \DateTime);
						$stock->setQuantity($stock->getQuantity()-$qty);
						$stockdst->setDateupd(new \DateTime);
						$stockdst->setQuantity($stockdst->getQuantity()+$qty);
						$this->getDoctrine()->getManager()->persist($stock);
						$this->getDoctrine()->getManager()->persist($stockdst);
						$this->getDoctrine()->getManager()->flush();

					}else return new JsonResponse(["result"=>-6, "text"=> "No hay stock suficiente para realizar el movimiento"]);
				}
				return new JsonResponse(["result"=>1, "text"=> "Cambio realizado correctamente"]);
			}else return new JsonResponse(["result"=>-5, "text"=> "Stock no encontrado"]);


		}

		/**
		* @Route("/api/erp/product/getimages/{id}", name="getProductImages")
		*/
		public function getProductImages($id, RouterInterface $router,Request $request){
			$response = $this->forward('App\Modules\Globale\Controller\GlobaleImagesController::getTypeImages', [
				'type'  => 'products',
				'id' => $id,
			]);
		 return $response;
		 //return new JsonResponse([]);
		}

		/**
		* @Route("/api/ERP/product/latestmovements/{id}/{type}", name="productLatestMovements", defaults={"type"=1})
		*/
		public function productLatestMovements($id, $type){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $repositoryProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		 $product=$repositoryProduct->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
		 if(!$product) return new JsonResponse(["result"=>-2, "text"=> "El producto proporcionado no existe"]);
		 if($type==1){  //Product id barcode
			 $result=$repositoryProduct->latestMovements($product);
			 return new JsonResponse(["result"=>1, "data"=>$result]);
		 }else{
			 //TODO: Movements by Variant, Navision haven't this information
		 }

		 return new JsonResponse(["result"=>-1, "text"=> "Ocurrio un error inexperado"]);
		}


		/**
		* @Route("/api/ERP/product/pscategoriesgettree/{id}", name="psCategoriesGetTree", defaults={"id"=0})
		*/
		public function psCategoriesGetTree($id, RouterInterface $router,Request $request){
/*
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$product=$productRepository->findOneBy(["id"=>$id]);

			$rootDir=$this->get('kernel')->getRootDir();
			$prestashopUtils= new ERPPrestashopUtils();
			$result=$prestashopUtils->getCategoriesTree(2);

			if($result) return new JsonResponse(["result"=>$result]);
			else return new JsonResponse(NULL);

*/

			}


			/**
	 	 * @Route("/api/ERP/product/variants/{code}/get", name="getProductVariants")
	 	 */
	 	 public function getProductVariants($code, RouterInterface $router,Request $request){
	 		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
	 	  $product=$productRepository->findOneBy(["code"=>$code]);

		//	$repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$variants=$productRepository->getvariant($product->getId());
	 		$responseVariants=Array();

	 		foreach($variants as $variant){
				$item['id']=$variant['id'];
	 			$item['name']=$variant['name'];
	 			$responseVariants[]=$item;
	 		}

	 		return new JsonResponse(["variants"=>$responseVariants]);

	 	 }


		 /**
		* @Route("/api/ERP/product/suppliers/{code}/get", name="getProductSuppliers", defaults={"code"=0})
		*/
		public function getProductSuppliers($code, RouterInterface $router,Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $product=$productRepository->findOneBy(["code"=>$code]);

	 //	$repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		 $suppliers=$productRepository->getSuppliers($product->getId());
		 $responseSuppliers=Array();

		 foreach($suppliers as $supplier){

			 $item['id']=$supplier['id'];
			 $item['name']=$supplier['name'];
			 if($product->getSupplier()->getId()==$supplier['id']) $item['preferential']=1;
			 else $item['preferential']=0;
			 $responseSuppliers[]=$item;
		 }

		 return new JsonResponse(["suppliers"=>$responseSuppliers]);

		}


		/**
 	 * @Route("/api/erp/getproducts/{supplier}/{category}", name="getProducts")
 	 */
 	public function getProducts($supplier, $category){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			 $referenceRepository=$this->getDoctrine()->getRepository(ERPReferences::class);
			 $EAN13Repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
			 $productsSuppliersPricesRepository=$this->getDoctrine()->getRepository(ERPProductsSuppliersPrices::class);
			 $objects=$productRepository->productsBySupplierCategory($supplier,$category);
			 $products=[];
			 foreach ($objects as $object){
				 $item=$productRepository->findOneById($object["id"]);
				 $product["id"]=$object["id"];
				 $product["code"]='<a href="'.$this->generateUrl('formProduct',["id"=>$object["id"]]).'" class="external">'.$item->getCode().'</a>';
				 $reference=$referenceRepository->findOneBy(["product"=>$item->getId(),"supplier"=>$supplier, "active"=>1, "deleted"=>0]);
				 if ($reference!=null) $product["reference"]=$reference->getName();
				 else $product["reference"]='';
				 $EAN13=$EAN13Repository->findOneBy(["product"=>$item->getId(),"supplier"=>$supplier, "active"=>1, "deleted"=>0]);
				 if($EAN13!=null)	 $product["EAN13"]=$EAN13->getName();
				 else $product["EAN13"]='';
				 $product["name"]='<a href="'.$this->generateUrl('formProduct',["id"=>$object["id"]]).'" class="external">'.$item->getName().'</a>';
				 if($item->getCategory()!=null)  $product["category"]=$item->getCategory()->getName();
				 else $product["category"]='';
				 $product["netprice"]=$item->getNetprice()==1?"true":"false";
				 if ($item->getNetprice()==false) $productssuppliersdiscounts=$this->getShoppingDiscounts($supplier,$item->getCategory());
				 else $productssuppliersdiscounts=null;
				 if ($productssuppliersdiscounts!=null) {
					 $product["discount"]=$productssuppliersdiscounts->getDiscount();
					 $product["start"]=$productssuppliersdiscounts->getStart()!=null ? $productssuppliersdiscounts->getStart()->format('d/m/Y') : '---';
					 $product["end"]=$productssuppliersdiscounts->getEnd()!=null ? $productssuppliersdiscounts->getEnd()->format('d/m/Y') : '---';
				 }
				 else {
					 $product["discount"]=0;
					 $product["start"]='';
					 $product["end"]='';
				 }
				 $product["quantity"]=0;
				 	/* $product["shopping_price"]=$item->getShoppingPrice();
				 $product["PVP"]=$item->getPVPR()==0 ? $item->getPVP() : $item->getPVPR();
				 $product["PVP"]=$product["PVP"]==null ? 0 : $product["PVP"];
				 */

				 $productPrices=$productsSuppliersPricesRepository->getProductsSuppliersPrices($object["id"],$supplier);
				 if ($item->getNetprice()){
					 foreach ($productPrices as $specificPrice){
						 $product["quantity"]=$specificPrice["quantity"];
						 $product["shopping_price"]=$specificPrice["price"];
						 $product["PVPr"]=$specificPrice["pvp"]==null ? '--' : $specificPrice["pvp"];
						 $products[]=$product;
						 $product["code"]='---';
						 $product["reference"]='---';
					 }
				 }
				 $product=[];
			 }

			 return new JsonResponse(["products"=>$products]);
 	}

		 /**
	 * @Route("/api/ERP/product/{code}/grouped", name="isGrouped")
	 */
	 public function isGrouped($code, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$product=$productRepository->findOneBy(["code"=>$code]);

		if($product->getGrouped()) return new JsonResponse(["result"=>1]);
		else return new JsonResponse(["result"=>0]);


	 }

	 public function getShoppingDiscounts($supplier, $category){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $repositoryproductssuppliersdiscounts=$this->getDoctrine()->getRepository(ERPProductsSuppliersDiscounts::class);
		 //Search in the treeCategories which is the most specific with productssuppliersdiscounts
		 $repositoryCategory=$this->getDoctrine()->getRepository(ERPCategories::class);
		 $productssuppliersdiscounts=$repositoryproductssuppliersdiscounts->findOneBy(["supplier"=>$supplier,"category"=>$category,"active"=>1,"deleted"=>0]);
		 if ($category!=null)
		 while ($category->getParentid()!=null && $productssuppliersdiscounts==null){
				 $category=$category->getParentid();
				 $productssuppliersdiscounts=$repositoryproductssuppliersdiscounts->findOneBy(["supplier"=>$supplier,"category"=>$category,"active"=>1,"deleted"=>0]);
		 }
		 if ($productssuppliersdiscounts==null)
				 $productssuppliersdiscounts=$repositoryproductssuppliersdiscounts->findOneBy(["supplier"=>$supplier,"active"=>1,"deleted"=>0]);
		 return $productssuppliersdiscounts!=null?$productssuppliersdiscounts:0;
	 }

	 /**
	 * 	@Route("/es/ERP/generateQR", name="generateQR")
	 */

	 public function generateQR(RouterInterface $router,Request $request){
		 $transfer=$request->query->get('name',null);
		 $params["rootdir"]= $this->get('kernel')->getRootDir();
		 $params["user"]=$this->getUser();
		 $params["name"]=$transfer;
		 $printQRUtils = new ERPPrintQR();
		 $name=substr($transfer,3);
		 $repositoryTransfers=$this->getDoctrine()->getRepository(NavisionTransfers::class);
		 $params["transfers"]=$repositoryTransfers->findBy(["name"=>$name, "active"=>1, "deleted"=>0]);
		 if (empty($params["transfers"])) {
			 $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-printTransfer.php?from='.$name);
			 $objects=json_decode($json, true);
			 $objects=$objects[0]["class"];
			 if (empty($objects)){
				 $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getTransfer.php?from='.$name);
				 $objects=json_decode($json, true);
				 $objects=$objects[0]["class"];
			 }
			 foreach ($objects as $object){
				 	 $transfersRepository=$this->getDoctrine()->getRepository(NavisionTransfers::class);
				 	 $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
				 	 $storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
					 $product=$productsRepository->findOneBy(["code"=>$object["code"]]);
					 $item=$transfersRepository->findOneBy(["name"=>$name, "product"=>$product, "active"=>1, "deleted"=>0]);
					 if ($item==null){
						 if (array_key_exists("Transfer-from Code",$object)) $originStore=$storesRepository->findOneBy(["code"=>$object["Transfer-from Code"]]);
						 else $originStore=$storesRepository->findOneBy(["code"=>"ALM01"]);
						 $dateSend=new \DateTime(date('Y-m-d 00:00:00',strtotime($object["dateSend"]["date"])));
						 $destinationStore=$storesRepository->findOneBy(["code"=>$object["almacen"]]);
						 $obj=new NavisionTransfers();
						 $obj->setOriginstore($originStore);
						 $obj->setDestinationstore($destinationStore);
						 $obj->setProduct($product);
						 $obj->setName($name);
						 $obj->setQuantity((int)$object["stock"]);
						 $obj->setCompany($this->getUser()->getCompany());
						 $obj->setDateadd(new \Datetime());
						 $obj->setDateupd(new \Datetime());
						 $obj->setDatesend($dateSend);
						 $obj->setActive(1);
						 $obj->setDeleted(0);
						 $obj->setReceived(0);
						 $this->getDoctrine()->getManager()->persist($obj);
					 }
					 $this->getDoctrine()->getManager()->flush();
				 }
		 $params["transfers"]=$repositoryTransfers->findBy(["name"=>$name, "active"=>1, "deleted"=>0]);
		 }
		 //$pdf=$printQRUtils->create($params);
		 $pdf=$printQRUtils->transferQR($params);
		 return new Response("", 200, array('Content-Type' => 'application/pdf'));
	 }


	 /**
	 * @Route("/es/ERP/receiveTransfer/{transfer}", name="receiveTransfer")
	 */

	 public function receiveTransfer($transfer, RouterInterface $router,Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getTransfer.php?from='.$transfer);
		 $objects=json_decode($json, true);
		 $objects=$objects[0]["class"];
		 $managerUserRepository=$this->getDoctrine()->getRepository(ERPStoresManagersUsers::class);
		 $managerUser=$managerUserRepository->findOneBy(['user'=>$this->getUser()->getId()]);
		 if ($managerUser==null) return new JsonResponse(["result"=>-1, "text"=>"El usuario ".$this->getUser()->getName()." no tiene permisos de recepcionar material."]);
		 foreach ($objects as $object){
			 // buscamos el almacen del traspaso
			 $storeRepository=$this->getDoctrine()->getRepository(ERPStores::class);
			 $store=$storeRepository->findOneBy(['code'=>$object["almacen"]]);
			 $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			 $storeLocation=$storeLocationsRepository->findOneBy(['name'=>$object["almacen"]]);
			 $storeUsersRepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			 $storeUsers=$storeUsersRepository->findOneBy(['user'=>$this->getUser()->getId(),'store'=>$store->getId(), 'active'=>1, 'preferential'=>1]);
			 if ($storeUsers==null) return new JsonResponse(["result"=>-2, "text"=>"El usuario ".$this->getUser()->getName()." no es gestor del almacén ".$store->getName()]);
			 // buscamos el producto del traspaso
			 $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			 $product=$productRepository->findOneBy(['code'=>$object["code"]]);
			 if ($product==null) return new JsonResponse(["result"=>-3, "text"=>"El producto ".$object["code"]." no existe en la base de datos"]);
			 //miramos si es una variante de un producto agrupado
			 $productvariant=null;
			 $variant=null;
			 $repositoryVariants=$this->getDoctrine()->getRepository(ERPVariants::class);
	     $repositoryProductsVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			 if($object["variant"]!="") $variant=$repositoryVariants->findOneBy(["name"=>$object["variant"]]);
       $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product,"variant"=>$variant]);
			 // buscamos la fila de los traspasos del producto y del almacén
			 $stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
			 $stocks=$stocksRepository->findOneBy(['storelocation'=>$storeLocation, 'productvariant'=>$productvariant, "active"=>1, "deleted"=>0]);
			 if ($stocks==null) return new JsonResponse(["result"=>-4, "text"=>"El producto  ".$object["code"]." no está en el almacén ".$store->getName()]);
			 // actualizamos el stock del pendiente de recibir
			 $received=(int)$object["stock"];
			 if ($stocks->getPendingreceive()<$received) return new JsonResponse(["result"=>-5, "text"=>"El producto  ".$object["code"]." no tiene pendiente de recibir tantas unidades "]);
			 $stocks->setPendingreceive($stocks->getPendingreceive()-$received);
			 $this->getDoctrine()->getManager()->persist($stocks);
			 // si el traspaso se realiza en un almacén que no sea campollano/romica buscamos el stock del producto para modificarlo
			 // en la ubicación genérica de ese almacén
			 if ($store->getId()>2){
				 $locationRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
				 $location=$locationRepository->findOneBy(['store'=>$store->getId()]);
				 $stockRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
				 $stock=$stockRepository->findOneBy(['storelocation'=>$location->getId(), 'product'=>$product->getId()]);
				 $typesRepository=$this->getDoctrine()->getRepository(ERPTypesMovements::class);
				 $type=$typesRepository->findOneBy(["name"=>"Traspaso recibido"]);
				 $stockHistory=new ERPStocksHistory();
				 $stockHistory->setProductVariant($productvariant);
         $stockHistory->setLocation($storeLocation);
         $stockHistory->setUser($this->getUser());
				 $stockHistory->setCompany($this->getUser()->getCompany());
         $stockHistory->setDateadd(new \Datetime());
         $stockHistory->setDateupd(new \Datetime());
         $stockHistory->setNumOperation($transfer);
         $stockHistory->setQuantity($received);
				 $stockHistory->setPreviousqty($stock->getQuantity());
				 $stockHistory->setNewqty($stock->getQuantity()+$received);
         $stockHistory->setType($type);
         $stockHistory->setActive(true);
         $stockHistory->setDeleted(false);
				 $stock->setQuantity($stock->getQuantity()+$received);

				 $this->getDoctrine()->getManager()->persist($stock);
				 $this->getDoctrine()->getManager()->persist($stockHistory);
			 } else return new JsonResponse(["result"=>-6, "text"=>"El almacén de destino (".$store->getName().") no se corresponde con un almacén gestionado"]);


			 $this->getDoctrine()->getManager()->flush();
		 	}

		 $transfersRepository=$this->getDoctrine()->getRepository(NavisionTransfers::class);
		 $transfersRepository->recivedTransfer($transfer);
		 return new JsonResponse(["result"=>1, "text"=>"Se ha recepcionado la mercancia del traspaso ".$transfer]);
	 }

	 /**
	 * @Route("/api/getWSProductsSupplier/{supplier_id}", name="getWSProductsSupplier", defaults={"supplier_id"=0})
	 */
	 public function getWSProductsSupplier(Request $request, $supplier_id)
	 {
		 // Listado de productos de un proveedor que tengan precio
	 	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $productRepository			= $this->getDoctrine()->getRepository(ERPProducts::class);
		 $result 	 = [];
		 $q 			 = $request->get('q');
		 $products = $productRepository->getProductsBySupplier($supplier_id, $q);
		 if ($products!=null){
			 $result = $products;
		 }
	 	 return new JsonResponse($result);
	 }

	 /**
	 * @Route("/api/getWSProductSupplier/{supplier_id}/{quantity}/{product_id}/{store_id}", name="getWSProductSupplier", defaults={"supplier_id"=0, "quantity"=1, "product_id"=0, "store_id"=0})
	 */
	 public function getWSProductSupplier($supplier_id, $quantity, $product_id, $store_id)
	 {
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$erpStocksRepository = $this->getDoctrine()->getRepository(ERPStocks::class);
			$result = [];
			$product = $productRepository->getProductBySupplier($supplier_id, $product_id, $quantity);
			if ($product!=null){
				$result = $product;
				if ($result!=null && count($result)>0){
					$aproduct = explode('~',$product_id);
					if (count($aproduct)>1)
						$product_id = $aproduct[0];
          $oproduct = $productRepository->find($product_id);
          if ($oproduct!=null && $oproduct->getStockcontrol()){
						$astore = explode('~',$store_id);
		        if (count($astore)>1)
		          $store_id = $astore[0];
            $stock = $erpStocksRepository->getStock($product_id,($oproduct->getGrouped()?0:null), $store_id);
            for($i=0; $i<count($result); $i++){
							if ($stock!=null){
                foreach($stock as $key=>$value){
									if ($value==null || $value=='')
										$value=0;
                  $result[$i][$key] = $value;
								}
              }else{
								$result[$i]['stock'] = 0;
								$result[$i]['minstock'] = 0;
								$result[$i]['stockpedingreceive'] = 0;
								$result[$i]['stockpedingserve'] = 0;
								$result[$i]['stockvirtual'] = 0;
								$result[$i]['stockt'] = 0;
								$result[$i]['stockpedingreceivet'] = 0;
								$result[$i]['stockpedingservet'] = 0;
								$result[$i]['stockvirtualt'] = 0;
							}
            }
          }
        }
			}
			return new JsonResponse($result);
	 }

	 /**
	 * @Route("/api/getWSProductSupplierMasive/{supplier_id}", name="getWSProductSupplierMasive", defaults={"supplier_id"=0})
	 */
	 public function getWSProductSupplierMasive($supplier_id)
	 {
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$result = [];
			if ($supplier_id!= null){
				$products = $productRepository->getProductsBySupplierMasive($supplier_id);
				if ($products!=null)
        	$result = $products;
			}
			return new JsonResponse($result);
	 }
	 /**
	 * @Route("/api/getWSProductStock/{product_id}/{variant_id}/{store_id}", name="getWSProductStock", defaults={"product_id"=0, "variant_id"=0, "store_id"=0})
	 */
	 public function getWSProductStock($product_id, $variant_id, $store_id)
	 {
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$erpStocksRepository = $this->getDoctrine()->getRepository(ERPStocks::class);
			$result = [];
			$aproduct = explode('~',$product_id);
			if (count($aproduct)>1)
				$product_id = $aproduct[0];
      $oproduct = $productRepository->find($product_id);
      if ($oproduct!=null && $oproduct->getStockcontrol()){
				$avariant = explode('~',$variant_id);
				if (count($avariant)>1)
					$variant_id = $avariant[0];
				$astore = explode('~',$store_id);
        if (count($astore)>1)
          $store_id = $astore[0];
        $stock = $erpStocksRepository->getStock($product_id,($oproduct->getGrouped()?$variant_id:null), $store_id);
				if ($stock!=null){
          foreach($stock as $key=>$value){
						if ($value==null || $value=='')
							$value=0;
            $result[0][$key] = $value;
					}
        }else{
					$result[0]['stock'] = 0;
					$result[0]['minstock'] = 0;
					$result[0]['stockpedingreceive'] = 0;
					$result[0]['stockpedingserve'] = 0;
					$result[0]['stockvirtual'] = 0;
					$result[0]['stockt'] = 0;
					$result[0]['stockpedingreceivet'] = 0;
					$result[0]['stockpedingservet'] = 0;
					$result[0]['stockvirtualt'] = 0;
				}
      }

			return new JsonResponse($result);
	 }
}
