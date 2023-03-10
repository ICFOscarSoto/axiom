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
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStoresManagersProducts;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\Security\Utils\SecurityUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Helpers\XLSXWriter\XLSXWriter;

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
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
    	$utils = new ERPStocksUtils;
  		$templateLists=$utils->formatListByProduct($id);
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
		 $classUtils=new ERPStocksUtils();
		 $utils = new GlobaleFormUtils();
	 	 $repository=$this->getDoctrine()->getRepository($this->class);
		 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
		 $referer = $request->headers->get('referer'); // get the referer, it can be empty!
		 $template = null;
     if (!\is_string($referer) || !$referer) {
       $template=dirname(__FILE__)."/../Forms/Stocks.json";
			 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
			 $excludedForm=$classUtils->getExcludedForm($params);
     }else{
			 $refererPathInfo = Request::create($referer)->getPathInfo();
			 $routeInfos = $this->get('router')->match($refererPathInfo);
			 $refererRoute = $routeInfos['_route'] ?? '';
			 if ($refererRoute=='formStoresManagers'){
			 	$template=dirname(__FILE__)."/../Forms/StocksManagers.json";
				$excludedForm=['storelocation','productvariant'];
			 }else{
			  $template=dirname(__FILE__)."/../Forms/Stocks.json";
				$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
				$excludedForm=$classUtils->getExcludedForm($params);
			 }
		 }

		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$excludedForm);
		 $form = $utils->make($id, $this->class, $action, "formStocks", "modal");
		 return $form;
		}

		/**
		 * @Route("/{_locale}/stocks/stock/{id}", name="istock", defaults={"id"=0})
		 */
		public function istock($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$stores_usersRepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$stores_by_user=$stores_usersRepository->getStoreByUser($this->getUser()->getId());
			$stocksRepository= $this->getDoctrine()->getRepository($this->class);
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$product=$productRepository->findOneBy(["id"=>$id]);
			$isgrouped=$product->getGrouped();
			if($isgrouped){
				$variants=$productRepository->getVariants($product->getId());
				$store_locations=array();
				foreach($stores_by_user as $store){
					foreach ($variants as $variant) {
						$locations_array=$stocksRepository->getStocksByProduct($product->getId(),$variant["id"],$store["id"]);
						$item=[];
						foreach($locations_array as $location){
								$item[]=$location;
						}
						$aux2["name"]=$variant["name"];
						$aux2["type"]=$variant["type"];
						$stocks2=$stocksRepository->findStockByProductVariantStore($product->getId(),$variant["id"],$store["id"]);
						$aux2["total"]=$stocks2["quantity"];
						$aux2["pendingreceive"]= $stocks2["pendingreceive"];
						$aux2["locations"]=$item;
						$item_variants[]=$aux2;
						$item=[];
						$aux2=[];
					}
					$aux["name"]=$store["name"];
					$stocks=$stocksRepository->findStockByProductStore($product->getId(),$store["id"]);
					$aux["total"]=$stocks["quantity"];
					$aux["pendingreceive"]= $stocks["pendingreceive"];
					$aux["preferential"]=$store["preferential"];
					$aux["variants"]=$item_variants;
					$store_locations[]=$aux;
					$item_variants=[];
					$aux=[];
				}

				$stocks=$stocksRepository->stocksByStores($id);
 			  $repositoryHistory=$this->getDoctrine()->getRepository(ERPStocksHistory::class);
				$history=$repositoryHistory->findHistory($id);
				$stockHistory=Array();

					foreach($history as $history_line){
						$item['Fecha']=$history_line['dateadd'];
						//$item['C??digo']=$history_line['product_code'];
						//$item['Nombre']=$history_line['product_name'];
						$item['Operacion']=$history_line['numOperation'];
						$item['Tipo']=$history_line['type'];
						$item['Cantidad']=$history_line['quantity'];
						$item['Ubicaci??n']=$history_line['location'];
						$item['Almac??n']=$history_line['store'];
						$item['Comentario']=$history_line['comment'];
						$item['Variante']=$history_line['variant_name'];
						$item['Stock Previo']=$history_line['prevqty'];
						$item['Stock Final']=$history_line['newqty'];
						$item['Usuario']=$history_line['user'];
						$stockHistory[]=$item;
					}

					return $this->render('@ERP/stocks.html.twig', array(
										'storelist'=>$store_locations,
										'id'=>$id,
										'variantes' => $variants,
										'historylist' => $stockHistory
					));
			}
			else{
						$store_locations=array();
						foreach($stores_by_user as $store){
								$item=[];
								$locations_array=$stocksRepository->getStocksByProduct($product->getId(),null,$store["id"]);
										foreach($locations_array as $location){
												$item[]=$location;
												//$item["location"]=$item2;
											}
								$aux["name"]=$store["name"];
							 	$stocks=$stocksRepository->findStockByProductStore($product->getId(),$store["id"]);
								$aux["total"]=$stocks["quantity"];
								$aux["pendingreceive"]= $stocks["pendingreceive"];
								$aux["preferential"]=$store["preferential"];
								$aux["locations"]=$item;
								$store_locations[]=$aux;
								$locations_array=[];
								$item=[];
								$aux=[];
							}

					$stocks=$stocksRepository->stocksByStores($id);
	 			  $repositoryHistory=$this->getDoctrine()->getRepository(ERPStocksHistory::class);
					$history=$repositoryHistory->findHistory($id);
					$stockHistory=Array();

					foreach($history as $history_line){
									 $item['Fecha']=$history_line['dateadd'];
									 //$item['C??digo']=$history_line['product_code'];
						 			 //$item['Nombre']=$history_line['product_name'];
									 $item['Operacion']=$history_line['numOperation'];
									 $item['Tipo']=$history_line['type'];
									 $item['Cantidad']=$history_line['quantity'];
						 			 $item['Ubicaci??n']=$history_line['location'];
						 			 $item['Almac??n']=$history_line['store'];
									 $item['Comentario']=$history_line['comment'];
						 			 $item['Stock Previo']=$history_line['prevqty'];
						 			 $item['Stock Final']=$history_line['newqty'];
									 $item['Usuario']=$history_line['user'];
						 			 $stockHistory[]=$item;
					}

					return $this->render('@ERP/stocks.html.twig', array(
										'storelist'=>$store_locations,
										'id'=>$id,
										'variantes' => null,
										'historylist' => $stockHistory
					));
				}
		}

		/**
		 * @Route("/{_locale}/stocks/inventory", name="inventory")
		 */
		 public function inventory(RouterInterface $router,Request $request)
		 {
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
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
				$repositoryProductsVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
				$product=$repositoryProducts->findOneBy(["code"=>$stock_object->product_code, "company"=>$this->getUser()->getCompany()]);
				$productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product, "variant"=>null]);
				$repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
				$storelocation=$repositoryStoreLocations->findOneBy(["name"=>$stock_object->location, "company"=>$this->getUser()->getCompany()]);
				$repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
				$store=$repositoryStores->findOneBy(["id"=>$storelocation->getStore(), "company"=>$this->getUser()->getCompany()]);
				$StockHistory= new ERPStocksHistory();
        $StockHistory->setProductcode($productvariant->getProduct()->getCode());
        $StockHistory->setProductname($productvariant->getProduct()->getName());
				$StockHistory->setProductVariant($productvariant);
				$StockHistory->setLocation($storelocation);
				$StockHistory->setUser($this->getUser());
				$stockHistory->setCompany($this->getUser()->getCompany());
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
		 $repositoryHistory=$this->getDoctrine()->getRepository(ERPStocksHistory::class);
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


		/**
		 * @Route("/es/stock/stockHistory/list", name="stockHistorylist")
		 */
		public function stockHistory(RouterInterface $router,Request $request){
			$managerRepository=$this->getDoctrine()->getRepository(ERPStoresManagers::class);
			$productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$managerId=$managerRepository->find(1);
			$products=$productRepository->getProductsByManager($managerId->getId());

			$stores_usersRepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$stocksRepository= $this->getDoctrine()->getRepository($this->class);
			$stores_by_user=$stores_usersRepository->getStoreByUser($this->getUser()->getId());
			$store_locations=array();
			foreach($stores_by_user as $store){
								$item=[];
								$aux["name"]=$store["name"];
								$aux["total"]=0;
								$aux["preferential"]=$store["preferential"];
								$aux["locations"]=$item;
								$store_locations[]=$aux;
								$locations_array=[];
								$item=[];
								$aux=[];
			}

			$stockHistory=Array();
			foreach ($products as $product) {
				$repositoryHistory=$this->getDoctrine()->getRepository(ERPStocksHistory::class);
				$history=$repositoryHistory->findAllHistory($product["product_id"]);

				foreach($history as $history_line){
								 $item['Fecha']=$history_line['dateadd'];
								 $item['C??digo']=$history_line['product_code'];
								 $item['Nombre']=$history_line['product_name'];
								 $item['Operacion']=$history_line['numOperation'];
								 $item['Tipo']=$history_line['type'];
								 $item['Cantidad']=$history_line['quantity'];
								 $item['Ubicaci??n']=$history_line['location'];
								 $item['Almac??n']=$history_line['store'];
								 $item['Comentario']=$history_line['comment'];
								 $item['Stock Previo']=$history_line['prevqty'];
								 $item['Stock Final']=$history_line['newqty'];
								 $item['Usuario']=$history_line['user'];
								 $stockHistory[]=$item;
				}
			}

			return $this->render('@ERP/stocks.html.twig', array(
								'storelist'=>$store_locations,
								'id'=>null,
								'variantes' => null,
								'historylist' => $stockHistory
			));
		}

		/*A??ADIMOS RUTAS PARA APP EN LAS PDAs*/

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
		* @Route("/api/ERP/inventory/create/{location}/{product}/{variant}/{qty}/{type}", name="inventoryCreate")
		*/
		public function inventoryCreate($location, $product, $variant, $qty, $type, RouterInterface $router,Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 //TODO: Comprobar que el usuario tiene permisos para hacer inventarios

			 $repositoryProducts=$this->getDoctrine()->getRepository(ERPProducts::class);
			 $repositoryProductsVariants=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			 $repositoryStoreLocations=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			 $repositoryStores=$this->getDoctrine()->getRepository(ERPStores::class);
			 $repositoryType=$this->getDoctrine()->getRepository(ERPTypesMovements::class);
			 $repository=$this->getDoctrine()->getRepository($this->class);
			 $manager=$this->getDoctrine()->getManager();
			 //TODO: Comprobar si el usuario esta asignado al almacen


			 $product_obj=$repositoryProducts->findOneBy(["id"=>$product, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			 if(!$product_obj) return new JsonResponse(["result"=>-2, "text"=>"Producto no encontrado"]);

			 $productvariant=$repositoryProductsVariants->findOneBy(["product"=>$product, "variant"=>$variant, "deleted"=>0]);

			 $storelocation=$repositoryStoreLocations->findOneBy(["id"=>$location, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			 if(!$storelocation) return new JsonResponse(["result"=>-3, "text"=>"Ubicaci??n no encontrada"]);
			 $store=$repositoryStores->findOneBy(["id"=>$storelocation->getStore(), "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
			 if(!$store) return new JsonResponse(["result"=>-4, "text"=>"Almac??n no encontrado"]);
			 $movementType=$repositoryType->findOneBy(["id"=>$type, "active"=>1, "deleted"=>0]);
			 if(!$movementType) return new JsonResponse(["result"=>-5, "text"=>"Tipo de movimiento no soportado"]);
			 $stock=$repository->findOneBy(["storelocation"=>$storelocation,"productvariant"=>$productvariant, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);

			 if(!$stock){
			 	$stock = new ERPStocks();
			 	$stock->setProductVariant($productvariant);
			 	$stock->setCompany($this->getUser()->getCompany());
			 	$stock->setStorelocation($storelocation);
			 	$stock->setDateadd(new \DateTime());
			 	$stock->setActive(1);
			 	$stock->setDeleted(0);
			 	$stock->setAuthor($this->getUser());
				$prev_stock=0;
			}else{
				$prev_stock=$stock->getQuantity();
			}
			  $stock->setQuantity($qty);
			  $stock->setLastinventorydate(new \DateTime());
			  $stock->setDateupd(new \DateTime());
			  $manager->persist($stock);
			  $manager->flush();

		 if($prev_stock!=$qty){
				 $StockHistory= new ERPStocksHistory();
         $StockHistory->setProductcode($productvariant->getProduct()->getCode());
         $StockHistory->setProductname($productvariant->getProduct()->getName());
				 $StockHistory->setProductVariant($productvariant);
				 $StockHistory->setLocation($storelocation);
				 $StockHistory->setUser($this->getUser());
				 $StockHistory->setCompany($this->getUser()->getCompany());
				 $StockHistory->setPreviousqty($prev_stock);
				 $StockHistory->setNewqty($qty);
				 $StockHistory->setActive(1);
				 $StockHistory->setDeleted(0);
				 $StockHistory->setDateupd(new \DateTime());
				 $StockHistory->setDateadd(new \DateTime());
				 $StockHistory->setProductvariant($variant);
				 $StockHistory->setType($movementType);
				 $StockHistory->setComment(null);
				 $StockHistory->setNumOperation(null);
				 $StockHistory->setQuantity(null);
				 $manager->persist($StockHistory);
				 $manager->flush();
			 }

			 /*CALCULAMOS EL STOCK TOTAL DEL ALMACEN EN TODAS LAS UBICACIONES PARA REALIZAR EL AJUSTE EN NAVISION*/
			 /*Obtenemos el stock que tiene en estos momentos el producto en Navision*/

			 //TODO: En principio dejamos esta parte pendiente de valorar y terminar si procede
			 /*$total_stock=$repository->findStockByProductStore($product_obj->getId(),$store->getId());
			 $json=file_get_contents($this->url.'navisionExport/axiom/app/do-NAVISION-getProductStock.php?code='.$product_obj->getCode().'&store='.$store->getCode());
			 $objects=json_decode($json, true);
			 $navision_stock=$objects[0]["stock"];
			 if($total_stock>$navision_stock){
				 $new_navision_stock=$total_stock-$navision_stock;
				 //$json=file_get_contents($this->url.'navisionExport/axiom/app/do-NAVISION-setProductStock.php?code='.$product_obj->getCode().'&store='.$store->getCode().'$qty='.$new_navision_stock.'&type=2');
			 }
			 else if($total_stock<$navision_stock){
				 	$new_navision_stock=$navision_stock-$total_stock;
					//$json=file_get_contents($this->url.'navisionExport/axiom/app/do-NAVISION-setProductStock.php?code='.$product_obj->getCode().'&store='.$store->getCode().'$qty='.$new_navision_stock.'&type=3');
			 }*/
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
   * @Route("/api/stocksmanaged/{product}/{storemanager}/list", name="stocksmanagedlist")
   */
  public function stocksmanagedlist($product,$storemanager,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
		$productRepository=$this->getDoctrine()->getRepository(ERPStoresManagersProducts::class);
    $product = $productRepository->find($product)->getProductvariant();
		$locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStocks::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksManaged.json"),true);
		//$user,$repository,$request,$manager,$listFields,$classname,$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null)
    $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields, ERPStocks::class,
																				['str.name'=>'store','stk.quantity'=>'quantity', 'stk.pendingreceive'=>'pendingreceive', 'stk.minstock'=>'minstock',
																				'stk.maxstock'=>'maxstock', 'stk.lastinventorydate'=>'lastinventorydate', 'stk.dateupd'=>'dateupd', 'stk.id'=>'id'],
																				'erpstocks stk
																				LEFT JOIN erpstore_locations sl ON sl.id=stk.storelocation_id
																				LEFT JOIN erpstores str ON str.id=sl.store_id',
																				'str.managed_by_id='.$storemanager.' and stk.deleted=0 and stk.productvariant_id='.$product->getId(),
																				null,
																				'stk.id',
																			);
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
 public function disable($id){
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
	 $stockDeleted;
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
	 if(!$location) return new JsonResponse(["result"=>-1, "text"=>"Ubicaci??n no encontrada"]);
	 $products=$Stocksrepository->findBy(["storelocation"=>$location, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
	 $array_products=[];
	 $result["id"]=$location->getId();
	 $result["name"]=$location->getName();
	 $result["storeId"]=$location->getStore()->getId();
	 $result["storeName"]=$location->getStore()->getName();
	 foreach($products as $item){
		 $obj=$item->getProduct();
		 $variant=$item->getProductVariant();
		 //$stocks=$Stocksrepository->findBy(["product"=>$obj, "company"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
		 $eans=$EAN13repository->findBy(["product"=>$obj, "productvariant"=>$variant?$variant:null, "active"=>1, "deleted"=>0]);
		 $result_prod["id"]=$item->getId();
		 $result_prod["id_product"]=$obj->getId();
		 $result_prod["code"]=$obj->getCode();
		 $result_prod["variant_id"]=$variant?$variant->getId():0;
		 $result_prod["variant_name"]=$variant?$variant->getVariant()-getVarianttype()->getName():"";
		 $result_prod["variant_value"]=$variant?$variant->getVariant()->getName():"";
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
			 $variant_item["name"]=$variant->getVariant()->getVarianttype()->getName();
			 $variant_item["value"]=$variant->getVariant()->getName();
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

 /**
	* @Route("/{_locale}/updateStocksManageds", name="updateStocksManageds")
	* store => codigo del almac??n en AXIOM
	* date => fecha de inicio de los movimientos en formato dd/mm/aaaa
	* date2 => fecha de inicio de los movimientos en formato aaaa/mm/dd
	*/
	 public function updateStocksManageds(RouterInterface $router,Request $request){
		 $storeName=$request->query->get('store',null);
		 $date=$request->query->get('date',null);
		 $date2=$request->query->get('date2',null);
		 $usersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		 $user=$usersRepository->findOneBy(["email"=>"oscar.soto@ferreteriacampollano.com", "deleted"=>0]);
		 $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $productVariantRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		 $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
		 $storeRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		 $stockRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
		 $storeLocation=null;
		 $stocks = array();
		 if ($storeName=='ALI') {
			 $storeLocation=$storeLocationsRepository->findOneBy(["name"=>"GESTOR ALI"]);
			 $store=$storeRepository->findOneBy(["code"=>"GESTOR ALI"]);
			 $stocks=$stockRepository->getOperations("GESTOR ALI",$date2);
		 }
		 else {
			 $storeLocation=$storeLocationsRepository->findOneBy(["name"=>$storeName]);
			 $store=$storeRepository->findOneBy(["code"=>$storeName]);
			 $stocks=$stockRepository->getOperations($storeName,$date2);
		 }
		 foreach($stocks as $istock){
			 $product=$productRepository->findOneBy(["code"=>$istock["code"]]);
			 $productvariant=$productVariantRepository->findOneBy(["product"=>$product, "variant"=>null]);
			 $stock=$stockRepository->findOneBy(["storelocation"=>$storeLocation, "productvariant"=>$productvariant]);
			 if ($stock==NULL) continue;
			 $quantity=$stock->getQuantity()-$istock["vendido"];
			 $stockHistory=new ERPStocksHistory();
			 $stockHistory->setProductcode($productvariant->getProduct()->getCode());
			 $stockHistory->setProductname($productvariant->getProduct()->getName());
			 $stockHistory->setProductVariant($productvariant);
			 $stockHistory->setLocation($storeLocation);
			 $stockHistory->setUser($user);
			 $stockHistory->setPreviousqty($stock->getQuantity());
			 $stockHistory->setNewqty($quantity);
			 $stockHistory->setDateadd(new \Datetime());
			 $stockHistory->setDateupd(new \Datetime());
			 $stockHistory->setActive(true);
			 $stockHistory->setDeleted(false);
			 $this->getDoctrine()->getManager()->persist($stockHistory);
			 $stock->setQuantity($quantity);
			 $this->getDoctrine()->getManager()->persist($stock);
		 }
		 $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getTransfersByStore.php?store='.$storeName.'&date='.$date);
		 $objects=json_decode($json, true);
		 $objects=$objects[0]["class"];
		 foreach ($objects as $object){
			 $product=$productRepository->findOneBy(["code"=>$object["code"]]);
			 $productvariant=$productVariantRepository->findOneBy(["product"=>$product, "variant"=>null]);
			 $stock=$stockRepository->findOneBy(["storelocation"=>$storeLocation, "productvariant"=>$productvariant]);
			 if ($stock!=null){
			 $quantity=$stock->getQuantity()+$object["stock"];
			 $stockHistory=new ERPStocksHistory();
			 $stockHistory->setProductcode($productvariant->getProduct()->getCode());
			 $stockHistory->setProductname($productvariant->getProduct()->getName());
			 $stockHistory->setProductVariant($productvariant);
			 $stockHistory->setLocation($storeLocation);
			 $stockHistory->setUser($user);
			 $stockHistory->setPreviousqty($stock->getQuantity());
			 $stockHistory->setNewqty($quantity);
			 $stockHistory->setDateadd(new \Datetime());
			 $stockHistory->setDateupd(new \Datetime());
			 $stockHistory->setActive(true);
			 $stockHistory->setDeleted(false);
			 $this->getDoctrine()->getManager()->persist($stockHistory);
			 $stock->setQuantity($quantity);
			 $this->getDoctrine()->getManager()->persist($stock);}
		 }

		 $this->getDoctrine()->getManager()->flush();
		 return new JsonResponse(["result"=>1, "text"=>"Se ha ajustado el stock"]);
	 }

	 /**
	 * @Route("/{_locale}/erp/StocksHistory/exportHistory", name="exportHistory")
	 */
	 public function exportHistory(Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $historyRepository=$this->getDoctrine()->getRepository(ERPStocksHistory::class);
		 $userRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		 $typeRepository=$this->getDoctrine()->getRepository(ERPTypesMovements::class);
		 $ids=$request->query->get('ids');
		 $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/temp/'.$this->getUser()->getId().'/';
		 if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
				 mkdir($uploadDir, 0775, true);
		 }
		 $filename = date("YmdHis").'_'.md5(uniqid()).'.xlsx';
		 $errorstyle[] = array('fill'=>"#AA0000");

		 $writer = new XLSXWriter();
		 $header = array("string","string","string","string");
		 $writer->setAuthor($this->getUser()->getName().' '.$this->getUser()->getLastname());
		 $writer->writeSheetHeader('Hoja1', $header, $col_options = ['suppress_row'=>true] );
		 $writer->writeSheetRow('Hoja1', ["Nombre producto", "C??digo", "Cantidad previa", "Cantidad operacion",   "Cantidad nueva", "M??quina", "Traspaso", "Usuario", "Fecha", "Tipo"]);
		 $row_number=1;
		 if($ids!=null){
			 $lines=$historyRepository->getMovements($ids);
			 foreach($lines as $line){
				 $row=[$line["productname"], $line["productcode"], $line["previousqty"], $line["quantity"],  $line["newqty"], $line["comment"],
				 				$line["num_operation"], $userRepository->findOneBy(["id"=>$line["user_id"]])->getName(), $line["date"], $typeRepository->findOneBy(["id"=>$line["type_id"]])->getName()];
				 $writer->writeSheetRow('Hoja1', $row);
				 $row_number++;
			 }
		 }

		 $writer->writeToFile($uploadDir.$filename);
		 $response = new BinaryFileResponse($uploadDir.$filename);
		 $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		 $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'history.xlsx');
		 return $response;
	 }

}
