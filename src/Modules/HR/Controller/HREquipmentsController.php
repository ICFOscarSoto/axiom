<?php
namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\HR\Entity\HREquipmentCategories;
use App\Modules\HR\Entity\HREquipments;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRWorkerEquipment;
use App\Modules\HR\Reports\HREquipmentsReports;
use App\Modules\HR\Utils\HRWorkerEquipmentUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;

class HREquipmentsController extends Controller
{

  private $module='HR';
  private $class=HRWorkerEquipment::class;
  private $utilsClass=HRWorkerEquipmentUtils::class;

  /**
  * @Route("/api/HR/equipments/elements/save", name="saveEquipmentsCategories")
  */
  public function saveEquipmentsCategories(RouterInterface $router,Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $equipment=json_decode($request->getContent());
   $equipmentsRepository=$this->getDoctrine()->getRepository(HREquipments::class);
   $workerEquipmentsRepository=$this->getDoctrine()->getRepository(HRWorkerEquipment::class);
   $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);

   $itemEquipment=$equipmentsRepository->findOneBy(["id"=>$equipment->equipment,"active"=>1,"deleted"=>0]);
   if(!$itemEquipment) return new JsonResponse(["result"=>-1]);
   $worker=$workerRepository->findOneBy(["id"=>$equipment->worker,"active"=>1,"deleted"=>0]);
   if(!$worker) return new JsonResponse(["result"=>-1]);
   $workerEquipment=new HRWorkerEquipment();
   $workerEquipment->setWorker($worker);
   $workerEquipment->setEquipment($itemEquipment);
   $workerEquipment->setSerial($equipment->serial);
   $workerEquipment->setQuantity($equipment->quantity);
   $workerEquipment->setSize($equipment->size);
   $workerEquipment->setExpiration($equipment->expiration!=null?date_create_from_format('d/m/Y H:i:s',$equipment->expiration):null);
   $workerEquipment->setObservations($equipment->observations);
   $workerEquipment->setDeliverydate(date_create_from_format('d/m/Y H:i:s',$equipment->deliverydate));
   $workerEquipment->setDateadd(new \DateTime());
   $workerEquipment->setDateupd(new \DateTime());
   $workerEquipment->setActive(1);
   $workerEquipment->setDeleted(0);

   $this->getDoctrine()->getManager()->persist($workerEquipment);
   $this->getDoctrine()->getManager()->flush();
   return new JsonResponse(["result"=>1, "id"=>$workerEquipment->getId()]);
  }

 /**
 * @Route("/api/HR/equipments/elements/{id}/get", name="getEquipmentsCategories", defaults={"id"=0})
 */
 public function getEquipmentsCategories($id, RouterInterface $router,Request $request){
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if($id==0) $id=null;
  $categoriesRepository=$this->getDoctrine()->getRepository(HREquipmentCategories::class);
  $equipmentsRepository=$this->getDoctrine()->getRepository(HREquipments::class);
  $category = $categoriesRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
  $categories = $categoriesRepository->findBy(["parent"=>$id, "active"=>1, "deleted"=>0]);
  $equipments = $equipmentsRepository->findBy(["company"=>$this->getUser()->getCompany(), "category"=>$category, "active"=>1, "deleted"=>0]);
  $responseCategories=Array();
  foreach($categories as $itemCategory){
    $item['id']=$itemCategory->getId();
    $item['name']=$itemCategory->getname();
    $item['icon']=$itemCategory->getIcon();
    $responseCategories[]=$item;
  }
  $responsEquipments=Array();
  foreach($equipments as $equipment){
    $item['id']=$equipment->getId();
    $item['name']=$equipment->getname();
    $item['requireserial']=$equipment->getRequireserial();
    $item['requiresize']=$equipment->getRequiresize();
    $item['requireexpiration']=$equipment->getRequireexpiration();
    $responsEquipments[]=$item;
  }

  return new JsonResponse(["parent"=> $category!=null?(($category->getParent()!=null)?$category->getParent()->getId():0):'',"categories"=>$responseCategories, "equipments"=>$responsEquipments]);
 }

 /**
 * @Route("/api/HR/equipments/report/{id}/print", name="printEquipmentReport")
 */
 public function printEquipmentReport($id, RouterInterface $router,Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $workerEquipmentsRepository=$this->getDoctrine()->getRepository(HRWorkerEquipment::class);
   $document=$workerEquipmentsRepository->findOneBy(["id"=>$id, "deleted"=>0]);
   if(!$document) return new JsonResponse(["result"=>-1]);
	 $reportsUtils = new HREquipmentsReports();
   $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$document->getId(), "user"=>$this->getUser(), "document"=>$document];
   $pdf=$reportsUtils->create($params);
 }


 /**
  * @Route("/{_locale}/HR/equipments/data/{id}/{action}/{idworker}", name="dataEquipments", defaults={"id"=0, "action"="read", "idworker"="0"})
  */
  public function data($id, $action, $idworker, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/WorkerEquipment.json";
   $utils = new GlobaleFormUtils();
   $utilsObj=new $this->utilsClass();
   $workerEquipmentsRepository=$this->getDoctrine()->getRepository(HRWorkerEquipment::class);
   $workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
   if($id==0){
     if($idworker==0 ) $idworker=$request->query->get('worker');
     if($idworker==0 || $idworker==null) $idworker=$request->request->get('id-parent',0);
     $worker = $workerRepository->find($idworker);
   }	else $obj = $workerEquipmentsRepository->find($id);

   $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$id==0?$worker:$obj->getWorker()];
   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
                      method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);

  //-----------------   CLOUD ----------------------
  $utilsCloud = new CloudFilesUtils();
  $path="HREquipments";
  $templateLists=["id"=>$path,"list"=>[$utilsCloud->formatList($this->getUser(),$path,$id)],"types"=>["Garantía", "Acta de entrega", "Factura", "Otros"],"path"=>$this->generateUrl("cloudUpload",["id"=>$id, "path"=>$path])];
  //------------------------------------------------

  return $utils->make($id, $this->class, $action, "formWorkEquipments", "modal", "@Globale/form.html.twig", null, null, ["filesHRWorkEquipments"=>["template"=>"@Cloud/genericlistfiles.html.twig", "vars"=>[
    "cloudConstructor"=>$templateLists,
    "scanner"=>$this->getUser()->getScanner(),
    'path' => $path,
    'id' => $id,
    'module' => "HR"
    ]]]);
 }

}
