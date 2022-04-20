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
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsVariantsUtils;
use \App\Modules\ERP\Utils\ERPPrestashopUtils;


class ERPProductsVariantsController extends Controller
{

  private $class=ERPProductsVariants::class;
  private $utilsClass=ERPProductsVariantsUtils::class;


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

  /**
  * @Route("/{_locale}/ProductsVariants/{id}/delete", name="deleteProductVariant")
  */
  public function delete($id){
    $this->denyAccessUnlessGranted('ROLE_GLOBAL');
    $entityUtils=new GlobaleEntityUtils();
    $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());

    $this_url="https://www.ferreteriacampollano.com";
    $auth = base64_encode("6TI5549NR221TXMGMLLEHKENMG89C8YV");
    $context = stream_context_create(["http" => ["header" => "Authorization: Basic $auth"]]);
    $productsVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
    $productsVariant=$productsVariantsRepository->find($id);


    // MIRAMOS SI EXISTE EL PRODUCTO EN PRESTASHOP OBTENIENDO EL ID
    $xml_string=file_get_contents($this_url."/api/products/?filter[reference]=".$productsVariant->getProduct()->getCode(), false, $context);
    $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOCDATA);
    $id_prestashop=NULL;
    $id_prestashop=$xml->products->product['id'];



    //comprobamos si existe la variante en Prestashop
    $xml_string_variant=file_get_contents($this_url."/api/product_option_values/?display=[id]&filter[name]=".$productsVariant->getVariantvalue()->getName(), false, $context);
    $xml_variant = simplexml_load_string($xml_string_variant, 'SimpleXMLElement', LIBXML_NOCDATA);
    $id_attribute=$xml_variant->product_option_values->product_option_value->id;
    //la variante sí que existe en prestashop, luego hay que comprobar si la combinación variante-producto también existe y borrarla
    if($id_attribute!=NULL)
    {
      //obtenemos todas las combinaciones asociadas al producto.
      $xml_string_product_combinations=file_get_contents($this_url."/api/combinations/?display=[id]&filter[id_product]=".$id_prestashop, false, $context);
      $xml_product_combinations = simplexml_load_string($xml_string_product_combinations, 'SimpleXMLElement', LIBXML_NOCDATA);

    //  dump($xml_product_combinations);
      foreach($xml_product_combinations->combinations->combination as $comb)
      {
        $array=array_unique((array) $comb);

        //obtenemos cada combinación por el ID.
        $xml_string_product_combination=file_get_contents($this_url."/api/combinations/".$array["id"], false, $context);
        $xml_product_combination = simplexml_load_string($xml_string_product_combination, 'SimpleXMLElement', LIBXML_NOCDATA);
        $id_attribute_ps=$xml_product_combination->combination->associations->product_option_values->product_option_value;
        $array_id_attribute=array_unique((array) $id_attribute_ps);
    //    dump($array_id_attribute["id"]."--".$id_attribute);
        if($array_id_attribute["id"]==$id_attribute){
          $prestashopUtils= new ERPPrestashopUtils();
          $prestashopUtils->deleteCombination($xml_product_combination,$array["id"]);
          continue;
        }
    }
    }



    return new JsonResponse(array('result' => $result));
  }

  /**
  * @Route("/api/getWSProductVariants/{product_id}", name="getWSProductVariants", defaults={"product_id"=0})
  */
  public function getWSProductVariants(Request $request, $product_id)
  {
    // Variantes de un producto
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $productVariantsRepository			= $this->getDoctrine()->getRepository(ERPProductsVariants::class);
    $result 	 = [];
    $variants = $productVariantsRepository->getWSProductVariants($product_id);
    if ($variants!=null){
      $head = [];
      $item = [];
  		$item["id"]			  = '0~Variante...';
  		$item["name"]		  ='Variante...';
  		$head[]=$item;
      $result = $variants;
      if ($result['data'] != null)
        $result['data'] = array_merge($head,$result['data']);
      else
        $result['data'] = $head;
    }
    return new JsonResponse($result);
  }

  /**
  * @Route("/api/getWSProductVariantPrice/{supplier_id}/{product_id}/{variant_id}/{quantity}", name="getWSProductVariantPrice", defaults={"supplier_id"=0, "product_id"=0, "variant_id"=0, "quantity"=1})
  */
  public function getWSProductVariantPrice($supplier_id, $product_id, $variant_id, $quantity)
  {
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $productVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
     $result = [];
     $product = $productVariantsRepository->getProductVariantBySupplier($supplier_id, $product_id, $variant_id, $quantity);
     if ($product!=null){
       $result = $product;
     }
     return new JsonResponse($result);
  }

  /**
  * @Route("/api/getWSProductVariantPriceStock/{supplier_id}/{product_id}/{variant_id}/{quantity}/{store_id}", name="getWSProductVariantPriceStock", defaults={"supplier_id"=0, "product_id"=0, "variant_id"=0, "quantity"=1, "store_id"=0})
  */
  public function getWSProductVariantPriceStock($supplier_id, $product_id, $variant_id, $quantity, $store_id)
  {
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $productVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
     $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
     $erpStocksRepository = $this->getDoctrine()->getRepository(ERPStocks::class);
     $result = [];
     $product = $productVariantsRepository->getProductVariantBySupplier($supplier_id, $product_id, $variant_id, $quantity);
     if ($product!=null){
       $result = $product;
       if ($result!=null && count($result)>0){
         $aproduct = explode('~',$product_id);
         if (count($aproduct)>1)
           $product_id = $aproduct[0];
         $oproduct = $productRepository->find($product_id);
         if ($oproduct!=null && $oproduct->getStockcontrol()){
           $avariant = explode('~',$variant_id);
           if (count($avariant)>1)
             $variant_id = $avariant[0];
           $astore = explode('~',$store_id);
           if (count($astore)>1)
             $store_id = $astore[0];
           $stock = $erpStocksRepository->getStock($product_id,($oproduct->getGrouped()?$variant_id:null), $store_id);
           for($i=0; $i<count($result); $i++){
             if ($stock!=null){
               foreach($stock as $key=>$value){
                 if ($value==null || $value=='')
                   $value=0;
                 $result[$i][$key] = $value;
               }
             }else{
               $result[$i]['stock'] = 0;
               $result[$i]['minstock'] = 0;
               $result[$i]['stockpedingreceive'] = 0;
               $result[$i]['stockpedingserve'] = 0;
               $result[$i]['stockvirtual'] = 0;
               $result[$i]['stockt'] = 0;
               $result[$i]['stockpedingreceivet'] = 0;
               $result[$i]['stockpedingservet'] = 0;
               $result[$i]['stockvirtualt'] = 0;
             }
           }
         }
       }
     }
     return new JsonResponse($result);
  }
}
