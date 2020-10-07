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
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;

class ERPProductsController extends Controller
{
	private $class=ERPProducts::class;
	private $module='ERP';
	//private $utilsClass=ERPProductsUtils::class;
    /**
     * @Route("/{_locale}/admin/global/products", name="products")
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
					'include_post_templates' => ['@ERP/categoriesmap.html.twig','@ERP/productlistcategories.html.twig'],
					'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
															 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
															 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
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

		 //$utils->values(["manufacturer"=>$manufacturer]);
		 //return $utils->make($id, $this->class, $action, "formproducts","full", "@ERP/productform.html.twig", "formProduct");

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
				if($obj) return $this->redirectToRoute('formProduct', ['id' => $obj->getId()]);
				else return $this->redirectToRoute('formProduct', ['id' => 0]);
			}

			$obj = $productRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$product_name=$obj?$obj->getName():'';
			if ($obj && $obj->getGrouped()) {
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
									'tabs' => [
										["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Products data", "active"=>true, "route"=>$this->generateUrl("dataProduct",["id"=>$id])],
										["name" => "variants", "icon"=>"fa fa-id-card", "caption"=>"Variants", "route"=>$this->generateUrl("generictablist",["function"=>"formatListByProduct","module"=>"ERP","name"=>"ProductsVariants","id"=>$id])],
										["name" => "ean13",  "icon"=>"fa fa-users", "caption"=>"EAN13", "route"=>$this->generateUrl("listEAN13",["id"=>$id])],
										["name" => "references",  "icon"=>"fa fa-users", "caption"=>"References", "route"=>$this->generateUrl("listReferences",["id"=>$id])],
										["name"=>  "productPrices", "icon"=>"fa fa-money", "caption"=>"Prices","route"=>$this->generateUrl("infoProductPrices",["id"=>$id])],
										["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("infoStocks",["id"=>$id])],
										["name" => "webproduct", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("dataWebProducts",["id"=>$id])],
										["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]
										],
										'include_tab_post_templates' => ['@ERP/categoriesmap.html.twig', '@ERP/categoriesmapproduct.html.twig'],
										'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																				["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
										'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
													 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
																				 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]

					));

			} else {
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
								'tabs' => [
									["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Products data", "active"=>true, "route"=>$this->generateUrl("dataProduct",["id"=>$id])],
									["name" => "ean13",  "icon"=>"fa fa-users", "caption"=>"EAN13", "route"=>$this->generateUrl("listEAN13",["id"=>$id])],
									["name" => "references",  "icon"=>"fa fa-users", "caption"=>"References", "route"=>$this->generateUrl("listReferences",["id"=>$id])],
									["name"=>  "productPrices", "icon"=>"fa fa-money", "caption"=>"Prices","route"=>$this->generateUrl("infoProductPrices",["id"=>$id])],
									["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("infoStocks",["id"=>$id])],
									["name" => "webproduct", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("dataWebProducts",["id"=>$id])],
									["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]
									],
									'include_tab_post_templates' => ['@ERP/categoriesmap.html.twig', '@ERP/categoriesmapproduct.html.twig'],
									'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																			["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
									'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
												 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
																			 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]

				));}
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

		/*$listEAN13 = new ERPEAN13Utils();
			$formUtilsEAN = new GlobaleFormUtils();
			$formUtilsEAN->initialize($this->getUser(), new ERPEAN13(), dirname(__FILE__)."/../Forms/EAN13.json", $request, $this, $this->getDoctrine());
			$forms[]=$formUtilsEAN->formatForm('EAN13', true, null, ERPEAN13::class);


			$listReferences = new ERPReferencesUtils();
			$formUtilsReferences = new GlobaleFormUtils();
			$formUtilsReferences->initialize($this->getUser(), new ERPReferences(), dirname(__FILE__)."/../Forms/References.json", $request, $this, $this->getDoctrine());
			$forms[]=$formUtilsReferences->formatForm('References', true, null, ERPReferences::class);


			$listAttributes = new ERPProductsAttributesUtils();
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$product=$productRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);

			$formUtilsAttributes = new GlobaleFormUtils();
			$formUtilsAttributes->initialize($this->getUser(), new ERPProductsAttributes(), dirname(__FILE__)."/../Forms/References.json", $request, $this, $this->getDoctrine(),
			$listAttributes->getExcludedForm(null),
			$listAttributes->getIncludedForm(["parent"=>$product, "doctrine"=>$this->getDoctrine(), "user"=>$this->getUser()]));
			$forms[]=$formUtilsAttributes->formatForm('ProductsAttributes', true, null, ERPProductsAttributes::class);
*/
			return $this->render('@ERP/productform.html.twig', array(
				'controllerName' => 'productsController',
				'interfaceName' => 'Productos',
				'optionSelected' => 'products',
				'userData' => $userdata,
				'id' => $id,
				'id_object' => $id,
				'form' => $formUtils->formatForm('products', false, $id, $this->class, "dataProduct")
				//'listEAN13' => $listEAN13->formatListByProduct($id),
				//'listReferences' => $listReferences->formatListByProduct($id),
				//'listAttributes' => $listAttributes->formatListByProduct($id),
				//'forms' => $forms
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
			$Variantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			$Stocksrepository=$this->getDoctrine()->getRepository(ERPStocks::class);
			$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$StoreLocationsrepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			$Storesrepository=$this->getDoctrine()->getRepository(ERPStores::class);
			$obj=null;
			$variant=null;
			if($id!=0){
				$obj = $this->getDoctrine()->getRepository($this->class)->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			}else{
				if($request->request->get('barcode',null)){
						if(substr(strtoupper($request->request->get('barcode')),0,2)=="P."){
							$product=$Productrepository->findOneBy(["id"=>intval(substr($request->request->get('barcode'),2)), "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
							$obj=$product;
						}else{
							if(substr(strtoupper($request->request->get('barcode')),0,2)=="V."){
								$variant=$Variantsrepository->findOneBy(["id"=>intval(substr($request->request->get('barcode'),2)), "deleted"=>0]);
								if($variant) $obj=$variant->getProduct();
							}else{
								$EAN13=$EAN13repository->findOneBy(["name"=>$request->request->get('barcode',null), "deleted"=>0]);
								if($EAN13){
								 	$obj=$EAN13->getProduct();
									$variant=$EAN13->getProductvariant();
								}
							}
						}

				}
			}
			if($obj){
				$stocks=$Stocksrepository->findBy(["product"=>$obj, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				$eans=$EAN13repository->findBy(["product"=>$obj, "productvariant"=>null, "active"=>1, "deleted"=>0]);
				$result["id"]=$obj->getId();
				$result["code"]=$obj->getCode();
				$result["variant_id"]=$variant?$variant->getId():0;
				$result["variant_name"]=$variant?$variant->getVariantname()->getName():"";
				$result["variant_value"]=$variant?$variant->getVariantvalue()->getName():"";
				$result["variant_active"]=$variant?$variant->getActive():true;

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

				$variants=$Variantsrepository->findBy(["product"=>$obj, "active"=>1, "deleted"=>0]);
				$result["variants"]=[];
				foreach($variants as $variant){
					$variant_item["id"]=$variant->getId();
					$variant_item["name"]=$variant->getVariantname()->getName();
					$variant_item["value"]=$variant->getVariantvalue()->getName();
					$eans=$EAN13repository->findBy(["product"=>$obj, "productvariant"=>$variant, "active"=>1, "deleted"=>0]);
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
						$stock_item["variant_id"]=!$stock->getProductvariant()?0:$stock->getProductvariant()->getId();
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
						if($EAN13) $obj=$EAN13->getProduct();
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
    * @Route("/api/erp/product/getimages/{id}", name="getProductImages", defaults={"id"=0})
    */
    public function getProductImages($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$image_path = $this->get('kernel')->getRootDir().'/../cloud/'.$this->getUser()->getCompany()->getId().'/images/products/'.$id.'/';
			$images=[];
			if(file_exists( $this->get('kernel')->getRootDir().'/../cloud/'.$this->getUser()->getCompany()->getId().'/images/products/'.$id.'-large.png') || file_exists( $this->get('kernel')->getRootDir().'/../cloud/'.$this->getUser()->getCompany()->getId().'/images/products/'.$id.'-large.jpg')){
				$image=["large"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"large", "id"=>$id, "number"=>0 )),
							 "thumb"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"thumb", "id"=>$id, "number"=>0 )),
							 "medium"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"medium", "id"=>$id, "number"=>0 ))];
				$images[]=$image;
			}
			$found=true;
			$i=1;
			while($found==true){
				if(file_exists($image_path.$id."-".$i.'-large.png') || file_exists($image_path.$id."-".$i.'-large.jpg')){
					$i++;
				}else{
					$found=false;
					$i--;
				}
			}
			for($j=1;$j<=$i;$j++){
				$image=["large"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"large", "id"=>$id, "number"=>$j )),
							 "thumb"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"thumb", "id"=>$id, "number"=>$j )),
							 "medium"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"medium", "id"=>$id, "number"=>$j ))];
				$images[]=$image;
			}
			return new JsonResponse(["result"=>1,"images"=>$images]);
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
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, Products::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]],[],null,"id",$this->getDoctrine());
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
	 if($type==1){
		 $ean=$repository->findOneBy(["id"=>$id]);
		 if($ean){
			 $code=$ean->getProduct()->getCode();
			 $barcode=$ean->getName();
			 $name=$ean->getProduct()->getName();
		 }
		 $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser()];
 	 }else if($type==2){
		 $product=$repositoryProduct->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
	 		if($product){
	 			$code=$product->getCode();
	 			$barcode='P.'.str_pad($product->getId(),8,'0', STR_PAD_LEFT);
	 			$name=$product->getName();
			}
			$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser()];
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
				$code=$ean->getProduct()->getCode();
				$barcode=$ean->getName();
				$name=$ean->getProduct()->getName();
			}
		} else {
			if($type==3){  //Variant id barcode
				$variant=$repositoryVariants->findOneBy(["id"=>$id]);
				if($variant && $variant->getProduct() && $variant->getVariantvalue() && $variant->getVariantname()){
					$code=$variant->getProduct()->getCode();
					$barcode='V.'.str_pad($variant->getId(),8,'0', STR_PAD_LEFT);
					$name=$variant->getProduct()->getName().' - '.$variant->getVariantname()->getName().' '.$variant->getVariantvalue()->getName();
				}
			}
		}
	//dump($barcode);
	$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser()];

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
		 $repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		 $location=$request->request->get('loc',null);
		 $storeId=$request->request->get('store',null);

		 $store=$repositoryStores->findOneBy(["id"=>$storeId, "company"=>$this->getUser()->getCompany()]);
		 if($store==null) return new JsonResponse(["result"=>-4, "text"=> "Almacén no encontrado"]);

		 $variant=null;
		 if($type==1){
			 	$product=$repositoryProducts->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			 	if($product==null || $location==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
		 }else{
				$variant=$repositoryVariants->findOneBy(["id"=>$id, "deleted"=>0]);
				if($variant==null || $location==null) return new JsonResponse(["result"=>-2, "text"=> "Variante no encontrada"]);
				$product=$variant->getProduct();
				if($product->getCompany()!=$this->getUser()->getCompany()) $product=null;
			 	if($product==null || $location==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
		 }

		 $storelocation=$repositoryStoreLocations->findOneBy(["name"=>$location, "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 if($storelocation==null) return new JsonResponse(["result"=>-3, "text"=> "Ubicación no encontrada"]);

		 if($type==1)
		 	$stock=$repositoryStocks->findOneBy(["product"=>$product, "storelocation"=>$storelocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			else $stock=$repositoryStocks->findOneBy(["product"=>$product, "productvariant"=>$variant, "storelocation"=>$storelocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

		 if(!$stock){
			 //Try to find in generic ALM01 or ALM02
			 if($storeId==1)
			 	$genericALM=$repositoryStoreLocations->findOneBy(["name"=>"ALM01", "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				else $genericALM=$repositoryStoreLocations->findOneBy(["name"=>"ALM02", "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

			 if($type==1)
			 	$stock=$repositoryStocks->findOneBy(["product"=>$product, "storelocation"=>$genericALM, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				else $stock=$repositoryStocks->findOneBy(["product"=>$product, "productvariant"=>$variant, "storelocation"=>$genericALM, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);

				 //return new JsonResponse(["result"=>0, "text"=> $product->getId()]);
		 }

		 if(!$stock){
			 $stock=new ERPStocks();
			 $stock->setDateadd(new \DateTime);
			 $stock->setLastinventorydate(new \DateTime);
			 $stock->setCompany($this->getUser()->getCompany());
			 $stock->setProduct($product);
			 $stock->setProductvariant($variant);
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
	 		$repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
	 		$repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
	 		$repositoryStocks=$this->getDoctrine()->getRepository(ERPStocks::class);
	 		$repositoryVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
	 		$locationSource=$request->request->get('locsrc',null);
			$locationDestination=$request->request->get('locdst',null);
			$qty=$request->request->get('qty',null);
	 		$storeId=$request->request->get('store',null);


			$store=$repositoryStores->findOneBy(["id"=>$storeId, "company"=>$this->getUser()->getCompany()]);
			if($store==null) return new JsonResponse(["result"=>-4, "text"=> "Almacén no encontrado"]);

			$variant=null;
			if($type==1){
				 $product=$repositoryProducts->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
				 if($product==null || $locationSource==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
			}else{
				 $variant=$repositoryVariants->findOneBy(["id"=>$id, "deleted"=>0]);
				 if($variant==null || $locationSource==null) return new JsonResponse(["result"=>-2, "text"=> "Variante no encontrada"]);
				 $product=$variant->getProduct();
				 if($product->getCompany()!=$this->getUser()->getCompany()) $product=null;
				 if($product==null || $locationSource==null) return new JsonResponse(["result"=>-1, "text"=> "Producto no encontrado"]);
			}

			$storelocation=$repositoryStoreLocations->findOneBy(["name"=>$locationSource, "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			if($storelocation==null) return new JsonResponse(["result"=>-3, "text"=> "Ubicación origen no encontrada"]);

			$storedstlocation=$repositoryStoreLocations->findOneBy(["name"=>$locationDestination, "store"=>$store, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			if($storedstlocation==null) return new JsonResponse(["result"=>-4, "text"=> "Ubicación destino no encontrada"]);

			if($storelocation==$storedstlocation) return new JsonResponse(["result"=>1, "text"=> "No se han realizado cambios"]);

			if($type==1){
			 $stock=$repositoryStocks->findOneBy(["product"=>$product, "storelocation"=>$storelocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			 $stockdst=$repositoryStocks->findOneBy(["product"=>$product, "storelocation"=>$storedstlocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		  }else{
			  $stock=$repositoryStocks->findOneBy(["product"=>$product, "productvariant"=>$variant, "storelocation"=>$storelocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				$stockdst=$repositoryStocks->findOneBy(["product"=>$product, "productvariant"=>$variant, "storelocation"=>$storedstlocation, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
			}

			if($stock){

				if(!$stockdst){
						if($stock->getQuantity()-$qty==0){
							//change de location
						 	$stock->setAuthor($this->getUser());
			 		 	 	$stock->setDateupd(new \DateTime);
			 			 	$stock->setStorelocation($storedstlocation);

							$StockHistory= new ERPStockHistory();
							$StockHistory->setProduct($product);
							$StockHistory->setLocation($storelocation);
							$StockHistory->setStore($store);
							$StockHistory->setUser($this->getUser());
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
				 			$newStock->setProduct($product);
				 			$newStock->setProductvariant($variant);
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

						$StockHistory= new ERPStockHistory();
						$StockHistory->setProduct($product);
						$StockHistory->setLocation($storelocation);
						$StockHistory->setStore($store);
						$StockHistory->setUser($this->getUser());
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

}
