<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleActivities;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleActivitiesUtils;
use App\Modules\Security\Utils\SecurityUtils;

class GlobaleActivitiesController extends Controller
{
	private $module='Globale';
	private $class=GlobaleActivities::class;

    /**
     * @Route("/{_locale}/admin/global/activities", name="activities")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new GlobaleActivitiesUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Activities.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('activities', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'currenciesController',
  				'interfaceName' => 'Actividades',
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
		 * @Route("/{_locale}/activities/data/{id}/{action}", name="dataActivities", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Activities.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formActivities", "modal");
		}



		/**
		* @Route("/api/global/activity/{id}/get", name="getActivity")
		*/
		public function getActivity($id){
			$activity = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
			if (!$activity) {
						throw $this->createNotFoundException('No currency found for id '.$id );
					}
					return new JsonResponse($activity->encodeJson());
		}

		/**
		* @Route("/api/activity/list", name="activitylist")
		*/
		public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(GlobaleActivities::class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Activities.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, GlobaleActivities::class);
		return new JsonResponse($return);
		}


	/**
	* @Route("/{_locale}/admin/global/activity/{id}/disable", name="disableActivity")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/activity/{id}/enable", name="enableActivity")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

 /**
 * @Route("/{_locale}/admin/global/activity/{id}/delete", name="deleteActivity", defaults={"id"=0})
 */
 public function delete($id, Request $request){
	 $this->denyAccessUnlessGranted('ROLE_ADMIN');
	 $entityUtils=new GlobaleEntityUtils();
	 if($id!=0) $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		else {
			$ids=$request->request->get('ids');
			$ids=explode(",",$ids);
			foreach($ids as $item){
				$result=$entityUtils->deleteObject($item, $this->class, $this->getDoctrine());
			}
		}
	 return new JsonResponse(array('result' => $result));
 }

}
