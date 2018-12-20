<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\Notifications;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\EntityUtils;

class NotificationsController extends Controller
{
	private $listFields=array(array("name" => "id", "caption"=>""), array("name" => "text", "caption"=>"Notificación"),
								 array("name" => "readed", "caption"=>"Leida", "width"=>"10%" ,"class" => "dt-center", "replace"=>array("1"=>"<div style=\"min-width: 75px;\" class=\"label label-success\">Leida</div>",
																																																		"0" => "<div style=\"min-width: 75px;\" class=\"label label-danger\">No leida</div>"))
								);
	 private $class=Notifications::class;
	 /**
     * @Route("/{_locale}/admin/global/notifications", name="notifications")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);

		$templateLists=array();
		$listNotifications=array();
		$listNotifications['id'] = 'listNotifications';
		$listNotifications['fields'] = $this->listFields;
		$listNotifications['route'] = 'notificationslist';
		$listNotifications['orderColumn'] = 2;
		$listNotifications['orderDirection'] = 'DESC';
		$listNotifications['tagColumn'] = 0;
		$listNotifications['fieldButtons'] = array(
			array("id" => "read", "type" => "info", "condition"=> "readed", "conditionValue" =>false , "icon" => "fa fa-eye-slash","name" => "leer", "route"=>"notificationsRead", "confirm" =>false, "actionType" => "background" )

		);
		$listNotifications['topButtons'] = array(
			array("id" => "printTop", "type" => "", "icon" => "fa fa-print","name" => "", "route"=>"editCompany", "confirm" =>false),
			array("id" => "exportTop", "type" => "", "icon" => "fa fa-file-excel-o","name" => "", "route"=>"editCompany", "confirm" =>false)
		);
		$templateLists[]=$listNotifications;
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'NotificationsController',
				'interfaceName' => 'Notificaciones',
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
	 * @Route("/api/notifications/list", name="notificationslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new ListUtils();
		$return=$listUtils->getRecords($repository,$request,$manager,$this->listFields, $this->class);
		return new JsonResponse($return);
	}

	/**
	 * @Route("/{_locale}/admin/api/notifications/unreadlist", name="notificationsUnreadList")
	 */
	public function notificationsUnreadList(Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository($this->class);
		$notifications=$repository->findNoReaded($user->getId());
		$response=Array();
		foreach($notifications as $notification){
			$item['id']=$notification->getId();
			$item['userId']=$notification->getUser()->getId();
			$item['text']=$notification->getText();
			$item['timestamp'] = $notification->getDateadd()->getTimestamp();
			$item['dateadd']=$notification->getDateadd();
			$response[]=$item;
		}
		return new JsonResponse($response);
	}

	 /**
	 * @Route("/{_locale}/admin/api/notifications/{id}/read", name="notificationsRead")
	 */
	public function notificationsRead($id){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$em = $this->getDoctrine()->getManager();
			$repository = $em->getRepository($this->class);
			$notification=$repository->findById($id, $user->getId());
			if(count($notification)>0){
				$notification[0]->setReaded(true);
				$em->persist($notification[0]);
				$em->flush();
				return new JsonResponse(array('result' => 'true'));
			}
			return new JsonResponse(array('result' => 'false'));
		}
		return new JsonResponse(array('result' => 'false'));
	}

	/**
	 * @Route("/{_locale}/admin/api/notifications/readall", name="notificationsReadAll")
	 */
	public function notificationsReadAll(){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$em = $this->getDoctrine()->getManager();
			$repository = $em->getRepository($this->class);
			$notifications=$repository->findNoReaded($user->getId());
			$response=Array();
			foreach($notifications as $notification){
				if($notification != false){
					$notification->setReaded(true);
					$em->persist($notification);
					$em->flush();
				}else return new JsonResponse(array('result' => 'false'));
			}
			return new JsonResponse(array('result' => 'true'));
		}
		return new JsonResponse(array('result' => 'false'));
	}
}
