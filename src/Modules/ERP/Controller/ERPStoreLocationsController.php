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
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPStoreLocationsUtils;
use App\Modules\ERP\Reports\ERPLocationsReports;

class ERPStoreLocationsController extends Controller
{
	private $class=ERPStoreLocations::class;
	private $utilsClass=ERPStoreLocationsUtils::class;

    /**
     * @Route("/{_locale}/admin/global/storelocations", name="storelocations")
     */
    public function index(RouterInterface $router,Request $request)
    {
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new $this->utilsClass();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/StoreLocations.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('storelocations', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'storelocationsController',
  				'interfaceName' => 'StoreLocations',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
	        'forms' => $templateForms
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/storelocations/data/{id}/{action}", name="dataStoreLocations", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/StoreLocations.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formStoreLocations", "modal");
		}

    /**
    * @Route("/api/global/storelocation/{id}/get", name="getStoreLocation")
    */
    public function getStoreLocation($id){
     $storelocation = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$storelocation) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($storelocation->encodeJson());
    }

  /**
   * @Route("/api/storelocation/list", name="storelocationlist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStoreLocations::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoreLocations.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPStoreLocations::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }

	/**
  * @Route("/api/ERP/locations/printLabel/{id}/{idend}", name="printLocationlabel", defaults={"id"=0, "idend"="0"})
  */
  public function printLocationlabel($id, $idend,Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 	 $repository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
	 $json=$request->query->get('json',null);
	 if($json){
		 $json=json_decode($json, true);
		 $locations=[];
		 foreach($json as $item){
			 $loc=$repository->findOneBy(["name"=>$item]);
			 $locitem["id"]=$loc->getId();
			 $locitem["name"]=$loc->getName();
			 $locitem["orientation"]=$loc->getOrientation();
			 if($loc) $locations[]=$locitem;
		 }
	 }else{
		 $locations=$repository->getLocations($id, $idend);
	 }

 	 $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "locations"=>$locations, "user"=>$this->getUser()];
 	 $reportsUtils = new ERPLocationsReports();
 	 $pdf=$reportsUtils->create($params);
 	 return new Response("", 200, array('Content-Type' => 'application/pdf'));
	 /*dump($locations);
	 return new Response('');*/
  }

	/**
  * @Route("/api/ERP/location/get", name="getLocation")
  */
  public function getLocation(Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 	 $repository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
	 $stockRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
	 $location=$repository->findOneBy(["name"=>$request->request->get('loc')]);
	 if(!$location) return new JsonResponse(["result"=>-1]);
	 $products=$stockRepository->findBy(["storelocation"=>$location, "active"=>1, "deleted"=>0]);
	 $result["id"]=$location->getId();
	 $result["name"]=$location->getName();
	 $result["storeId"]=$location->getStore()->getId();
	 $result["storeName"]=$location->getStore()->getName();
	 $result["inventory"]=[];
	 foreach($products as $product){
		 if($product->getProduct()->getActive() && !$product->getProduct()->getDeleted()){
			 $result_product["id"]=$product->getProduct()->getId();
			 $result_product["name"]=$product->getProduct()->getName();
			 $result_product["quantity"]=$product->getQuantity();
			 $result["inventory"][]=$result_product;
		 }
	 }
	 return new JsonResponse($result);
  }

	/**
	* @Route("/{_locale}/admin/global/storelocation/{id}/disable", name="disableStoreLocation")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/storelocation/{id}/enable", name="enableStoreLocation")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/storelocation/{id}/delete", name="deleteStoreLocation")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
