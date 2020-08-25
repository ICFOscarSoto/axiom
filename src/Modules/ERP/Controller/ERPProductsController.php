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
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Products.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new ERPProductsUtils();
		 $manufacturerRepository= $this->getDoctrine()->getRepository(ERPManufacturers::class);
		 $manufacturer=$manufacturerRepository->findOneBy(["name"=>"Prueba"]);
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());

		 $utils->values(["manufacturer"=>$manufacturer]);
		 return $utils->make($id, $this->class, $action, "formproducts","full", "@ERP/productform.html.twig", "formProduct");

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
										["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Products data", "active"=>true, "route"=>$this->generateUrl("formInfoProduct",["id"=>$id])],
										["name" => "variants", "icon"=>"fa fa-id-card", "caption"=>"Variants", "route"=>$this->generateUrl("generictablist",["function"=>"formatListByProduct","module"=>"ERP","name"=>"ProductsVariants","id"=>$id])],
										["name" => "list",  "icon"=>"fa fa-users", "caption"=>"References", "route"=>$this->generateUrl("listEAN13",["id"=>$id])],
										["name"=>  "productPrices", "icon"=>"fa fa-money", "caption"=>"Prices","route"=>$this->generateUrl("infoProductPrices",["id"=>$id])],
										["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("infoStocks",["id"=>$id])],
										["name" => "webproduct", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("dataWebProducts",["id"=>$id])],
										["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]
										],
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
									["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Products data", "active"=>true, "route"=>$this->generateUrl("formInfoProduct",["id"=>$id])],
									["name" => "list",  "icon"=>"fa fa-users", "caption"=>"References", "route"=>$this->generateUrl("listEAN13",["id"=>$id])],
									["name"=>  "productPrices", "icon"=>"fa fa-money", "caption"=>"Prices","route"=>$this->generateUrl("infoProductPrices",["id"=>$id])],
									["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("infoStocks",["id"=>$id])],
									["name" => "webproduct", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("dataWebProducts",["id"=>$id])],
									["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]
									],
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
    * @Route("/api/erp/product/get/{id}", name="getProduct", defaults={"id"=0})
    */
    public function getProduct($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$EAN13repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
			$Stocksrepository=$this->getDoctrine()->getRepository(ERPStocks::class);
			$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$StoreLocationsrepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			$Storesrepository=$this->getDoctrine()->getRepository(ERPStores::class);
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
				$stocks=$Stocksrepository->findBy(["product"=>$obj, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
				$result["id"]=$obj->getId();
				$result["code"]=$obj->getCode();
				$result["name"]=$obj->getName();
				$result["provider"]=$obj->getSupplier()?$obj->getSupplier()->getName():"";
				$stock_items=[];
				foreach($stocks as $stock){
					$storeUser=$StoreUsersrepository->findOneBy(["user"=>$this->getUser(), "store"=>$stock->getStorelocation()->getStore(), "active"=>1, "deleted"=>0]);
					if($storeUser){
						$stock_item["id"]=$stock->getId();
						$stock_item["warehouse_code"]=$stock->getStorelocation()->getStore()->getCode();
						$stock_item["warehouse"]=$stock->getStorelocation()->getStore()->getName();
						$stock_item["warehouse_preferential"]=$storeUser->getPreferential();
						$stock_item["location"]=$stock->getStorelocation()->getName();
						$stock_item["quantity"]=!$stock->getQuantity()?0:$stock->getQuantity();
						$stock_item["pendingserve"]=!$stock->getPendingserve()?0:$stock->getPendingserve();
						$stock_item["pendingreceive"]=!$stock->getPendingreceive()?0:$stock->getPendingreceive();
						$stock_item["minstock"]=!$stock->getMinstock()?0:$stock->getMinstock();
						$stock_items[]=$stock_item;
					}
				}
				$result["stock"]=$stock_items;
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
			$image=["large"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"large", "id"=>$id, "number"=>0 )),
						 "thumb"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"thumb", "id"=>$id, "number"=>0 )),
						 "medium"=>$this->generateUrl('getImage', array('type' => 'products', "size"=>"medium", "id"=>$id, "number"=>0 ))];
			$images[]=$image;
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
			return new JsonResponse(["images"=>$images]);
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
 * @Route("/{_locale}/admin/ERP/product/printLabel/{id}", name="printeanlabel")
 */
 public function printeanlabel($id){
	 $repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
	 $ean=$repository->findOneBy(["id"=>$id]);
	 $code="";
	 $barcode="0000000000000";
	 $name="";
	 if($ean){
		 $code=$ean->getProduct()->getCode();
		 $barcode=$ean->getName();
		 $name=$ean->getProduct()->getName();
	 }
	 $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "code"=>$code, "barcode"=>$barcode, "name"=>$name, "user"=>$this->getUser()];
	 $reportsUtils = new ERPEan13Reports();
	 $pdf=$reportsUtils->create($params);
	 return new Response("", 200, array('Content-Type' => 'application/pdf'));
 }

 /**
 * @Route("/{_locale}/admin/ERP/product/printLabel/{id}/{printer}/{copies}/{type}", name="printDirectly", defaults={"copies"=1,"type"=1})
 */
 public function printDirectly($id, $printer, $copies, $type){
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
			$barcode='p#'.str_pad($product->getId(),9,'0', STR_PAD_LEFT);
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
					$barcode='v#'.str_pad($variant->getId(),9,'0', STR_PAD_LEFT);
					$name=$variant->getProduct()->getName().' - '.$variant->getVariantname()->getName().' '.$variant->getVariantname()->getName();
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

}
