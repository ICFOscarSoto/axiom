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
use App\Modules\Globale\Entity\GlobaleClockDevices;
use App\Modules\Globale\Entity\GlobaleClockDevicesWorkers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleCompaniesUtils;
use App\Modules\Globale\Utils\GlobaleClockDevicesWorkersUtils;
//use App\Modules\Globale\UtilsEntityUtils;
//use App\Modules\Form\Controller\FormController;

class GlobaleClockDevicesWorkersController extends Controller
{
  private $class=GlobaleClockDevicesWorkers::class;
  private $utilsClass=GlobaleClockDevicesWorkersUtils::class;
  /**
   * @Route("/{_locale}/globale/{id}/clockdevicesworker", name="clockdevicesworker")
   */
  public function index($id,RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  //$this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->utilsClass();
  $templateLists=$utils->formatList($id);
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/ClockDevicesWorkers.json", $request, $this, $this->getDoctrine());
  $templateForms[]=$formUtils->formatForm('ClockDevicesWorker', true, $id, $this->class);
  //$repository=$this->getDoctrine()->getRepository($this->class);
  //$clockdevicesworker=$repository->findOneBy(["id"=>$id, "deleted"=>0]);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/list.html.twig', [
      'listConstructor' => $templateLists,
      'forms' => $templateForms,
      'clockdevices_id' => $id,
      'include_modal_footer' => ['@Globale/clockdevicesfingers.html.twig']
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }


  /**
   * @Route("/{_locale}/globale/clockdevicesworker/data/{id}/{action}/{idclockdevice}", name="clockdevicesworkersdata", defaults={"id"=0, "action"="read", "idclockdevice"="0"})
   */
   public function data($id, $action, $idclockdevice, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $template=dirname(__FILE__)."/../Forms/ClockDevicesWorkers.json";
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $repository=$this->getDoctrine()->getRepository($this->class);
    $clockdeviceRepository=$this->getDoctrine()->getRepository(GlobaleClockDevices::class);
    if($id==0){
      if($idclockdevice==0 ) $idclockdevice=$request->query->get('clockdevice');
      if($idclockdevice==0 || $idclockdevice==null) $idclockdevice=$request->request->get('id-parent',0);
      $clockdevice = $clockdeviceRepository->find($idclockdevice);
    }	else $obj = $repository->find($id);

    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "clockdevice"=>$id==0?$clockdevice:$obj->getClockDevice()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
                       method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
    return $utils->make($id, $this->class, $action, "formclockdevice", "modal");
  }


  /**
   * @Route("/api/globale/clockdevices/worker/{id}/list", name="clockdevicesworkerslist")
   */
  public function clockslistworker($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $clockDeviceRepository=$this->getDoctrine()->getRepository(GlobaleClockDevices::class);
    $clockdevice=$clockDeviceRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ClockDevicesWorkers.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields,$this->class,[["type"=>"and", "column"=>"clockdevice", "value"=>$clockdevice]]);
    return new JsonResponse($return);
  }


  /**
   * @Route("/api/globale/clockdevicesworker/{id}/datetime", name="clockdevicesdatetime")
   */
   public function clockdevicesdatetime($id, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
  }



}
