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
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Entity\GlobaleFirebaseDevices;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleNotifications;

class GlobaleFirebaseDevicesController extends Controller
{
	private $class=GlobaleFirebaseDevices::class;
  private $utilsClass=GlobaleFirebaseDevicesUtils::class;


  /**
  * @Route("/api/globale/settoken/{deviceid}", name="setFirebaseToken")
  */
  public function setFirebaseToken($deviceid, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $devicesrepository=$this->getDoctrine()->getRepository($this->class);
   $token=$request->request->get('TOKEN');
	 $platform=$request->request->get('platform','android');
     $device=$devicesrepository->findOneBy(["deviceid"=>$deviceid, "deleted"=>0, "active"=>1], ['id'=>'DESC']);
     if($device===NULL){
       $device=new $this->class();
       $device->setDeviceid($deviceid);
       $device->setUser($this->getUser());
			 $device->setPlatform($platform);
       $device->setToken($token);
       $device->setDateupd(new \DateTime());
       $device->setDateadd(new \DateTime());
       $device->setActive(1);
       $device->setDeleted(0);
       $this->getDoctrine()->getManager()->persist($device);
       $this->getDoctrine()->getManager()->flush();
       return new JsonResponse(["result"=>1]);
     }else{
       $device->setToken($token);
       $device->setUser($this->getUser());
       $device->setDateupd(new \DateTime());
       $this->getDoctrine()->getManager()->persist($device);
       $this->getDoctrine()->getManager()->flush();
       return new JsonResponse(["result"=>1]);
     }
     return new JsonResponse(["result"=>-1]);
  }


	/**
	 * @Route("/{_locale}/firebase/send/{id}/notification/{notificationid}", name="sendFirebase", defaults={"notificationid"=0})
	 */
	 public function sendFirebase($id, $notificationid, Request $request){
			$url = "https://fcm.googleapis.com/fcm/send";
			$usersrepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			$notificationsrepository=$this->getDoctrine()->getRepository(GlobaleNotifications::class);
			$devicesrepository=$this->getDoctrine()->getRepository($this->class);
			//Get all devices of user
			$user=$usersrepository->findOneBy(["id"=>$id, "deleted"=>0, "active"=>1]);
			$devices=$devicesrepository->findBy(["user"=>$user, "deleted"=>0, "active"=>1]);
			$notification=$notificationsrepository->findOneBy(["id"=>$notificationid, "readed"=>0]);
			if($notification){
			$serverKey = 'AAAAf9MGJoU:APA91bE6KicZ68wYAnLBfZcawG1vkO3DBdO24CeVFIW0ctkDGiYMJ7AuDq3I7k6nlqsIGIM-0hkpS9YigFWFAreX2CSlWj1YFHNdu5lFfzqxR1mBJ3FS2gOGJfLRnSfYvSOrgZ6cRgI0';

				foreach($devices as $device){
					$json=null;
					if($device->getPlatform()=="android"){
						$params = array('id' =>$notification->getId(), 'title' =>'Axiom' , 'body' => $notification->getText());
						$arrayToSend = array('to' => $device->getToken(), 'data' => ['body'=> ['op'=>'notification', 'params' => $params]], 'priority'=>'high');
						$json = json_encode($arrayToSend);
					}else{
						if($device->getPlatform()=="ios"){
							$message = array(
					        "body" => $notification->getText(),
					        "message" => $notification->getText(),
					        "title" => "Axiom",
					        "sound" => 1,
					        "vibrate" => 1,
					        "badge" => 1,
					    );
							$arrayToSend = array(
	             'registration_ids' => $device->getToken(),
	             'notification' => $message,
	             'priority' => 'high'
	            );
							$json = json_encode($arrayToSend);
						}
					}
					$headers = array();
					$headers[] = 'Content-Type: application/json';
					$headers[] = 'Authorization: key='. $serverKey;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
					curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
					curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
					//Send the request
					$response = curl_exec($ch);
					//Close request
					if ($response === FALSE) {
						die('FCM Send Error: ' . curl_error($ch));
					}
					curl_close($ch);
				}
			}

		return new Response('Sended.');
	}

}
