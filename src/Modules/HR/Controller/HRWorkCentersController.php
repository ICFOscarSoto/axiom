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
use App\Modules\HR\Utils\HRWorkCentersUtils;
use App\Modules\HR\Entity\HRWorkCenters;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\Security\Utils\SecurityUtils;

class HRWorkCentersController extends Controller
{
  private $module='HR';
  private $class=HRWorkCenters::class;
  private $utilsClass=HRWorkCentersUtils::class;

  /**
   * @Route("/{_locale}/HR/workcenter/{id}/workers", name="workcenterworkers")
   */
  public function workcenterworkers($id,RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new HRWorkCentersUtils();
  $templateLists[]=$utils->formatListWorkers($this->getUser(), $id);

  $repository = $this->getDoctrine()->getRepository($this->class);
  $obj=$repository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
  $entityName=$obj?$obj->getName():'';
  $breadcrumb=$menurepository->formatBreadcrumb('genericindex', "HR", "workCenters");
  array_push($breadcrumb, ["rute"=>null, "name"=>$entityName, "icon"=>"fa fa-address-book-o"]);
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'entity_name' => $entityName,
      'controllerName' => 'HRController',
      'interfaceName' => 'Trabajadores',
      'optionSelected' => "genericindex",
      'optionSelectedParams' => ["module"=>"HR", "name"=>"workCenters"],
      'menuOptions' =>  $menurepository->formatOptions($userdata),
      'breadcrumb' =>  $breadcrumb,
      'userData' => $userdata,
      'lists' => $templateLists,
      'include_post_templates' => ['@HR/clocksprintselect.html.twig'],
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }


  /**
  * @Route("/{_locale}/admin/HR/workcenter/{id}/delete", name="deleteWorkCenter", defaults={"id"=0})
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


  /**
   * @Route("/{_locale}/HR/workcenter/export", name="exportWorkCenters")
   */
   public function exportWorkCenters(Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $utilsExport = new GlobaleExportUtils();
     $user = $this->getUser();
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/WorkCenters.json"),true);
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
     $result = $utilsExport->export($list,$listFields);
     return $result;
   }

   /**
 	 * @Route("/api/HR/workcenter/print", name="printWorkCenters")
 	 */
 	 public function printDepartments(Request $request){
 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 		 $utilsPrint = new GlobalePrintUtils();
 		 $user = $this->getUser();
 		 $manager = $this->getDoctrine()->getManager();
 		 $repository = $manager->getRepository($this->class);
 		 $listUtils=new GlobaleListUtils();
 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Prints/WorkCenters.json"),true);
 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $utilsPrint->title="LISTADO DE CENTROS DE TRABAJO";
 		 $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
		 return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
 	 }



}
