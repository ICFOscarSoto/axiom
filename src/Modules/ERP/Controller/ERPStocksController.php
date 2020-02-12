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
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProducts;
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
			$stocksReposiitory= $this->getDoctrine()->getRepository($this->class);
			$stocks=$stocksReposiitory->stocksByStores($id);
			foreach($stocks as $key=>$item){
				$stocks[$key]["Acciones"]="<button>Ir</button>";
			}
			return $this->render('@ERP/infoStocks.html.twig', array(
				'stocklist'=>$stocks
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
		 * @Route("/api/ERP/inventory/elements/{store}/{category}/get", name="getInventoryStocks")
		 */
		 public function getInventoryStocks($store, $category, RouterInterface $router,Request $request){
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		  $stocksRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
	//		$categoriesRepository=$this->getDoctrine()->getRepository(ERPCategories::class);
		//	$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		//	$store=$storesRepository->findBy(["id"=>$store, "active"=>1, "deleted"=>0]);
		//  $category = $categoriesRepository->findBy(["id"=>$category, "active"=>1, "deleted"=>0]);

			$stocks=$stocksRepository->findInventoryStocks($this->getUser()->getCompany(),$store,$category);
			$responseStocks=Array();

			foreach($stocks as $stock){
				$item['id']=$stock['id'];
		    $item['product']=$stock['product'];
		    $item['location']=$stock['location'];
				$item['pendingreceive']=$stock['pendingreceive'];
				$item['pendingserve']=$stock['pendingserve'];
				$item['quantity']=$stock['quantity'];
				$item['lastinventorydate']=$stock['lastinventorydate'];
		    $responseStocks[]=$item;
			}

		  return new JsonResponse(["stocks"=>$responseStocks]);

		 }


		/**
		 * @Route("/api/ERP/inventory/{id}/{qty}/save", name="saveInventoryStock")
		 */
		 public function saveInventoryStock($id, $qty, RouterInterface $router,Request $request){

			  $repository=$this->getDoctrine()->getRepository($this->class);
				$stock=$repository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
				if($stock){
					$datetime=new \DateTime();
					$stock->setQuantity($qty);
					$stock->setLastinventorydate($datetime);
					$manager=$this->getDoctrine()->getManager();
					$manager->persist($stock);
					$manager->flush();
					return new JsonResponse(["result"=>1]);
				}
				else return new JsonResponse(["result"=>1]);
		}

    /**
    * @Route("/api/global/stock/{id}/get", name="getStock")
    */
    public function getStock($id){
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

}
