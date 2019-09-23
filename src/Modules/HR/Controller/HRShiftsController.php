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
use App\Modules\HR\Utils\HRShiftsUtils;
use App\Modules\HR\Entity\HRSchedules;
use App\Modules\HR\Entity\HRShifts;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class HRShiftsController extends Controller
{

  private $class=HRShifts::class;
  private $utilsClass=HRShiftsUtils::class;

  /**
   * @Route("/{_locale}/HR/{id}/shifts", name="shifts")
   */
  public function shifts($id, RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $schedulesRepository=$this->getDoctrine()->getRepository(HRSchedules::class);
  $schedule=$schedulesRepository->findOneBy(["id"=>$id]);

  //Redirect to periods if schedule type is 'fijo'
  if($schedule->getType()==2){
    $shiftsRepository=$this->getDoctrine()->getRepository(HRShifts::class);
    $shift=$shiftsRepository->findOneBy(["schedule"=>$schedule]);
    return $this->redirectToRoute('periods', ['id' => $shift->getId()]);
  }

  $utils = new $this->utilsClass();
  $templateLists[]=$utils->formatListbySchedule($id);
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new HRShifts(), dirname(__FILE__)."/../Forms/Shifts.json", $request, $this, $this->getDoctrine());
  $templateForms[]=$formUtils->formatForm('shifts', true, $id, $this->class);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'controllerName' => 'HRShiftsController',
      'interfaceName' => 'Turnos horario '.$schedule->getName(),
      'optionSelected' => 'schedules',
      'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
      'breadcrumb' => $menurepository->formatBreadcrumb('schedules'),
      'userData' => $userdata,
      'schedule_id' => $id,
      'id_parent' =>$id,
      'lists' => $templateLists,
      'forms' => $templateForms,
      'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
      'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                           ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
   * @Route("/api/HR/shifts/{id}/list", name="shiftslist")
   */
  public function shiftslist($id, RouterInterface $router,Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $user = $this->getUser();
      $scheduleRepository=$this->getDoctrine()->getRepository(HRSchedules::class);
      $schedule = $scheduleRepository->find($id);
      $locale = $request->getLocale();
      $this->router = $router;
      $manager = $this->getDoctrine()->getManager();
      $repository = $manager->getRepository($this->class);
      $listUtils=new GlobaleListUtils();
      $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Shifts.json"),true);
      $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields,$this->class,[["type"=>"and", "column"=>"schedule", "value"=>$schedule]]);
      return new JsonResponse($return);
  }

  /**
   * @Route("/{_locale}/HR/shifts/data/{id}/{action}/{idschedule}", name="dataShifts", defaults={"id"=0, "action"="read", "idschedule"="0"})
   */
   public function data($id, $action, $idschedule, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $template=dirname(__FILE__)."/../Forms/Shifts.json";
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $shiftsRepository=$this->getDoctrine()->getRepository($this->class);
    $scheduleRepository=$this->getDoctrine()->getRepository(HRSchedules::class);

    if($id==0){
      if($idschedule==0 ) $idschedule=$request->query->get('schedule');
      if($idschedule==0 || $idschedule==null) $idschedule=$request->request->get('id-parent',0);
      if($idschedule==0 || $idschedule==null) $idschedule=$request->request->get('form',[])["schedule"];
      $schedule = $scheduleRepository->find($idschedule);
    }	else $obj = $shiftsRepository->find($id);

    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "schedule"=>$id==0?$schedule:$obj->getSchedule()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
                       method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
    if($id==0) $utils->values(["schedule"=>$schedule]);
    return $utils->make($id, $this->class, $action, "shifts", "modal");
  }

  /**
   * @Route("/{_locale}/HR/department/export", name="exportDepartments")
   */
   public function exportDepartment(Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $utilsExport = new GlobaleExportUtils();
     $user = $this->getUser();
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Departments.json"),true);
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
     $result = $utilsExport->export($list,$listFields);
     return $result;
   }

   /**
 	 * @Route("/api/HR/department/print", name="printDepartments")
 	 */
 	 public function printDepartments(Request $request){
 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 		 $utilsPrint = new GlobalePrintUtils();
 		 $user = $this->getUser();
 		 $manager = $this->getDoctrine()->getManager();
 		 $repository = $manager->getRepository($this->class);
 		 $listUtils=new GlobaleListUtils();
 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Prints/Departments.json"),true);
 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $utilsPrint->title="LISTADO DE DEPARTAMENTOS";
 		 $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
		 return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
 	 }



  /**
  * @Route("/{_locale}/HR/shift/{id}/disable", name="disableShift")
  */
  public function disableShift($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/shift/{id}/enable", name="enableShift")
  */
  public function enableShift($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/shift/{id}/delete", name="deleteShift", defaults={"id"=0})
  */
  public function deleteShift($id,Request $request){
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
