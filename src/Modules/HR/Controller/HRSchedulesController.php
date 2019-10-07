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
use App\Modules\HR\Utils\HRSchedulesUtils;
use App\Modules\HR\Entity\HRSchedules;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HRSchedulesController extends Controller
{

  private $class=HRSchedules::class;
  private $utilsClass=HRSchedulesUtils::class;

  /**
   * @Route("/{_locale}/HR/schedules", name="schedules")
   */
  public function schedules(RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->utilsClass();
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Schedules.json", $request, $this, $this->getDoctrine());
  $templateLists[]=$utils->formatList($this->getUser());
  $templateForms[]=$formUtils->formatForm('schedules', true, null, $this->class);
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'controllerName' => 'HRSchedulesController',
      'interfaceName' => 'Horarios',
      'optionSelected' => $request->attributes->get('_route'),
      'menuOptions' =>  $menurepository->formatOptions($userdata),
      'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
      'userData' => $userdata,
      'lists' => $templateLists,
      'forms' => $templateForms
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
   * @Route("/api/HR/schedules/list", name="scheduleslist")
   */
  public function scheduleslist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Schedules.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
    return new JsonResponse($return);
  }

  /**
   * @Route("/{_locale}/HR/schedules/data/{id}/{action}", name="dataSchedules", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/Schedules.json";
   $utils = new GlobaleFormUtils();
   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
   return $utils->make($id, $this->class, $action, "formSchedules", "modal");
  }

  /**
   * @Route("/{_locale}/HR/schedules/export", name="exportSchedules")
   */
   public function exportSchedules(Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $utilsExport = new GlobaleExportUtils();
     $user = $this->getUser();
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Schedules.json"),true);
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
     $result = $utilsExport->export($list,$listFields);
     return $result;
   }

   /**
 	 * @Route("/api/HR/schedules/print", name="printSchedules")
 	 */
 	 public function printSchedules(Request $request){
 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 		 $utilsPrint = new GlobalePrintUtils();
 		 $user = $this->getUser();
 		 $manager = $this->getDoctrine()->getManager();
 		 $repository = $manager->getRepository($this->class);
 		 $listUtils=new GlobaleListUtils();
 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Prints/Schedules.json"),true);
 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $utilsPrint->title="LISTADO DE HORARIOS";
 		 $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
		 return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
 	 }

   /**
  * @Route("/{_locale}/HR/schedules/{id}/disable", name="disableSchedules")
  */
  public function disableSchedules($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/schedules/{id}/enable", name="enableSchedules")
  */
  public function enableSchedules($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/schedules/{id}/delete", name="deleteSchedules", defaults={"id"=0})
  */
  public function deleteSchedules($id,Request $request){
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
