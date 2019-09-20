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
use App\Modules\HR\Utils\HRPeriodsUtils;
use App\Modules\HR\Entity\HRPeriods;
use App\Modules\HR\Entity\HRShifts;


class HRPeriodsController extends Controller
{

	 private $class=HRPeriods::class;
   private $utilsClass=HRPeriodsUtils::class;

   /**
    * @Route("/{_locale}/HR/{id}/periods", name="periods", defaults={"id"=0})
    */
   public function periods($id, RouterInterface $router,Request $request)
   {
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   //$this->denyAccessUnlessGranted('ROLE_ADMIN');
   $userdata=$this->getUser()->getTemplateData();
   $locale = $request->getLocale();
   $this->router = $router;
   $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
   $utils = new $this->utilsClass();
   $formUtils=new GlobaleFormUtils();
   $formUtils->initialize($this->getUser(), new HRPeriods(), dirname(__FILE__)."/../Forms/Periods.json", $request, $this, $this->getDoctrine());
   $templateLists[]=$utils->formatListbyShift($id);
   //$templateForms[]=$formUtils->formatForm('workcalendars', true);
   $templateForms[]=$formUtils->formatForm('periods', true, $id, $this->class, null);
   if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
     return $this->render('@Globale/genericlist.html.twig', [
       'controllerName' => 'HRController',
       'interfaceName' => 'Calendarios laborales',
       'optionSelected' => 'schedules',
       'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
       'breadcrumb' =>  $menurepository->formatBreadcrumb('schedules'),
       'userData' => $userdata,
       'lists' => $templateLists,
       'forms' => $templateForms,
			 'entity_id' => $id,
			 'shift' => $id,
			 'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
       'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                            ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
       ]);
   }
   return new RedirectResponse($this->router->generate('app_login'));
   }

   /**
    * @Route("/api/HR/periods/{id}/list", name="periodslist")
   */
   public function workcalendarslist($id, RouterInterface $router,Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $user = $this->getUser();
		 $shiftsRepository=$this->getDoctrine()->getRepository(HRShifts::class);
		 $shift = $shiftsRepository->find($id);
		 $locale = $request->getLocale();
		 $this->router = $router;
		 $manager = $this->getDoctrine()->getManager();
		 $repository = $manager->getRepository($this->class);
		 $listUtils=new GlobaleListUtils();
		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Periods.json"),true);
		 $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields,$this->class,[["type"=>"and", "column"=>"shift", "value"=>$shift]]);
		 return new JsonResponse($return);
   }

	 /**
	  * @Route("/{_locale}/HR/periods/data/{id}/{action}/{idshift}", name="dataPeriods", defaults={"id"=0, "action"="read", "idshift"=0})
	  */
	  public function data($id, $idshift, $action, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    $this->denyAccessUnlessGranted('ROLE_ADMIN');
	    $template=dirname(__FILE__)."/../Forms/Periods.json";
	    $utils = new GlobaleFormUtils();
	    $utilsObj=new $this->utilsClass();
	    $periodsRepository=$this->getDoctrine()->getRepository($this->class);
	    $shiftsRepository=$this->getDoctrine()->getRepository(HRShifts::class);

	    if($id==0){
	      if($idshift==0 ) $idshift=$request->query->get('shift');
				if($idshift==0 || $idshift==null) $idshift=$request->request->get('form',[])["shift"];
	      $shift = $shiftsRepository->find($idshift);
	    }	else $obj = $periodsRepository->find($id);

	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "shift"=>$id==0?$shift:$obj->getShift()];
	    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
	                       method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	    if($id==0) $utils->values(["shift"=>$shift]);
	    return $utils->make($id, $this->class, $action, "periods", "modal");
	 }

  /**
 	* @Route("/{_locale}/HR/periods/{id}/disable", name="disablePeriod")
 	*/
 	public function disableWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new GlobaleEntityUtils();
 		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}

 	/**
 	* @Route("/{_locale}/HR/periods/{id}/enable", name="enablePeriod")
 	*/
 	public function enableWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new GlobaleEntityUtils();
 		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}



	/**
  * @Route("/{_locale}/HR/periods/{id}/delete", name="deletePeriod", defaults={"id"=0})
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
