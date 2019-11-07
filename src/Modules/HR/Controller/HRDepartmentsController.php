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
use App\Modules\HR\Utils\HRDepartmentsUtils;
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\Security\Utils\SecurityUtils;

class HRDepartmentsController extends Controller
{
  private $module='HR';
  private $class=HRDepartments::class;
  private $utilsClass=HRDepartmentsUtils::class;

  /**
   * @Route("/{_locale}/HR/departments", name="departments")
   */
  public function departments(RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->utilsClass();
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new HRDepartments(), dirname(__FILE__)."/../Forms/Departments.json", $request, $this, $this->getDoctrine());
  $templateLists[]=$utils->formatList($this->getUser());
  $templateForms[]=$formUtils->formatForm('departments', true, null, $this->class);
  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/genericlist.html.twig', [
      'controllerName' => 'HRDepartmentsController',
      'interfaceName' => 'Departamentos',
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
   * @Route("/api/HR/departments/list", name="departmentslist")
   */
  public function departmentslist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(HRDepartments::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Departments.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, HRDepartments::class);
    return new JsonResponse($return);
  }

  /**
   * @Route("/{_locale}/HR/department/data/{id}/{action}", name="dataDepartments", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/Departments.json";
   $utils = new GlobaleFormUtils();
   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
   return $utils->make($id, $this->class, $action, "formDepartments", "modal");
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
    * @Route("/{_locale}/HR/department/{id}/workers", name="departmentworkers")
    */
   public function departmentworkers($id,RouterInterface $router,Request $request)
   {
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
   $locale = $request->getLocale();
   $this->router = $router;
   $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
   $repository = $this->getDoctrine()->getRepository($this->class);
   $obj=$repository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
   $entityName=$obj?$obj->getName():'';
   $breadcrumb=$menurepository->formatBreadcrumb("departments");
	 array_push($breadcrumb, ["rute"=>null, "name"=>$entityName, "icon"=>"fa fa-address-book-o"]);
   $utils = new HRDepartmentsUtils();
   $templateLists[]=$utils->formatListWorkers($this->getUser(), $id);
   if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
     return $this->render('@Globale/genericlist.html.twig', [
       'entity_name' => $entityName,
       'controllerName' => 'HRController',
       'interfaceName' => 'Trabajadores',
       'optionSelected' => "departments",
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
  * @Route("/{_locale}/HR/department/{id}/disable", name="disableDepartment")
  */
  public function disableDepartment($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/department/{id}/enable", name="enableDepartment")
  */
  public function enableDepartment($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/department/{id}/delete", name="deleteDepartment", defaults={"id"=0})
  */
  public function deleteDepartment($id,Request $request){
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
