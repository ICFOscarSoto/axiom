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
use App\Modules\ERP\Entity\ERPSuppliers;
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
     $supplier=$id==0?$product->getSupplier():$obj->getProduct()->getSupplier();
     $defaultSupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
     if($obj->getSupplier()==null) $default=$defaultSupplier->findOneBy(['id'=>$supplier->getId()]);
      else $default=$obj->getSupplier();
     $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "supplier"=>$default, "product"=>$id==0?$product:$obj->getProduct()];
     $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
                            method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
     return $utils->make($id, ERPReferences::class, $action, "formProducts", "modal");
    }

    /**
     * @Route("/{_locale}/listReferences/{id}", name="listReferences", defaults={"id"=0})
     */
    public function listReferences($id, Request $request){
      $listReferences = new ERPReferencesUtils();
      $formUtils=new GlobaleFormUtils();
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),"supplier"=>null, "product"=>null, "productvariant"=>null];
      $formUtils->initialize($this->getUser(), ERPReferences::class, dirname(__FILE__)."/../Forms/References.json", $request, $this, $this->getDoctrine(),method_exists($listReferences,'getExcludedForm')?$listReferences->getExcludedForm($params):[],method_exists($listReferences,'getIncludedForm')?$listReferences->getIncludedForm($params):[]);
      $templateForms[]=$formUtils->formatForm('References', true, null, ERPReferences::class);
      return $this->render('@Globale/list.html.twig', array(
        'listConstructor' => $listReferences->formatListByProduct($id),
        'id_object'=>$id,
        'forms' => $templateForms
      ));
    }

}
