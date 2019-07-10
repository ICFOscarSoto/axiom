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
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;


class ERPEAN13Controller extends Controller
{


  /**
   * @Route("/api/EAN13/{id}/list", name="EAN13list")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product = $productRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $class=ERPEAN13::class;
    $repository = $manager->getRepository($class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/EAN13.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"product", "value"=>$product]]);
    return new JsonResponse($return);

  }

  /**
   * @Route("/{_locale}/EAN13/data/{id}/{action}/{idproduct}", name="dataEAN13", defaults={"id"=0, "action"="read", "idproduct"=0})
   */
   public function data($id, $action, $idproduct, Request $request)
   {
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/EAN13.json";
   $utils = new GlobaleFormUtils();
   $utilsObj=new ERPEAN13Utils();
   $defaultProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
   $EAN13Repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
   $obj=new ERPEAN13();
   if($id==0){
    if($idproduct==0 ) $idproduct=$request->query->get('idproduct');
    if($idproduct==0 || $idproduct==null) $idproduct=$request->request->get('id-parent',0);
    $product = $defaultProduct->find($idproduct);
   }else $obj = $EAN13Repository->find($id);
   $supplier=$id==0?$product->getSupplier():$obj->getProduct()->getSupplier();
   $defaultSupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
   $default=$defaultSupplier->findOneBy(['id'=>2]);
   dump($supplier);
   dump($default);
   $obj->setSupplier($default);
   dump($obj);
   $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "product"=>$id==0?$product:$obj->getProduct()];
   $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
                          method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
   $make=$utils->make($id, ERPEAN13::class, $action, "formProducts", "modal");

   return $make;
  }






}
