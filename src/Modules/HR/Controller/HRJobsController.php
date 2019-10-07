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
use App\Modules\Globale\Utils\GlobaleListRelationUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\HR\Utils\HRJobsUtils;
use App\Modules\HR\Entity\HRJobs;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HRJobsController extends Controller
{

  private $class=HRJobs::class;
  private $utilsClass=HRJobsUtils::class;
  /**
   * @Route("/{_locale}/HR/jobs/form/{id}", name="jobsindex", defaults={"id"=0})
   */
  public function index($id,RouterInterface $router,Request $request)
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $userdata=$this->getUser()->getTemplateData();
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
    $breadcrumb=$menurepository->formatBreadcrumb('jobs');
    array_push($breadcrumb, $new_breadcrumb);
    $breadcrumb=$menurepository->formatBreadcrumb('profile');
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$this->getUser()->getId(), "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Jobs.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);


    return $this->render('@Globale/genericform.html.twig', array(
            'controllerName' => 'HRJobs',
            'interfaceName' => 'Puesto trabajo',
            'optionSelected' => $request->attributes->get('_route'),
            'menuOptions' =>  $menurepository->formatOptions($userdata),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $this->getUser()->getId(),
            'route' => $this->generateUrl("dataJobs",["id"=>$id]),
            'form' => $utils->formatForm("formJobs", true, $id, $this->class, 'dataJobs')

    ));
  }

  /**
   * @Route("/{_locale}/job/data/{id}/{action}", name="dataJobs", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $template=dirname(__FILE__)."/../Forms/Jobs.json";
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request,
                       $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
                       method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
    return $utils->make($id, $this->class, $action, "formJobs", "full", "@Globale/form.html.twig", 'dataJobs', $this->utilsClass);
  }

  /**
   * @Route("/api/job/{id}/courses/list", name="jobcourseslist")
   */
  public function list($id, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $listUtils=new GlobaleListRelationUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/JobsCourses.json"),true);
    $return=$listUtils->getRecords($user,$this->getDoctrine(),$request,$manager,$listFields,"hrjobs_hrcourses",["hrjobs", "hrcourses"],1,[["type"=>"and", "column"=>"user", "value"=>$user]]);

    return new JsonResponse($return);
  }

  /**
  * @Route("/{_locale}/admin/hr/jobs/{id}/delete", name="deleteJob", defaults={"id"=0})
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
