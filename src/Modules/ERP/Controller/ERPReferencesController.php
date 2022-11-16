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
use App\Modules\ERP\Entity\ERPProductsVariants;
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
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"productvariant.product", "value"=>$product]]);
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
     $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
     $productVariantRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
     $referencesRepository=$this->getDoctrine()->getRepository(ERPReferences::class);
     $product=null;
     $productvariant=null;
     $reference=new ERPReferences();
     if($id==0){
      $form = $request->request->get('form');
      if ($form && $form['productvariant'])
        $productvariant = $productVariantRepository->findOneBy(['id'=>$form['productvariant']]);
      if ($productvariant==null){
        if($idproduct==null || $idproduct==0)
          $idproduct=$request->query->get('idproduct');
        if($idproduct==null || $idproduct==0)
          $idproduct=$request->request->get('id-parent',0);
        $product = $productRepository->find($idproduct);
        $productvariant = $productVariantRepository->findOneBy(['product'=>$product, 'variant'=>null]);
      }else
        $product = $productvariant->getProduct();
     }else{
      $reference = $referencesRepository->find($id);
      $productvariant = $reference->getProductvariant();
      $product = $productvariant->getProduct();
     }
     $supplier=$id==0?$product->getSupplier():$reference->getSupplier();
     $customer=$id==0?null:$reference->getCustomer();

     $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),
     "supplier"=>$supplier,
     "customer"=>$customer,
     "product"=>$product,
     "productvariant"=>$productvariant];
     $utils->initialize($this->getUser(), $reference, $template, $request, $this, $this->getDoctrine(),
                            method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
                            method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
     if($id==0) $utils->values(["product"=>$product]);
     $make=$utils->make($id, ERPReferences::class, $action, "references", "modal");
     return $make;
    }

    /**
     * @Route("/{_locale}/listReferences/{id}", name="listReferences", defaults={"id"=0})
     */
    public function listReferences($id, Request $request){
      $listReferences = new ERPReferencesUtils();
      $formUtils=new GlobaleFormUtils();
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),"supplier"=>null, "product"=>null, "productvariant"=>null];
      $formUtils->initialize($this->getUser(), ERPReferences::class,
      dirname(__FILE__)."/../Forms/References.json", $request, $this, $this->getDoctrine(),
      method_exists($listReferences,'getExcludedForm')?$listReferences->getExcludedForm($params):[],
      method_exists($listReferences,'getIncludedForm')?$listReferences->getIncludedForm($params):[]);
      $templateForms[]=$formUtils->formatForm('references', true, null, ERPReferences::class);
      return $this->render('@Globale/list.html.twig', array(
        'listConstructor' => $listReferences->formatListByProduct($id),
        'id_object'=>$id,
        'forms' => $templateForms
      ));
    }

}
