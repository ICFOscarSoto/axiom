<?php
namespace App\Modules\Tracker\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Tracker\Entity\TrackerTrackers;
use App\Modules\Tracker\Entity\TrackerLocations;
use App\Modules\Tracker\Utils\TrackerLocationsUtils;

class TrackerLocationsController extends Controller
{
  private $class=TrackerLocations::class;
  private $classUtils=TrackerLocationsUtils::class;

  /**
   * @Route("/{_locale}/trackers/locations/location/{id}", name="locations", defaults={"id"=0})
   */
  public function index($id,RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  //$this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->classUtils();
  $templateLists[]=$utils->formatList($this->getUser(), $id);
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Locations.json", $request, $this, $this->getDoctrine());
  $templateForms[]=$formUtils->formatForm('locations', true, null, $this->class);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'controllerName' => 'trackersLocationsController',
      'interfaceName' => 'Locations',
      'optionSelected' => $request->attributes->get('_route'),
      'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
      'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
      'userData' => $userdata,
      'lists' => $templateLists,
      'forms' => $templateForms
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
   * @Route("/{_locale}/trackers/locations/tracking/{id}", name="tracking", defaults={"id"=0})
   */
  public function tracking($id,RouterInterface $router,Request $request)
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    //$this->denyAccessUnlessGranted('ROLE_ADMIN');
    $userdata=$this->getUser()->getTemplateData();
    $locale = $request->getLocale();
    $this->router = $router;
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@Tracker/tracking.html.twig', [
        'controllerName' => 'trackersController',
        'interfaceName' => 'Trackers',
        'optionSelected' => 'trackers',
        'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
        'breadcrumb' =>  'trackers',
        'userData' => $userdata
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
  * @Route("/api/tracker/dolocation/{company}/{id}/{token}", name="doLocations")
  */
  public function doLocations($company, $id, $token, Request $request){
    $trackerRepository=$this->getDoctrine()->getRepository(TrackerTrackers::class);
    $tracker=$trackerRepository->find($id);
    $locationRepository=$this->getDoctrine()->getRepository(TrackerLocations::class);
    if($tracker==NULL) return new JsonResponse(["result"=>-1]);
    if($tracker->getCompany()->getId()!=$company) return new JsonResponse(["result"=>-2]);
    if($tracker->getToken()!=$token) return new JsonResponse(["result"=>-3]); //Tokens sin almohadilllas
    $json=[];
    $content = $request->getContent();
    if (!empty($content)){
       $json = json_decode($content, true);
    }else return new JsonResponse(["result"=>0]);
    foreach($json as $item){
      $lastLocation=$locationRepository->findBy(["tracker" => $tracker, "date" => new \DateTime($item["datetime"])]);
      if($lastLocation==NULL){ //Prevenir dos localizaciones del mismo tracker con el mismo timestamp
        if($item["lat"]!=0.0 && $item["lon"]!=0.0){ //Comprobamos si las coordenadas son validas
          $location=new TrackerLocations();
          $location->setTracker($tracker);
          $location->setLatitude($item["lat"]);
          $location->setLongitude($item["lon"]);
          $location->setHdop($item["hdop"]);
          $location->setSats($item["sat"]);
          $location->setAge($item["age"]);
          $location->setAltitude($item["alt"]);
          $location->setCourse($item["course"]);
          $location->setKmph($item["kmph"]);
          $location->setDate(new \DateTime($item["datetime"]));
          $location->setDateupd(new \DateTime());
          $location->setDateadd(new \DateTime());
          $location->setActive(1);
          $location->setDeleted(0);
          $this->getDoctrine()->getManager()->persist($location);
          $this->getDoctrine()->getManager()->flush();
        }
      }
    }
    return new JsonResponse(["result"=>1,"json"=>$json]);
  }

  /**
   * @Route("/api/trackers/locations/{id}/getPoints", name="getPoints")
   */
  public function getPoints($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $trackerRepository=$this->getDoctrine()->getRepository(TrackerTrackers::class);
    $locationsRepository=$this->getDoctrine()->getRepository(TrackerLocations::class);
    $tracker=$trackerRepository->find($id);
    $start=$request->query->get('start','2000-01-01 00:00:00');
    $end=$request->query->get('end','2999-01-01 00:00:00');
    $locations = $locationsRepository->findPoints($tracker,$start,$end);
    $result=[];
    foreach($locations as $location){
      $point["id"]=$location->getId();
      $point["latitude"]=$location->getLatitude();
      $point["longitude"]=$location->getLongitude();
      $point["hdop"]=$location->getHdop();
      $point["sats"]=$location->getSats();
      $point["age"]=$location->getAge();
      $point["altitude"]=$location->getAltitude();
      $point["course"]=$location->getCourse();
      $point["kmph"]=$location->getKmph();
      $point["date"]=$location->getDate();
      $result[]=$point;
    }
    //dump($locations);
    return new JsonResponse($result);
  }

  /**
   * @Route("/api/trackers/locations/getpositions", name="getPositions")
   */
  public function getPositions(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $trackerRepository=$this->getDoctrine()->getRepository(TrackerTrackers::class);
    $locationsRepository=$this->getDoctrine()->getRepository(TrackerLocations::class);
    $tracker=$trackerRepository->find(1);
    //$tracker=$trackerRepository->findBy();
    //$start=$request->query->get('start','2000-01-01 00:00:00');
    //$end=$request->query->get('end','2999-01-01 00:00:00');
    //$locations = $locationsRepository->findPoints($tracker,$start,$end);
    $location = $locationsRepository->findOneBy(['tracker'=>$tracker,'active'=>1,'deleted'=>0],['date' => 'DESC']);
    $result=[];
  //    foreach($locations as $location){
      $point["id"]=$location->getTracker()->getId();
      $point["latitude"]=$location->getLatitude();
      $point["longitude"]=$location->getLongitude();
      $point["hdop"]=$location->getHdop();
      $point["sats"]=$location->getSats();
      $point["age"]=$location->getAge();
      $point["altitude"]=$location->getAltitude();
      $point["course"]=$location->getCourse();
      $point["kmph"]=$location->getKmph();
      $gmtTimezone = new \DateTimeZone('GMT');
      $dt = new \DateTime($location->getDate()->format('Y-m-d H:i:s'), $gmtTimezone);
      $tz = new \DateTimeZone('Europe/Madrid'); // or whatever zone you're after
      $dt->setTimezone($tz);
      $point["date"]=$dt->format('Y-m-d H:i:s');
      $result[]=$point;
  //  }
    //dump($locations);
    return new JsonResponse($result);
  }


  /**
   * @Route("/{_locale}/trackers/locations/data/{id}/{action}", name="dataLocations", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $template=dirname(__FILE__)."/../Forms/Locations.json";
     $utils = new GlobaleFormUtils();
     $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
     return $utils->make($id, $this->class, $action, "formLocations", "modal");
   }

  /**
   * @Route("/api/trackers/locations/{id}/list", name="locationslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $trackerRepository=$this->getDoctrine()->getRepository(TrackerTrackers::class);
    $tracker = $trackerRepository->find($id);
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Locations.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class, [["type"=>"and", "column"=>"tracker", "value"=>$tracker]]);
    return new JsonResponse($return);
  }



  /**
  * @Route("/{_locale}/tracker/locations/{id}/disable", name="disableLocation")
  */
  public function disable($id)
   {
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/tracker/locations/{id}/enable", name="enableLocation")
  */
  public function enable($id)
   {
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/tracker/locations/{id}/delete", name="deleteLocation")
  */
  public function delete($id){
   $this->denyAccessUnlessGranted('ROLE_GLOBAL');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }

}
