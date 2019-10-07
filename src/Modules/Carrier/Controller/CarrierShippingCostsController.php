<?php

namespace App\Modules\Carrier\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Carrier\Entity\CarrierShippingCosts;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Carrier\Utils\CarrierShippingCostsUtils;

class CarrierShippingCostsController extends Controller
{
	private $class=CarrierShippingCosts::class;
	private $utilsClass=CarrierShippingCostsUtils::class;

    /**
     * @Route("/{_locale}/carriers/shippingcosts", name="shippingcosts")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new $this->utilsClass();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/ShippingCosts.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('shippingcosts', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'ShippingCostsController',
  				'interfaceName' => 'shippingcosts',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists,
	        'forms' => $templateForms
  				]);
  		}
  	return new RedirectResponse($this->router->generate('app_login'));
		//return new Response("");

		}

		/**
		 * @Route("/{_locale}/carriers/shippingcosts/data/{id}/{action}", name="dataShippingCosts", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/ShippingCosts.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formShippingCosts", "modal");
		}


		/**
		* @Route("/api/carriers/shippingcosts/{id}/get", name="getShippingCost")
		*/
		public function getShippingCost($id){
			$shippingcost = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
			if (!$shippingcost) {
						throw $this->createNotFoundException('No currency found for id '.$id );
					}
					return new JsonResponse($shippingcost->encodeJson());
		}

		/**
		* @Route("/api/carriers/shippingcosts/list", name="shippingcostlist")
		*/
		public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class());
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ShippingCosts.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class());
		return new JsonResponse($return);
		}



	/**
	* @Route("/{_locale}/carriers/shippingcosts/{id}/disable", name="disableShippingCost")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/carriers/shippingcosts/{id}/enable", name="enableShippingCost")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/carriers/shippingcosts/{id}/delete", name="deleteShippingCost")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
