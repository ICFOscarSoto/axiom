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

class HREquipmentsController extends Controller
{

 /**
 * @Route("/api/HR/equipments/elements/{id}/get", name="getEquipmentsCategories", defaults={"id"=0})
 */
 public function index($id, RouterInterface $router,Request $request){
  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  if($id==0) $id=null;
  $categoriesRepository=$this->getDoctrine()->getRepository(HREquipmentCategories::class);
  $equipmentsRepository=$this->getDoctrine()->getRepository(HREquipments::class);
  $category = $categoriesRepository->findBy(["id"=>$id, "active"=>1, "deleted"=>0]);
  $categories = $categoriesRepository->findBy(["parent"=>$id, "active"=>1, "deleted"=>0]);
  $equipments = $equipmentsRepository->findBy(["company"=>$this->getUser()->getCompany(), "category"=>$category, "active"=>1, "deleted"=>0]);
  $responseCategories=Array();
  foreach($categories as $category){
    $item['id']=$category->getId();
    $item['name']=$category->getname();
    $item['icon']=$category->getIcon();
    $responseCategories[]=$item;
  }

  $responsEquipments=Array();
  foreach($equipments as $equipment){
    $item['id']=$equipment->getId();
    $item['name']=$equipment->getname();
    $responsEquipments[]=$item;
  }

  return new JsonResponse(["categories"=>$responseCategories, "equipments"=>$responsEquipments]);

 }

}
