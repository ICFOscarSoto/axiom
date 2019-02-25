<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Entity\ShippingCosts;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ShippingCostsUtils;

class ShippingCostsController extends Controller
{
	private $class=ShippingCosts::class;

    /**
     * @Route("/{_locale}/admin/global/shippingcosts", name="shippingcosts")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
    	$utils = new ShippingCostsUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new FormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/ShippingCosts.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('shippingcosts', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'ShippingCostsController',
  				'interfaceName' => 'shippingcosts',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
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
		 * @Route("/{_locale}/shippingcosts/data/{id}/{action}", name="dataShippingCosts", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/ShippingCosts.json";
		 $utils = new FormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formShippingCosts", "modal");
		}


		/**
		* @Route("/api/global/shippingcost/{id}/get", name="getShippingCost")
		*/
		public function getShippingCost($id){
			$shippingcost = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
			if (!$shippingcost) {
						throw $this->createNotFoundException('No currency found for id '.$id );
					}
					return new JsonResponse($shippingcost->encodeJson());
		}

		/**
		* @Route("/api/shippingcost/list", name="shippingcostlist")
		*/
		public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(ShippingCosts::class);
		$listUtils=new ListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ShippingCosts.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, ShippingCosts::class);
		return new JsonResponse($return);
		}



	/**
	* @Route("/{_locale}/admin/global/shippingcost/{id}/disable", name="disableShippingCost")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/shippingcost/{id}/enable", name="enableShippingCost")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/shippingcost/{id}/delete", name="deleteShippingCost")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
