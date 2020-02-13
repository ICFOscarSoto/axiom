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

class HREquipmentsController extends Controller
{

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
   $workerEquipment->setObservations($equipment->observations);
   $workerEquipment->setDeliverydate(date_create_from_format('d/m/Y H:i:s',$equipment->deliverydate));
   $workerEquipment->setDateadd(new \DateTime());
   $workerEquipment->setDateupd(new \DateTime());
   $workerEquipment->setActive(1);
   $workerEquipment->setDeleted(0);

   $this->getDoctrine()->getManager()->persist($workerEquipment);
   $this->getDoctrine()->getManager()->flush();
   return new JsonResponse(["result"=>1]);
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

}
