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
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'ShippingCostsController',
  				'interfaceName' => 'shippingcosts',
  				'optionSelected' => $request->attributes->get('_route'),
  				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
  				'userData' => $userdata,
  				'lists' => $templateLists
  				]);
  		}
  	return new RedirectResponse($this->router->generate('app_login'));
		//return new Response("");

		}


		/**
		* @Route("/{_locale}/admin/global/shippingcost/new", name="newShippingCost")
		*/

		public function newShippingCost(Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$obj=new ShippingCosts();
			$utils = new ShippingCostsUtils();
			$editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "New", "fa fa-plus");
			//print_r($obj);
			//return new Response("SI AQUI");
			return $this->render($editor["template"], $editor["vars"]);

		}

		/**
		* @Route("/{_locale}/admin/global/shippingcost/{id}/edit", name="editShippingCost")
		*/
		public function editShippingCost($id,Request $request)
			{
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				$this->denyAccessUnlessGranted('ROLE_ADMIN');
				$repository = $this->getDoctrine()->getRepository($this->class);
				$obj=$repository->find($id);
				$utils = new ShippingCostsUtils();
				$editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "Edit", "fa fa-edit");
				return $this->render($editor["template"], $editor["vars"]);
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
