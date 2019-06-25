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
   $token=$request->query->get('token');

     $device=$devicesrepository->findOneBy(["deviceid"=>$deviceid, "deleted"=>0, "active"=>1], ['id'=>'DESC']);
     if($device===NULL){
       $device=new $this->class();
       $device->setDeviceid($deviceid);
       $device->setUser($this->getUser());
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


}
