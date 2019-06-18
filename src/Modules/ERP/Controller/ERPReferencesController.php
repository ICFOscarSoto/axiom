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
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPReferencesUtils;


class ERPReferencesController extends Controller
{


  /**
   * @Route("/api/references/{id}/list", name="referenceslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product = $productRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $class=ERPReferences::class;
    $repository = $manager->getRepository($class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/References.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"product", "value"=>$product]]);
    return new JsonResponse($return);
  }


    /**
     * @Route("/{_locale}/references/data/{id}/{action}/{idproduct}", name="datareferences", defaults={"id"=0, "action"="read", "idproduct"=0})
     */
     public function data($id, $action, $idproduct, Request $request)
     {
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $template=dirname(__FILE__)."/../Forms/References.json";
     $utils = new GlobaleFormUtils();
     $utilsObj=new ERPReferencesUtils();
     $defaultProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
     $EAN13Repository=$this->getDoctrine()->getRepository(ERPReferences::class);
     $obj=new ERPReferences();
     if($id==0){
      if($idproduct==0 ) $idproduct=$request->query->get('idproduct');
      if($idproduct==0 || $idproduct==null) $idproduct=$request->request->get('id-parent',0);
      $product = $defaultProduct->find($idproduct);
    }else $obj = $EAN13Repository->find($id);

     $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "product"=>$id==0?$product:$obj->getProduct()];
     $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
                            method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
     return $utils->make($id, ERPReferences::class, $action, "formProducts", "modal");
    }



}
