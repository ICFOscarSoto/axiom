<?php
namespace App\Modules\Calendar\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Calendar\Entity\CalendarCalendars;
use App\Modules\Calendar\Entity\CalendarEvents;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use App\Modules\Security\Utils\SecurityUtils;

class CalendarController extends Controller{

	private $module='Calendar';

  /**
	 * @Route("/{_locale}/admin/calendar", name="calendar")
	 */
	public function calendar(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			return $this->render('@Calendar/calendar_list.html.twig', [
				'controllerName' => 'CalendarController',
				'interfaceName' => 'Calendario',
				'optionSelected' => 'calendar',
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata
				]);
		}else return new RedirectResponse($this->router->generate('app_login'));
	}

  /**
  * @Route("/api/calendars/list", name="calendarsList")
  */
  public function calendarsList(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
      $return=array();
      $user=$this->getUser();
      foreach($user->getCalendars() as $calendar){
          $item["id"] = $calendar->getId();
          $item["name"] = $calendar->getName();
          $item["color"] = $calendar->getColor();
          $item["active"] = $calendar->getActive();
          $item["url"] = $this->generateUrl('calendarGet', array('id'=>$calendar->getId()));
          $item["eventUrl"] = $this->generateUrl('eventsList', array('id'=>$calendar->getId()));
					$return[]=$item;
      }

			return new JsonResponse($return);
    }
    return new Response();
  }

  /**
  * @Route("/api/calendar/{id}/get", name="calendarGet")
  */
  public function calendarGet($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
      $return=array();
      $user=$this->getUser();
      $calendar=$calendarRepository->find($id);
      if($user->getId()!=$calendar->getUser()->getId()){
        return new Response();
      }
        $item["id"] = $calendar->getId();
        $item["name"] = $calendar->getName();
        $item["color"] = $calendar->getColor();
        $item["url"] = $calendar->getColor();

      return new JsonResponse($item);
    }
    return new Response();
  }

  /**
  * @Route("/api/event/{id}/get", name="eventGet")
  */
  public function eventGet($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
      $eventRepository = $this->getDoctrine()->getRepository(CalendarEvents::class);
      $return=array();
      $user=$this->getUser();
      $event=$eventRepository->find($id);
      $calendar=$event->getCalendar();
      if($user->getId()!=$calendar->getUser()->getId()){
        return new Response();
      }
      $item["id"] = $event->getId();
      $item["title"] = $event->getTitle();
      $item["allDay"] = $event->getAllDay();
      $item["location"] = $event->getLocation();
      $item["calendar"] = $calendar->getId();
      $item["color"] = $event->getColor();
      $item["startDate"] = $event->getStart()->format('d/m/Y');
      $item["startTime"] = $event->getStart()->format('H:i');
      $item["endDate"] = $event->getEnd()->format('d/m/Y');
      $item["endTime"] = $event->getEnd()->format('H:i');
      $item["description"] = $event->getDescription();
      return new JsonResponse($item);
    }
    return new Response();
  }

  /**
  * @Route("/api/events/{id}/list", name="eventsList")
  */
  public function eventsList($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
      $eventRepository = $this->getDoctrine()->getRepository(CalendarEvents::class);
      $return=array();
      $user=$this->getUser();
      $start=$request->query->get('start');
      $end=$request->query->get('end');
      $events=$eventRepository->findByRange($id, $start, $end);
      $calendar=$calendarRepository->find($id);
      if($user->getId()!=$calendar->getUser()->getId()){
        return new Response();
      }
      foreach($events as $event){
          $item["id"] = $event->getId();
          $item["title"] = $event->getTitle();
          $item["allDay"] = $event->getAllDay();
          $item["start"] = $event->getStart()->format('Y-m-d\TH:i:s');
          $item["end"] = $event->getEnd()->format('Y-m-d\TH:i:s');
          $item["color"] = $event->getColor()!=null?$event->getColor():$calendar->getColor();
          $item["url"] = $this->generateUrl('eventGet', array('id'=>$event->getId()));
          $return[]=$item;
      }

      return new JsonResponse($return);
    }
    return new Response();
  }


/**
* @Route("/api/calendars/save", name="calendarSave")
*/
public function calendarSave(RouterInterface $router,Request $request){
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
    $em = $this->getDoctrine()->getManager();
    $return=array();
    $user=$this->getUser();
    $id=$request->query->get('calendarId');
    $calendar=null;
    if($id!=null){
      $calendar=$calendarRepository->find($id);
    }else{
      $calendar=new CalendarCalendars();
      $calendar->setDateAdd(new \DateTime('now'));
      $calendar->setUser($user);
      $calendar->setDeleted(false);
      $calendar->setActive(true);
    }
    $calendar->setName($request->query->get('calendarName'));
    $calendar->setColor($request->query->get('calendarColor'));
    $calendar->setDateUpd(new \DateTime('now'));
    $return=0;
    try{
      $em->persist($calendar);
      $em->flush();
      $return=1;
    }catch(Exception $e){
      $return=-1;
    }

    return new JsonResponse(array("result" => $return));
  }
  return new Response();
}

/**
* @Route("/api/events/save", name="eventSave")
*/
public function eventSave(RouterInterface $router,Request $request){
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
    $eventRepository = $this->getDoctrine()->getRepository(CalendarEvents::class);
    $em = $this->getDoctrine()->getManager();
    $return=array();
    $user=$this->getUser();
    $id=$request->query->get('eventId');
    $event=null;
    if($id!=null){
      $event=$eventRepository->find($id);
      $calendar=$calendarRepository->find($event->getCalendar()->getId());
      if($user->getId()!=$calendar->getUser()->getId()){
        return new Response();
      }
    }else{
      $event=new CalendarEvents();
      $event->setDateAdd(new \DateTime('now'));
      $event->setDeleted(false);
      $event->setActive(true);
    }
    $calendar=$calendarRepository->find($request->query->get('eventCalendar'));
    $event->setTitle($request->query->get('eventTitle'));
    $event->setAllDay($request->query->get('eventAllDay')=="false"?true:false);
    $event->setLocation($request->query->get('eventLocation'));
    $event->setCalendar($calendar);
    $event->setColor($request->query->get('eventColor'));
    $event->setStart(\DateTime::createFromFormat("d/m/Y H:i", $request->query->get('eventStartDate')." ".$request->query->get('eventStartTime')));
    $event->setEnd(\DateTime::createFromFormat("d/m/Y H:i", $request->query->get('eventEndDate')." ".$request->query->get('eventEndTime')));
    $event->setDescription($request->query->get('description'));
    $event->setDateUpd(new \DateTime('now'));
    $return=0;
    try{
      $em->persist($event);
      $em->flush();
      $return=1;
    }catch(Exception $e){
      $return=-1;
    }

    return new JsonResponse(array("result" => $return));
  }
  return new Response();
}

  /**
  * @Route("/api/events/changeDate", name="eventChangeDate")
  */
  public function eventChangeDate(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $calendarRepository = $this->getDoctrine()->getRepository(CalendarCalendars::class);
      $eventRepository = $this->getDoctrine()->getRepository(CalendarEvents::class);
      $em = $this->getDoctrine()->getManager();
      $return=array();
      $user=$this->getUser();
      $id=$request->query->get('eventId');
      $start=$request->query->get('eventStart');
      $end=$request->query->get('eventEnd');
      $event=$eventRepository->find($id);
      $calendar=$calendarRepository->find($event->getCalendar()->getId());
      if($user->getId()!=$calendar->getUser()->getId()){
        return new Response();
      }
      if($request->query->has('eventStart')){
        if(strlen($start)>10) $event->setStart(\DateTime::createFromFormat("Y-m-d\TH:i:s", $start));
          else $event->setStart(\DateTime::createFromFormat("Y-m-d", $start));
      }
      if($request->query->has('eventEnd')) $event->setEnd(\DateTime::createFromFormat("Y-m-d\TH:i:s", $end));

      $return=0;
      try{
        $em->persist($event);
        $em->flush();
        $return=1;
      }catch(Exception $e){
        $return=-1;
      }
      return new JsonResponse(array("result" => $return));
    }
    return new Response();
  }



}
