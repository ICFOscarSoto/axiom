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
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRVacations;
use App\Modules\HR\Utils\HRVacationsUtils;

class HRVacationsController extends Controller
{
  private $module='HR';
  private $class=HRVacations::class;
  private $utilsClass=HRVacationsUtils::class;

  /**
   * @Route("/{_locale}/HR/{id}/vacations", name="vacations")
   */
  public function index($id,RouterInterface $router,Request $request)
  {
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  //$this->denyAccessUnlessGranted('ROLE_ADMIN');
  $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  $locale = $request->getLocale();
  $this->router = $router;
  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
  $utils = new $this->utilsClass();
  $templateLists=$utils->formatListbyWorker($id);
  $formUtils=new GlobaleFormUtils();
  $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Vacations.json", $request, $this, $this->getDoctrine());
  $templateForms[]=$formUtils->formatForm('vacations', true, $id, $this->class);

  $workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
  $vacationsrepository=$this->getDoctrine()->getRepository(HRVacations::class);
  $worker=$workersrepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);

  if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
    return $this->render('@Globale/list.html.twig', [
      'listConstructor' => $templateLists,
      'forms' => $templateForms,
      'worker_id' => $id
      ]);
  }
  return new RedirectResponse($this->router->generate('app_login'));
  }


  /**
   * @Route("/{_locale}/HR/{id}/vacations/export", name="exportVacations")
   */
   public function exportVacations($id, Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $utilsExport = new GlobaleExportUtils();
     $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
     $worker = $workerRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
     $user = $this->getUser();
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Vacations.json"),true);
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"worker", "value"=>$worker]],[],-1);
     $result = $utilsExport->export($list,$listFields);
     return $result;
   }

   /**
   * @Route("/api/HR/{id}/vacations/print", name="printVacations")
   */
   public function printVacations($id, Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $utilsPrint = new GlobalePrintUtils();
     $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
     $worker = $workerRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
     $user = $this->getUser();
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Prints/Vacations.json"),true);
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"worker", "value"=>$worker]],[],-1);
     $utilsPrint->title="LISTADO DE VACACIONES: ".$worker->getLastname().", ".$worker->getName()." (".$worker->getIdcard().")";
     $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
     return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
   }


   /**
    * @Route("/{_locale}/HR/vacations/data/{id}/{action}/{idworker}", name="dataVacations", defaults={"id"=0, "action"="read", "idworker"="0"})
    */
    public function data($id, $action, $idworker, Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $template=dirname(__FILE__)."/../Forms/Vacations.json";
     $utils = new GlobaleFormUtils();
     $utilsObj=new $this->utilsClass();
     $vacationsRepository=$this->getDoctrine()->getRepository($this->class);
     $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
     if($id==0){
       if($idworker==0 ) $idworker=$request->query->get('worker');
       if($idworker==0 || $idworker==null) $idworker=$request->request->get('id-parent',0);
       $worker = $workerRepository->find($idworker);
     }	else $obj = $vacationsRepository->find($id);

     $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "worker"=>$id==0?$worker:$obj->getWorker()];
     $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
                        method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
     return $utils->make($id, $this->class, $action, "formworker", "modal");
   }

   /**
    * @Route("/api/HR/vacations/worker/{id}/list", name="vacationslistworker")
    */
   public function clockslistworker($id,RouterInterface $router,Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $user = $this->getUser();
     $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
     $worker = $workerRepository->find($id);
     $locale = $request->getLocale();
     $this->router = $router;
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Vacations.json"),true);
     $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields,$this->class,[["type"=>"and", "column"=>"worker", "value"=>$worker]]);
     return new JsonResponse($return);
   }


   /**
 	* @Route("/{_locale}/HR/vacations/{id}/disable", name="disableVacations")
 	*/
 	public function disable($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new GlobaleEntityUtils();
 		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}
 	/**
 	* @Route("/{_locale}/HR/vacations/{id}/enable", name="enableVacations")
 	*/
 	public function enable($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new GlobaleEntityUtils();
 		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}

  /**
  * @Route("/{_locale}/HR/vacations/{id}/delete", name="deleteVacations", defaults={"id"=0})
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
