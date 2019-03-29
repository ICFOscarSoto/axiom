<?php
namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRWorkersUtils;
use App\Modules\HR\Utils\HRWorkCalendarsUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use App\Modules\HR\Entity\HRWorkCalendars;
use App\Modules\HR\Entity\HRHollidays;
use App\Modules\HR\Utils\HRHollidaysUtils;


class HRHollidaysController extends Controller
{

private $class=HRHollidays::class;
private $utilsClass=HRHollidaysUtils::class;

/**
 * @Route("/{_locale}/HR/{id}/holidays", name="holidays")
 */
public function holidays($id, RouterInterface $router, Request $request)
{
$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
//$this->denyAccessUnlessGranted('ROLE_ADMIN');
$userdata=$this->getUser()->getTemplateData();
$locale = $request->getLocale();
$this->router = $router;
$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
$workCalendarRepository=$this->getDoctrine()->getRepository(HRWorkCalendars::class);
$hollidaysRepository=$this->getDoctrine()->getRepository(HRHollidays::class);
$workCalendar=$workCalendarRepository->find($id);
dump($workCalendar->getWorkDays());
$utils = new $this->utilsClass();
$formUtils=new GlobaleFormUtils();
$formUtils->initialize($this->getUser(), new HRHollidays(), dirname(__FILE__)."/../Forms/Hollidays.json", $request, $this, $this->getDoctrine());
$templateForm=$formUtils->formatForm('holidays', true, $id, $this->class);
$this->get('session')->set('calendarId', $id);
if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  return $this->render('@HR/listhollidays.html.twig', [
    'controllerName' => 'HRController',
    'interfaceName' => 'Calendario laboral '.$workCalendar->getName(),
    'optionSelected' => 'workcalendars',
    'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
    'breadcrumb' => 'workcalendars',
    'userData' => $userdata,
    'id' => $id,
    'listConstructor' => ['topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/HolidaysTopButtons.json"),true)],
    'form' => $templateForm
    ]);
}
return new RedirectResponse($this->router->generate('app_login'));
}


/**
 * @Route("/{_locale}/HR/holidays/data/{id}/{action}", name="dataHollidays", defaults={"id"=0, "action"="read"})
 */
 public function dataHollidays($id, $action, Request $request){
 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 $template=dirname(__FILE__)."/../Forms/Hollidays.json";
 $workCalendarRepository=$this->getDoctrine()->getRepository(HRWorkCalendars::class);
 $workCalendar=$workCalendarRepository->find($this->get('session')->get('calendarId'));
 $utils = new GlobaleFormUtils();
 $utils->initialize($this->getUser(), new HRHollidays(), $template, $request, $this, $this->getDoctrine(),["calendar"]);
 $utils->values(["calendar"=>$workCalendar]);
 return $utils->make($id, HRHollidays::class, $action, "formHollidays", "modal");
}

/**
* @Route("/api/HR/hollidays/{id}/get", name="hollidaysGet")
*/
public function hollidaysGet($id,RouterInterface $router,Request $request){
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    $workCalendarRepository=$this->getDoctrine()->getRepository(HRWorkCalendars::class);
    $hollidaysRepository=$this->getDoctrine()->getRepository(HRHollidays::class);
    $workCalendar=$workCalendarRepository->find($id);
    $return=array();
    $user=$this->getUser();
    $start=$request->query->get('start');
    $end=$request->query->get('end');
    $events=$hollidaysRepository->findBy(["calendar"=>$workCalendar, "deleted"=>0, "active"=>1]);
    if($user->getCompany()!=$workCalendar->getCompany()){
      return new Response();
    }
    foreach($events as $event){
        $color='';
        switch($event->getType()){
          case 1: $color='#990000'; break;
          case 2: $color='#003399'; break;
          case 3: $color='#336600'; break;
        }
        $item["id"] = $event->getId();
        $item["title"] = $event->getName();
        $item["allDay"] = 1;
        $item["start"] = $event->getDate()->format('Y-m-d\TH:i:s');
        $item["end"] = $event->getDate()->format('Y-m-d\TH:i:s');
        $item["color"] = $color;
        $item["url"] = '';
        $return[]=$item;
    }
    return new JsonResponse($return);
  }
  return new Response();
}

/**
* @Route("/{_locale}/admin/global/holidays/{id}/disable", name="disableHoliday")
*/
public function disable($id){
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $entityUtils=new GlobaleEntityUtils();
  $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
  return new JsonResponse(array('result' => $result));
}
/**
* @Route("/{_locale}/admin/global/holidays/{id}/enable", name="enableHoliday")
*/
public function enable($id){
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $entityUtils=new GlobaleEntityUtils();
  $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
  return new JsonResponse(array('result' => $result));
}
/**
* @Route("/{_locale}/admin/global/holidays/{id}/delete", name="deleteHoliday")
*/
public function delete($id){
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $entityUtils=new GlobaleEntityUtils();
  $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
  return new JsonResponse(array('result' => $result));
}

}
