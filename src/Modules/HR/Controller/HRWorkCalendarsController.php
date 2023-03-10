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
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRWorkersUtils;
use App\Modules\HR\Utils\HRWorkCalendarsUtils;
use App\Modules\HR\Entity\HRWorkCalendars;
use App\Modules\HR\Entity\HRWorkCalendarGroups;
use App\Modules\Security\Utils\SecurityUtils;

class HRWorkCalendarsController extends Controller
{
	 private $module='HR';
	 private $class=HRWorkCalendars::class;
   private $utilsClass=HRWorkCalendarsUtils::class;

   /**
    * @Route("/{_locale}/HR/{id}/workcalendars", name="workcalendars", defaults={"id"=0})
    */
   public function workcalendars($id, RouterInterface $router,Request $request)
   {
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
   $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
   $locale = $request->getLocale();
   $this->router = $router;
   $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

	 $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
	 $breadcrumb=$menurepository->formatBreadcrumb('genericindex','HR','WorkCalendarGroups');
	 array_push($breadcrumb, $new_breadcrumb);
	 $repository=$this->getDoctrine()->getRepository(HRWorkCalendarGroups::class);
	 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 if($id!=0 && $obj==null){
			 return $this->render('@Globale/notfound.html.twig',[
				 "status_code"=>404,
				 "status_text"=>"Objeto no encontrado"
			 ]);
	 }
	 $entity_name=$obj?$obj->getName():'';

   $utils = new $this->utilsClass();
   $formUtils=new GlobaleFormUtils();
   $formUtils->initialize($this->getUser(), new HRWorkCalendars(), dirname(__FILE__)."/../Forms/WorkCalendars.json", $request, $this, $this->getDoctrine(),["workcalendargroup"]);
   $templateLists[]=$utils->formatList($this->getUser(), $id);
   //$templateForms[]=$formUtils->formatForm('workcalendars', true);
   $templateForms[]=$formUtils->formatForm('workcalendars', true, $id, $this->class, null);
   if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
     return $this->render('@Globale/genericlist.html.twig', [
			 'entity_name' => $entity_name,
       'controllerName' => 'HRController',
       'interfaceName' => 'Calendarios laborales',
       'optionSelected' => 'genericindex',
			 'optionSelectedParams' => ["module"=>"HR", "name"=>"WorkCalendarGroups"],
       'menuOptions' =>  $menurepository->formatOptions($userdata),
       'breadcrumb' =>  $breadcrumb,
       'userData' => $userdata,
       'lists' => $templateLists,
       'forms' => $templateForms,
			 'entity_id' => $id,
       ]);
   }
   return new RedirectResponse($this->router->generate('app_login'));
   }

   /**
    * @Route("/api/HR/workcalendars/{id}/list", name="workcalendarslist")
   */
   public function workcalendarslist($id, RouterInterface $router,Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $user = $this->getUser();
     $locale = $request->getLocale();
     $this->router = $router;
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository(HRWorkCalendars::class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkCalendars.json"),true);
     $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, HRWorkCalendars::class,[["type"=>"and", "column"=>"workcalendargroup", "value"=>$id]],[],-1);
     return new JsonResponse($return);
   }

	 /**
	  * @Route("/{_locale}/HR/workcalendars/{entity}/data/{id}/{action}", name="dataWorkCalendars", defaults={"id"=0, "action"="read"})
	  */
	  public function data($entity,$id, $action, Request $request){
	 	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 	$this->denyAccessUnlessGranted('ROLE_ADMIN');
	 	$template=dirname(__FILE__)."/../Forms/WorkCalendars.json";
	 	$utils = new GlobaleFormUtils();
		$obj=new $this->class();
		$calendarGroupsRepository = $this->getDoctrine()->getManager()->getRepository(HRWorkCalendarGroups::class);
		$calendargroup=$calendarGroupsRepository->findOneBy(["id"=>$entity, "company"=>$this->getUser()->getCompany()]);
	 	$utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),["workcalendargroup"],[],null,["entity"=>$entity]);
		$utils->values(["workcalendargroup"=>$calendargroup]);
		return $utils->make($id, $this->class, $action, "formWorkCalendar", "modal");
	 }

  /**
 	* @Route("/{_locale}/HR/workcalendar/{id}/disable", name="disableWorkCalendar")
 	*/
 	public function disableWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new GlobaleEntityUtils();
 		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}
 	/**
 	* @Route("/{_locale}/HR/workcalendar/{id}/enable", name="enableWorkCalendar")
 	*/
 	public function enableWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new GlobaleEntityUtils();
 		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}

	/**
	 * @Route("/api/HR/workcalendars/export", name="exportWorkCalendars")
	 */
	 public function exportWorkCalendars(Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $utilsExport = new GlobaleExportUtils();
		 $user = $this->getUser();
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/WorkCalendars.json"),true);
		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $result = $utilsExport->export($list,$listFields);
		 return $result;
	 }

	 /**
 	 * @Route("/api/HR/workcalendars/print", name="printWorkCalendars")
 	 */
 	 public function printWorkers(Request $request){
 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 		 $utilsPrint = new GlobalePrintUtils();
 		 $user = $this->getUser();
 		 $manager = $this->getDoctrine()->getManager();
 		 $repository = $manager->getRepository($this->class);
 		 $listUtils=new GlobaleListUtils();
 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Prints/WorkCalendars.json"),true);
 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $utilsPrint->title="LISTADO DE CALENDARIOS LABORALES";
 		 $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
		 return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
 	 }



	/**
  * @Route("/{_locale}/HR/workcalendar/{id}/delete", name="deleteWorkCalendar", defaults={"id"=0})
  */
  public function delete($id,Request $request){
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
