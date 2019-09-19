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
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;

class ERPProductsController extends Controller
{
	private $class=ERPProducts::class;
	//private $utilsClass=ERPProductsUtils::class;
    /**
     * @Route("/{_locale}/admin/global/products", name="products")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
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
  				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists
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
		 $obj=new ERPProducts();
		 $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $obj=$productRepository->find($id);
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formproducts","full", "@ERP/productform.html.twig", "formProduct");

		}


		/**
		 * @Route("/{_locale}/admin/global/product/form/{id}", name="formProduct", defaults={"id"=0})
		 */
		 public function formProduct($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/Products.json";
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('products');
			array_push($breadcrumb, $new_breadcrumb);
			$productRepository=$this->getDoctrine()->getRepository($this->class);
			$obj = $productRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$product_name=$obj?$obj->getName():'';
			return $this->render('@Globale/generictabform.html.twig', array(
								'entity_name' => $product_name,
								'controllerName' => 'ProductsController',
								'interfaceName' => 'Productos',
								'optionSelected' => 'products',
								'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
								'breadcrumb' => $breadcrumb,
								'userData' => $userdata,
								'id' => $id,
								'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
								'tabs' => [
									["name" => "data", "icon"=>"fa fa-id-card", "caption"=>"Products data", "active"=>true, "route"=>$this->generateUrl("formInfoProduct",["id"=>$id])],
									["name" => "webproduct", "icon"=>"fa fa-id-card", "caption"=>"Web", "route"=>$this->generateUrl("dataWebProducts",["id"=>$id])],
									["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Files", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"products"])]
									],

								//	["name" => "stocks", "icon"=>"fa fa-id-card", "caption"=>"Stocks", "route"=>$this->generateUrl("stocks",["id"=>$id])]],

									'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																			["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
									'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
												 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]

				));
		}


		/**
		 * @Route("/{_locale}/products/info/{id}", name="formInfoProduct", defaults={"id"=0})
		 */
		public function formInfoProduct($id,  Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('products');
			array_push($breadcrumb, $new_breadcrumb);
			$template=dirname(__FILE__)."/../Forms/Products.json";
			$formUtils = new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
			$listEAN13 = new ERPEAN13Utils();
			$formUtilsEAN = new GlobaleFormUtils();
			$formUtilsEAN->initialize($this->getUser(), new ERPEAN13(), dirname(__FILE__)."/../Forms/EAN13.json", $request, $this, $this->getDoctrine());
			$forms[]=$formUtilsEAN->formatForm('EAN13', true, null, ERPEAN13::class);
			$listReferences = new ERPReferencesUtils();
			$formUtilsReferences = new GlobaleFormUtils();
			$formUtilsReferences->initialize($this->getUser(), new ERPReferences(), dirname(__FILE__)."/../Forms/References.json", $request, $this, $this->getDoctrine());
			$forms[]=$formUtilsReferences->formatForm('References', true, null, ERPReferences::class);
			$listStocks = new ERPStocksUtils();
			$formUtilsStocks = new GlobaleFormUtils();
			$formUtilsStocks->initialize($this->getUser(), new ERPStocks(), dirname(__FILE__)."/../Forms/Stocks.json", $request, $this, $this->getDoctrine());
			$forms[]=$formUtilsStocks->formatForm('Stocks', true, null, ERPStocks::class);

			return $this->render('@ERP/productform.html.twig', array(
				'controllerName' => 'productsController',
				'interfaceName' => 'Productos',
				'optionSelected' => 'products',
				'userData' => $userdata,
				'id' => $id,
				'id_object' => $id,
				'form' => $formUtils->formatForm('products', true, $id, $this->class, "dataProduct"),
				'listEAN13' => $listEAN13->formatListByProduct($id),
				'listReferences' => $listReferences->formatListByProduct($id),
				'listStocks' => $listStocks->formatListByProduct($id),
				'forms' => $forms
			));

		}

    /**
    * @Route("/api/global/product/{id}/get", name="getProduct")
    */
    public function getProduct($id){
			$obj = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$obj) {
        throw $this->createNotFoundException('No product found for id '.$id );
			}
			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
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
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, Products::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
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

}
