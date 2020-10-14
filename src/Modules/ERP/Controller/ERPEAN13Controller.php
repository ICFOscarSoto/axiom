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
use App\Modules\ERP\Entity\ERPProductsVariants;
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
   //$default=$defaultSupplier->findOneBy(['id'=>$supplier->getId()]);
   if($obj->getSupplier()==null) $default=$defaultSupplier->findOneBy(['id'=>$supplier->getId()]);
    else $default=$obj->getSupplier();


   $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),
   "supplier"=>$default, "product"=>$id==0?$product:$obj->getProduct(), "productvariant"=>$id==0?null:$obj->getProductVariant()];

   $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
                          method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
  if($id==0) $utils->values(["product"=>$product]);

   $make=$utils->make($id, ERPEAN13::class, $action, "EAN13", "modal");

   return $make;
  }

  /**
   * @Route("/{_locale}/listEAN13/{id}", name="listEAN13", defaults={"id"=0})
   */
  public function listEAN13($id, Request $request){
    $listEAN13 = new ERPEAN13Utils();
    $formUtils=new GlobaleFormUtils();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),"supplier"=>null, "product"=>null, "productvariant"=>null];
    $formUtils->initialize($this->getUser(), ERPEAN13::class, dirname(__FILE__)."/../Forms/EAN13.json", $request, $this, $this->getDoctrine(),method_exists($listEAN13,'getExcludedForm')?$listEAN13->getExcludedForm($params):[],method_exists($listEAN13,'getIncludedForm')?$listEAN13->getIncludedForm($params):[]);
		$templateForms[]=$formUtils->formatForm('EAN13', true, null, ERPEAN13::class);
    return $this->render('@Globale/list.html.twig', array(
      'listConstructor' => $listEAN13->formatListByProduct($id),
      'id_object'=>$id,
      'forms' => $templateForms
    ));
  }

  /**
   * @Route("/api/ERP/barcode/add/{id}/{type}", name="addBarcode", defaults={"id"=0, "type"=1})
   */
  public function addBarcode($id, $type, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  	$repositoryEAN=$this->getDoctrine()->getRepository(ERPEAN13::class);
  	$repositoryProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
    $Variantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
    $barcode=$request->request->get('barcode',null);
    if($barcode===false) return new JsonResponse(["result"=>-2, "text"=>"El código de barras no puede ser nulo"]);
    //check if product exists
    if($type==1)
      $product=$repositoryProduct->findOneBy(["id"=>$id, "company"=> $this->getUser()->getCompany(), "deleted"=>0]);
      else{
        $variant=$Variantsrepository->findOneBy(["id"=>$id, "deleted"=>0]);
        if($variant) $product=$variant->getProduct();
          else return new JsonResponse(["result"=>-1, "text"=>"El producto no existe"]);
      }
    if(!$product) return new JsonResponse(["result"=>-1, "text"=>"El producto no existe"]);
    if($product->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(["result"=>-1, "text"=>"El producto no existe"]);
    //check if barcode exists
    $tempbarcode=$repositoryEAN->findOneBy(["name"=>$barcode, "deleted"=>0]);
    if($tempbarcode) return new JsonResponse(["result"=>-3, "text"=>"El código de barras ya esta en uso por este u otro producto"]);
    //check if product has supplier
    //if($product->getSupplier()===null) return new JsonResponse(["result"=>-4, "text"=>"El producto no tiene proveedor"]);

    $newBarcode=new ERPEAN13();
    $newBarcode->setSupplier($product->getSupplier());
    $newBarcode->setProduct($product);
    $newBarcode->setName($barcode);
    $newBarcode->setAuthor($this->getUser());
    $newBarcode->setDateadd(new \Datetime());
    $newBarcode->setDateupd(new \Datetime());
    $newBarcode->setActive(1);
    $newBarcode->setDeleted(0);
    $newBarcode->setType(1);
    if($type==2)
      $newBarcode->setProductvariant($variant);

    $this->getDoctrine()->getManager()->persist($newBarcode);
    $this->getDoctrine()->getManager()->flush();

    //Create in Navision
    $params=["axiom_id"=>$newBarcode->getId(), "product_code"=>$product->getCode(), "supplier_code"=>$newBarcode->getSupplier()===null?"P99999":$newBarcode->getSupplier()->getCode(), "barcode"=>$newBarcode->getName()];
    $result_json=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-createEAN13.php?json='.json_encode($params));
    $result=json_decode($result_json, true);
    if($result["result"]==1)
        return new JsonResponse(["result"=>1]);
    else{
      $this->getDoctrine()->getManager()->remove($newBarcode);
      $this->getDoctrine()->getManager()->flush();
      return new JsonResponse($result);
    }
  }

  /**
   * @Route("/api/ERP/barcode/variant/{id}/{idvariant}", name="changeBarcodeVariant", defaults={"id"=0, "type"=1})
   */
  public function changeBarcodeVariant($id, $idvariant, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $repositoryEAN=$this->getDoctrine()->getRepository(ERPEAN13::class);
    $repositoryProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
    $Variantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
    $barcode=$request->request->get('barcode',null);
    if($barcode===false) return new JsonResponse(["result"=>-1, "text"=>"El código de barras no puede ser nulo"]);
    $product=$repositoryProduct->findOneBy(["id"=>$id, "company"=> $this->getUser()->getCompany(), "deleted"=>0]);
    if(!$product) return new JsonResponse(["result"=>-2, "text"=>"El producto no existe"]);
    $variant=$Variantsrepository->findOneBy(["id"=>$idvariant, "product"=>$product, "deleted"=>0]);
    if(!$variant) return new JsonResponse(["result"=>-3, "text"=>"La variante no existe"]);
    $ean=$repositoryEAN->findOneBy(["name"=>$barcode, "product"=>$product, "deleted"=>0]);
    if(!$ean) return new JsonResponse(["result"=>-4, "text"=>"Codigo de barras incorrecto"]);
    $ean->setProductvariant($variant);
    $this->getDoctrine()->getManager()->persist($ean);
    $this->getDoctrine()->getManager()->flush();
    return new JsonResponse(["result"=>1, "text"=>""]);

  }

  /**
  * @Route("/{_locale}/admin/ERP/barcode/{id}/delete", name="deleteEAN")
  */
  public function deleteEAN($id){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $entityUtils=new GlobaleEntityUtils();
    $repositoryEAN=$this->getDoctrine()->getRepository(ERPEAN13::class);
    $ean=$repositoryEAN->findOneBy(["id"=>$id]);
    if($ean && $ean->getName()!=null && $ean->getName()!=""){
      $deleteEAN=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-removeEAN13.php?barcode='.$ean->getName());
      $result_deleteEAN=json_decode($deleteEAN,true);
      if(isset($result_deleteEAN["result"]) && $result_deleteEAN["result"]==1)
        $result=$entityUtils->deleteObject($id, ERPEAN13::class, $this->getDoctrine());
        else return new JsonResponse(array('result' => -1));
    }else{
      return new JsonResponse(array('result' => -1));
    }
    return new JsonResponse(array('result' => $result));
  }


}
