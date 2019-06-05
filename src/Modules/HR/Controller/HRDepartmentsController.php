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
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HRDepartmentsController extends Controller
{

  private $class=HRDepartments::class;
  private $utilsClass=HRDepartmentsUtils::class;

  /**
   * @Route("/{_locale}/HR/departments", name="departments")
   */
  public function departments(RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  $this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData();
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
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "columns"=>"company", "value"=>$user->getCompany()]],[],-1);
     $result = $utilsExport->export($list,$listFields);
     return $result;
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
  public function enableWorkCalendar($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
  /**
  * @Route("/{_locale}/HR/department/{id}/delete", name="deleteDepartment")
  */
  public function deleteWorkCalendar($id){
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $entityUtils=new GlobaleEntityUtils();
   $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
   return new JsonResponse(array('result' => $result));
  }
}
