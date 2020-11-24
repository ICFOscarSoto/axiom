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
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsVariantsUtils;


class ERPProductsVariantsController extends Controller
{


  /**
   * @Route("/api/ProductsVariants/{id}/list", name="ProductsVariantslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product = $productRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $class=ERPProductsVariants::class;
    $repository = $manager->getRepository($class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductsVariants.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"product", "value"=>$product]]);
    return new JsonResponse($return);

  }

  /**
   * @Route("/{_locale}/ProductsVariants/data/{id}/{action}/{idproduct}", name="dataProductsVariants", defaults={"id"=0, "action"="read", "idproduct"=0})
   */
   public function data($id, $action, $idproduct, Request $request)
   {
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $template=dirname(__FILE__)."/../Forms/ProductsVariants.json";
   $utils = new GlobaleFormUtils();
   $utilsObj=new ERPProductsVariantsUtils();
   $defaultProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
   $productsVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
   $obj=new ERPProductsVariants();
   if($id==0){
    if($idproduct==0 ) $idproduct=$request->query->get('idproduct');
    if($idproduct==0 || $idproduct==null) $idproduct=$request->request->get('id-parent',0);

    $product = $defaultProduct->find($idproduct);
   }else $obj = $productsVariantsRepository->find($id);

   $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),"product"=>$id==0?$product:$obj->getProduct()];

   $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
                          method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
   if($id==0) $utils->values(["product"=>$product]);

   $make=$utils->make($id, ERPProductsVariants::class, $action, "ProductsVariants", "modal");

   return $make;
  }

  /**
   * @Route("/{_locale}/listProductsVariants/{id}", name="listProductsVariants", defaults={"id"=0})
   */
  public function listProductsVariants($id, Request $request){
    $listProductsVariants = new ERPProductsVariantsUtils();
    $formUtils=new GlobaleFormUtils();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "product"=>null];
    $formUtils->initialize($this->getUser(), ERPProductsVariants::class, dirname(__FILE__)."/../Forms/ProductsVariants.json", $request, $this, $this->getDoctrine(),method_exists($listProductsVariants,'getExcludedForm')?$listProductsVariants->getExcludedForm($params):[],method_exists($listProductsVariants,'getIncludedForm')?$listProductsVariants->getIncludedForm($params):[]);
		$templateForms[]=$formUtils->formatForm('ProductsVariants', true, null, ERPProductsVariants::class);
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    return $this->render('@Globale/list.html.twig', array(
      'listConstructor' => $listProductsVariants->formatListByProduct($id),
      'id_object'=>$id,
      'userData' => $userdata,
      'forms' => $templateForms
    ));
  }


}
