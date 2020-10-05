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
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPStocksController extends Controller
{
	private $module='ERP';
	private $class=ERPStocks::class;
	private $utilsClass=ERPStocksUtils::class;
	private $url="http://192.168.1.250:9000/";

    /**
     * @Route("/{_locale}/ERP/{id}/stocks", name="stocks")
     */
    public function index($id, RouterInterface $router, Request $request)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
    	$utils = new ERPStocksUtils;
  		$templateLists=$utils->formatList($id);
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Stocks.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('stocks', true, $id, $this->class, "dataStocks", ["id"=>$id, "action"=>"save"]);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/list.html.twig', [
					'listConstructor' => $templateLists,
	        'forms' => $templateForms
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/stocks/data/{id}/{action}", name="dataStocks", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Stocks.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formStocks", "modal");
		}

		/**
		 * @Route("/{_locale}/stocks/infoStocks/{id}", name="infoStocks", defaults={"id"=0})
		 */
		public function infoStocks($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$stocksReposiitory= $this->getDoctrine()->getRepository($this->class);
			$stocks=$stocksReposiitory->stocksByStores($id);
			foreach($stocks as $key=>$i){
				$stocks[$key]["Acciones"]="<button>Ir</button>";
			}

		$repositoryHistory=$this->getDoctrine()->getRepository(ERPStockHistory::class);
 		 $history=$repositoryHistory->findHistory($id);
 		 $stockHistory=Array();

 		 foreach($history as $history_line){
			 $item['Fecha']=$history_line['dateadd'];
			 $item['Código']=$history_line['product_code'];
 			 $item['Nombre']=$history_line['product_name'];
 			 $item['Ubicación']=$history_line['location'];
 			 $item['Almacén']=$history_line['store'];
 			 $item['Stock Previo']=$history_line['prevqty'];
 			 $item['Stock Final']=$history_line['newqty'];
			 $item['Usuario']=$history_line['user'];
 			 $stockHistory[]=$item;
 		 }

		 //dump($stockHistory);

			return $this->render('@ERP/infoStocks.html.twig', array(
				'stocklist'=>$stocks,
				'id'=>$id,
				'historylist' => $stockHistory
			));
		}


		/**
		 * @Route("/{_locale}/stocks/inventory", name="inventory")
		 */
		 public function inventory(RouterInterface $router,Request $request)
		 {
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		 //$this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $store=$request->query->get("store",0);
		 $category=$request->query->get("category",0);
		 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		 $utils = new $this->utilsClass();
  	 $repository = $this->getDoctrine()->getManager()->getRepository($this->class);
		 $repositoryStores = $this->getDoctrine()->getRepository(ERPStores::class);
		 $stores = $repositoryStores->findBy(["company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 $repositoryCategories = $this->getDoctrine()->getRepository(ERPCategories::class);
		 $categories = $repositoryCategories->findBy(["company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);


		 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 return $this->render('@ERP/inventory.html.twig', [
				 'controllerName' => 'ERPStocksController',
				 'interfaceName' => 'Inventario',
				 'optionSelected' => "inventory",
				 'menuOptions' =>  $menurepository->formatOptions($userdata),
				 'breadcrumb' =>  $menurepository->formatBreadcrumb('inventory'),
				 'userData' => $userdata,
				 'stores' => $stores,
				 'categories' => $categories,
				 'selectedStore' => $store,
				 'selectedCategory' => $category
				 ]);
		 } return new RedirectResponse($this->router->generate('app_login'));
		 }



		 /**
		 * @Route("/api/ERP/inventory/elements/{store}/{location}/{category}/get", name="getInventoryStocks")
		 */
		 public function getInventoryStocks($store, $location, $category, RouterInterface $router,Request $request){
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  $stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
			$stocks=$stocksRepository->findInventoryStocks($this->getUser()->getCompany(),$store,$location,$category);
			$responseStocks=Array();

			foreach($stocks as $stock){
				$item['id']=$stock['id'];
			  $item['product_code']=$stock['product_code'];
		    $item['product_name']=$stock['product_name'];
		    $item['location']=$stock['location'];
				$item['quantity']=$stock['quantity'];
				$item['lastinventorydate']=$stock['lastinventorydate'];
		    $responseStocks[]=$item;
			}

		  return new JsonResponse(["stocks"=>$responseStocks]);

		 }


		 /**
		 * @Route("/api/ERP/inventory/{store}/locations/get", name="getInventoryLocations")
		 */
		 public function getInventoryLocations($store, RouterInterface $router,Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			 $storelocations=$storeLocationsRepository->findInventoryStoreLocations($this->getUser()->getCompany(),$store);
		   $responseStoreLocations=Array();

			foreach($storelocations as $storelocation){
				$item['id']=$storelocation['id'];
				$item['name']=$storelocation['name'];
				$responseStoreLocations[]=$item;
			}

			return new JsonResponse(["storelocations"=>$responseStoreLocations]);

		 }




		/**
		 * @Route("/api/ERP/inventory/save", name="saveInventoryStock")
		 */
		 public function saveInventoryStock(RouterInterface $router,Request $request){
			  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

			  $stock_object=json_decode($request->getContent());
				$repositoryProducts=$this->getDoctrine()->getRepository(ERPProducts::class);
				$product=$repositoryProducts->findOneBy(["code"=>$stock_object->product_code, "company"=>$this->getUser()->getCompany()]);
				$repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
				$storelocation=$repositoryStoreLocations->findOneBy(["name"=>$stock_object->location, "company"=>$this->getUser()->getCompany()]);
				$repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
				$store=$repositoryStores->findOneBy(["id"=>$storelocation->getStore(), "company"=>$this->getUser()->getCompany()]);

				$StockHistory= new ERPStockHistory();
				$StockHistory->setProduct($product);
				$StockHistory->setLocation($storelocation);
				$StockHistory->setStore($store);
				$StockHistory->setUser($this->getUser());
				$StockHistory->setPreviousqty($stock_object->prevqty);
				$StockHistory->setNewqty($stock_object->nextqty);
				$StockHistory->setActive(1);
				$StockHistory->setDeleted(0);
				$StockHistory->setDateupd(new \DateTime());
				$StockHistory->setDateadd(new \DateTime());
				$manager=$this->getDoctrine()->getManager();
				$manager->persist($StockHistory);
				$manager->flush();

				$repository=$this->getDoctrine()->getRepository($this->class);
				$stock=$repository->findOneBy(["id"=>$stock_object->id, "company"=>$this->getUser()->getCompany()]);
				if($stock){
					$datetime=new \DateTime();
					$stock->setQuantity($stock_object->nextqty);
					$stock->setLastinventorydate($datetime);
					$manager=$this->getDoctrine()->getManager();
					$manager->persist($stock);
					$manager->flush();
					return new JsonResponse(["result"=>1]);
				}
				else return new JsonResponse(["result"=>-1]);
		}

		/**
		 * @Route("/api/ERP/inventory/{id}/delete", name="deleteInventoryStock")
		 */
		 public function deleteInventoryStock($id, RouterInterface $router,Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $repository=$this->getDoctrine()->getRepository($this->class);
			 $stock=$repository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
			 if($stock){
				 	$datetime=new \DateTime();
					$stock->setLastinventorydate($datetime);
					$stock->setActive(0);
					$stock->setDeleted(0);
					$manager=$this->getDoctrine()->getManager();
					$manager->persist($stock);
					$manager->flush();
					return new JsonResponse(["result"=>1]);
			 }

			else return new JsonResponse(["result"=>-1]);
		}



		/**
		* @Route("/api/global/stock/{id}/history", name="getStockHistory")
		*/
		public function getStockHistory($id){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $repositoryHistory=$this->getDoctrine()->getRepository(ERPStockHistory::class);
		 $history=$repositoryHistory->findHistory($id);
		 $responseHistory=Array();

		 foreach($history as $history_line){
			 $item['product_code']=$history_line['product_code'];
			 $item['product_name']=$history_line['product_name'];
			 $item['location']=$history_line['location'];
			 $item['store']=$history_line['store'];
			 $item['prevqty']=$history_line['prevqty'];
			 $item['newqty']=$history_line['newqty'];
			 $item['dateadd']=$history_line['dateadd'];
			 $responseHistory[]=$item;
		 }

		 return new JsonResponse(["history"=>$responseHistory]);

		}


		/*AÑADIMOS RUTAS PARA APP EN LAS PDAs*/

		/**
 	 * @Route("/api/ERP/inventory/elements/{location}/get", name="getInventoryLocation")
 	 */
 	 public function getInventoryLocation($location, RouterInterface $router,Request $request){
 		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		$stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
 		$stocks=$stocksRepository->findInventoryStockByLocation($this->getUser()->getCompany(),$location);
 		$responseStocks=Array();

 		foreach($stocks as $stock){
 			$item['id']=$stock['id'];
 			$item['product_code']=$stock['product_code'];
 			$item['product_name']=$stock['product_name'];
 			$item['location']=$stock['location'];
 			$item['quantity']=$stock['quantity'];
 			$item['lastinventorydate']=$stock['lastinventorydate'];
 			$responseStocks[]=$item;
 		}

 		return new JsonResponse(["stocks"=>$responseStocks]);

 	 }


	 /**
		* @Route("/api/ERP/inventory/{location}/{product}/{qty}/saveStock", name="saveProductStock")
		*/
		public function saveProductStock($location, $product, $qty, RouterInterface $router,Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

			 $repositoryProducts=$this->getDoctrine()->getRepository(ERPProducts::class);
			 $product_obj=$repositoryProducts->findOneBy(["code"=>$product, "company"=>$this->getUser()->getCompany()]);
			 $repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			 $storelocation=$repositoryStoreLocations->findOneBy(["name"=>$location, "company"=>$this->getUser()->getCompany()]);
			 $repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
			 $store=$repositoryStores->findOneBy(["id"=>$storelocation->getStore(), "company"=>$this->getUser()->getCompany()]);
			 $repository=$this->getDoctrine()->getRepository($this->class);
			 $stock=$repository->findOneBy(["storelocation"=>$storelocation,"product"=>$product_obj, "company"=>$this->getUser()->getCompany()]);

			 $prev_stock=$stock->getQuantity();
			 $new_stock=$qty;

			 if($prev_stock!=$new_stock){

				 $StockHistory= new ERPStockHistory();
				 $StockHistory->setProduct($product_obj);
				 $StockHistory->setLocation($storelocation);
				 $StockHistory->setStore($store);
				 $StockHistory->setUser($this->getUser());
				 $StockHistory->setPreviousqty($prev_stock);
				 $StockHistory->setNewqty($new_stock);
				 $StockHistory->setActive(1);
				 $StockHistory->setDeleted(0);
				 $StockHistory->setDateupd(new \DateTime());
				 $StockHistory->setDateadd(new \DateTime());
				 $manager=$this->getDoctrine()->getManager();
				 $manager->persist($StockHistory);
				 $manager->flush();


				 if($stock){
				 	$datetime=new \DateTime();
				 	$stock->setQuantity($new_stock);
				 	$stock->setLastinventorydate($datetime);
				 	$manager=$this->getDoctrine()->getManager();
				 	$manager->persist($stock);
				 	$manager->flush();
				//	return new JsonResponse(["result"=>1]);

				 }
				// else return new JsonResponse(["result"=>-1]);
			 }


			 $total_stock=$repository->findStockByProductStore($product_obj->getId(),$store->getId());
			 /*Obtenemos el stock que tiene en estos momentos el producto en Navision*/
			 $json=file_get_contents($this->url.'navisionExport/axiom/app/do-NAVISION-getProductStock.php?code='.$product_obj->getCode().'&store='.$store->getCode());
			 $objects=json_decode($json, true);
			 $navision_stock=$objects[0]["stock"];

			 if($total_stock>$navision_stock)
			 {
				 $new_navision_stock=$total_stock-$navision_stock;
			//	 $json=file_get_contents($this->url.'navisionExport/axiom/app/do-NAVISION-setProductStock.php?code='.$product_obj->getCode().'&store='.$store->getCode().'$qty='.$new_navision_stock.'&type=2');

			 }
			 else if($total_stock<$navision_stock){
				 	$new_navision_stock=$navision_stock-$total_stock;
			//	 $json=file_get_contents($this->url.'navisionExport/axiom/app/do-NAVISION-setProductStock.php?code='.$product_obj->getCode().'&store='.$store->getCode().'$qty='.$new_navision_stock.'&type=3');

			 }

			return new JsonResponse(["result"=>1]);
	 }



    /**
    * @Route("/api/global/stock/{id}/get", name="getStock")
    */
    public function getStock($id){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $stock = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$stock) {
            throw $this->createNotFoundException('No currency found for id '.$id );
      }
          return new JsonResponse($stock->encodeJson());
    }

  /**
   * @Route("/api/stock/{id}/list", name="stocklist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
		$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product = $productRepository->find($id);
		$locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStocks::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Stocks.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPStocks::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()],["type"=>"and","column"=>"product", "value"=>$product]]);
		return new JsonResponse($return);
  }



	/**
   * @Route("/api/stock/list/{id}", name="stockproductlist")
   */
  public function indexproductlist(RouterInterface $router,Request $request,$id){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStocks::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Stocks.json"),true);
		$stock=new ERPStocks();
		$stock=$this->getDoctrine()->getRepository($this->class)->findOneById($id);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPStocks::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()],["type"=>"and", "column"=>"product", "value"=>$stock->getProduct()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/stock/{id}/disable", name="disableStock")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/stock/{id}/enable", name="enableStock")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

 /**
 * @Route("/{_locale}/admin/global/stock/{id}/delete", name="deleteStock")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }


 /**
 * @Route("/api/erp/inventory/location/getproducts/{loc}", name="locationGetProduct")
 */
 public function getProduct($loc,Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $EAN13repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
	 $Productrepository=$this->getDoctrine()->getRepository(ERPProducts::class);
	 $Variantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
	 $Stocksrepository=$this->getDoctrine()->getRepository(ERPStocks::class);
	 //$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
	 $StoreLocationsrepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
	 $Storesrepository=$this->getDoctrine()->getRepository(ERPStores::class);
	 $obj=null;
	 $variant=null;

	 $location=$StoreLocationsrepository->findOneBy(["name"=>$loc, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
	 if(!$location) return new JsonResponse(["result"=>-1, "text"=>"Ubicación no encontrada"]);
	 $products=$Stocksrepository->findBy(["storelocation"=>$location, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
	 $array_products=[];
	 $result["id"]=$location->getId();
	 $result["name"]=$location->getName();
	 $result["storeId"]=$location->getStore()->getId();
	 $result["storeName"]=$location->getStore()->getName();
	 foreach($products as $item){
		 $obj=$item->getProduct();
		 $variant=$item->getProductvariant();
		 //$stocks=$Stocksrepository->findBy(["product"=>$obj, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 $eans=$EAN13repository->findBy(["product"=>$obj, "productvariant"=>$variant?$variant:null, "active"=>1, "deleted"=>0]);
		 $result_prod["id"]=$obj->getId();
		 $result_prod["code"]=$obj->getCode();
		 $result_prod["variant_id"]=$variant?$variant->getId():0;
		 $result_prod["variant_name"]=$variant?$variant->getVariantname()->getName():"";
		 $result_prod["variant_value"]=$variant?$variant->getVariantvalue()->getName():"";
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

		 /*$variants=$Variantsrepository->findBy(["product"=>$obj, "active"=>1, "deleted"=>0]);
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
		 });*/

		 /*$stock_items=[];
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
		 $result["stock"]=$stock_items;*/
		 $result_prod["active"]=$obj->getActive();
		 $array_products[]=$result_prod;
	}
	$result["products"]=$array_products;

	 return new JsonResponse($result);
 }

}
