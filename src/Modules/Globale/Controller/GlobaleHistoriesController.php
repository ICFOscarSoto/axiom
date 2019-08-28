<?php

namespace App\Modules\Globale\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleHistories;

class GlobaleHistoriesController extends Controller
{


  /**
   * @Route("/api/global/histories/getchanges", name="getHistoriesChanges")
   */
  public function getHistoriesChanges(RouterInterface $router,Request $request){
  /*  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $entity=$request->request->get('entity');
    //$histories
    $entity_id=$request->request->get('entity_id');
    //$entity='App\Modules\HR\Entity\HRClocks';
    //$entity_id=5416;
    $historiesRepository=$this->getDoctrine()->getRepository(GlobaleHistories::class);
    $user = $this->getUser();
    $histories =	$historiesRepository->findBy(["entity"=>$entity, "entity_id"=>$entity_id, "company"=>$user->getCompany(), "active"=>true, "deleted"=>false]);
    $result=[];
    foreach($histories as $history){

    }
*/
    return new JsonResponse([]);
  }

}
