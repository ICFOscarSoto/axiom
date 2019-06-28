<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleNotifications;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Utils\GlobaleListApiUtils;

class GlobaleNotificationsController extends Controller
{
	private $listFields=array(array("name" => "id", "caption"=>""), array("name" => "text", "caption"=>"Notificación"),
								 array("name" => "readed", "caption"=>"Leida", "width"=>"10%" ,"class" => "dt-center", "replace"=>array("1"=>"<div style=\"min-width: 75px;\" class=\"label label-success\">Leida</div>",
																																																		"0" => "<div style=\"min-width: 75px;\" class=\"label label-danger\">No leida</div>"))
								);
	 private $class=GlobaleNotifications::class;
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
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

		$templateLists=array();
		$listNotifications=array();
		$listNotifications['id'] = 'listNotifications';
		$listNotifications['fields'] = $this->listFields;
		$listNotifications['route'] = 'notificationslist';
		$listNotifications['orderColumn'] = 2;
		$listNotifications['orderDirection'] = 'DESC';
		$listNotifications['tagColumn'] = 0;
		$listNotifications['fieldButtons'] = json_decode(file_get_contents (dirname(__FILE__)."/../Lists/NotificationsFieldButtons.json"),true);
		$listNotifications['topButtons'] = array(
			array("id" => "printTop", "type" => "", "icon" => "fa fa-print","name" => "", "route"=>"", "confirm" =>false),
			array("id" => "exportTop", "type" => "", "icon" => "fa fa-file-excel-o","name" => "", "route"=>"", "confirm" =>false)
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
		$listUtils=new GlobaleListUtils();
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$this->listFields, $this->class);
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
	 * @Route("/api/globale/notifications/{id}/read", name="notificationsRead")
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
				$notification[0]->setDateupd(new \DateTime());
				$em->persist($notification[0]);
				$em->flush();
				return new JsonResponse(array('result' => 'true'));
			}
			return new JsonResponse(array('result' => 'false'));
		}
		return new JsonResponse(array('result' => 'false'));
	}


	/**
	* @Route("/api/notifications/worker/{id}/create/{type}", name="createNotificationWorker")
	*/
 public function createNotificationWorker($id, $type){
	 $notification=new GlobaleNotifications();
	 $workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
	 $worker=$workersrepository->findOneBy(["id"=>$id]);
	 $user=$worker->getUser();
	 if($user){
		 $notification->setUser($user);
		 setlocale(LC_ALL,"es_ES.utf8");
		 $date = new \DateTime();
		 switch($type){
			 case "clockSTART":
			 	$notification->setText("Jornada laboral iniciada el ".strftime('%A %e de %B a las %H:%M:%S',$date->getTimestamp()));
			 break;
			 case "clockEND":
			 	$notification->setText("Jornada laboral finalizada el ".strftime('%A %e de %B a las %H:%M:%S',$date->getTimestamp()));
			 break;
		 }
		 $notification->setDateadd(new \DateTime());
		 $notification->setDateupd(new \DateTime());
		 $notification->setReaded(0);
		 $notification->setDeleted(0);
		 $this->getDoctrine()->getManager()->persist($notification);
		 $this->getDoctrine()->getManager()->flush();
		 $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		  $url = $this->generateUrl(
		 		'sendFirebase',
		 		['id'  => $worker->getUser()->getId(),
		 	 'notificationid' => $notification->getId()]
		 );
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL,$protocol.$_SERVER['SERVER_NAME'].$url);
		 curl_setopt($ch, CURLOPT_POST, 1);
		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 $result= curl_exec ($ch);
		 return new JsonResponse(array('result' => 'true'));
	 }else return new JsonResponse(array('result' => 'false'));


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
					$notification->setDateupd(new \DateTime());
					$em->persist($notification);
					$em->flush();
				}else return new JsonResponse(array('result' => 'false'));
			}
			return new JsonResponse(array('result' => 'true'));
		}
		return new JsonResponse(array('result' => 'false'));
	}



	/**
	 * @Route("/api/globale/clocks/collection", name="genericNotificationscollection")
	 */
	 public function genericNotificationscollection(Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $parameters=$request->query->all();
		 $filter[]=["type"=>"and", "column"=>"user", "value"=>$this->getUser()];
		 foreach($parameters as $key => $parameter){
			 if(in_array("set".ucfirst($parameter),get_class_methods($this->class)))
				 $filter[]=["type"=>"and", "column"=>$key, "value"=>$parameter];
		 }
		 $listUtils=new GlobaleListApiUtils();
		 $return=$listUtils->getRecords($this->getUser(),$repository,$request,$manager, $this->class,$filter,[],-1);
		 return new JsonResponse($return);
	 }

}
