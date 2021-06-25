<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPInfoStocks;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPInfoStocksUtils;


class ERPInfoStocksController extends Controller
{


  /**
   * @Route("/es/infoStocks/{id}/list", name="infoStockslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product = $productRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $class=ERPInfoStocks::class;
    $repository = $manager->getRepository($class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/InfoStocks.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"product", "value"=>$product]]);
    return new JsonResponse($return);
  }


    /**
     * @Route("/{_locale}/listInfoStocks/{id}", name="listInfoStocks", defaults={"id"=0})
     */
    public function listInfoStocks($id, Request $request){
      $listInfoStocks = new ERPInfoStocksUtils();
      $formUtils=new GlobaleFormUtils();
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "product"=>null];
      $formUtils->initialize($this->getUser(), ERPInfoStocks::class,
      dirname(__FILE__)."/../Forms/InfoStocks.json", $request, $this, $this->getDoctrine(),
      method_exists($listInfoStocks,'getExcludedForm')?$listInfoStocks->getExcludedForm($params):[],
      method_exists($listInfoStocks,'getIncludedForm')?$listInfoStocks->getIncludedForm($params):[]);
      $templateForms[]=$formUtils->formatForm('infoStocks', true, null, ERPInfoStocks::class);
      return $this->render('@Globale/list.html.twig', array(
        'listConstructor' => $listInfoStocks->formatListByProduct($id),
        'id_object'=>$id,
        'forms' => $templateForms
      ));
    }

}
