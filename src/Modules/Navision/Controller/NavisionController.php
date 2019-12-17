<?php

namespace App\Modules\Navision\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\AERP\Entity\AERPCustomers;
use App\Modules\AERP\Entity\AERPCustomerGroups;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\AERP\Utils\AERPCustomersUtils;
use App\Modules\ERP\Reports\ERPInvoiceReports;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPAttributeNames;
use App\Modules\ERP\Entity\ERPAttributesValues;
use App\Modules\ERP\Entity\ERPProductsAttributes;
use App\Modules\Security\Utils\SecurityUtils;
use \DateTime;

class NavisionController extends Controller
{
  private $url="http://192.168.1.250:9000/";
  private $module="Navision";

  /**
   * @Route("/api/navision/invoices", name="navisionInvoices")
   */
   public function navisionInvoices(Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
     $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
     $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
     $start=new DateTime('first day of this month');
     $end=new DateTime('last day of this month');
     $invoices=file_get_contents($this->url.'navisionExport/do-NAVISION-invoice-list.php?start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
     return $this->render('@Navision/invoices.html.twig', [
       "interfaceName" => "Facturas",
       'optionSelected' => "navisionInvoices",
       'menuOptions' =>  $menurepository->formatOptions($userdata),
       'breadcrumb' =>  "navisionInvoices",
       'userData' => $userdata,
       'start' => $start->format("d/m/Y"),
       'end' => $end->format("d/m/Y"),
       'basiclist' => json_decode ($invoices, true)
     ]);
   }

   /**
    * @Route("/api/navision/get/invoices", name="navisionGetInvoices")
    */
    public function navisionGetInvoices(Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $start=$request->request->get("start");
      $end=$request->request->get("end");
      $start=date_create_from_format('d/m/Y',$start);
      $end=date_create_from_format('d/m/Y',$end);
      $invoices=file_get_contents($this->url.'navisionExport/do-NAVISION-invoice-list.php?start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
      return new Response($invoices);
    }

  /**
   * @Route("/api/navision/invoice/print/{id}", name="navisionPrintInvoice", defaults={"id"=0})
   */
   public function navisionPrintInvoice($id, Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     /*$ids=$request->request->get('ids');
     $ids=explode(",",$ids);*/
     $invoice=file_get_contents($this->url.'navisionExport/do-NAVISION-invoice.php?invoices=["'.$id.'"]');
     //dump($this->url.'navisionExport/do-NAVISION-invoice.php?invoices=['.$id.']');
     $ids=$id;
     $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "ids"=>$ids, "user"=>$this->getUser(), "invoices"=>json_decode($invoice, true)];
     $reportsUtils = new ERPInvoiceReports();
     //dump($invoice);
     $pdf=$reportsUtils->create($params);
     return new Response("", 200, array('Content-Type' => 'application/pdf'));
   }


   /**
    * @Route("/api/navision/product/importInaer", name="navisionImportInaer")
    */
    public function navisionImportInaer(Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $invoice=file_get_contents($this->url.'navisionExport/do-NAVISION-getProdcutsInaer.php');
      $products=json_decode($invoice, true);
      $products=$products[0];
      //dump($products["products"]);
      foreach ($products["products"] as $key){
        $this->navisionImportProduct($key["code"], $request);
      }
      return new Response(null);
    }





    /**
     * @Route("/api/navision/product/importAttributes", name="navisionImportAttributes")
     */
    public function navisionImportAttributes(Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $invoice=file_get_contents($this->url.'navisionExport/do-NAVISION-attributes.php');
    $attributes=json_decode($invoice, true);
    foreach ($attributes as $attribute){
    $repository=$this->getDoctrine()->getRepository(ERPAttributeNames::class);
          if ($attribute["Description"]!=null){
            $attributeExists=$repository->findOneBy(["name"=>$attribute["Description"]]);
            if ($attributeExists==null){
              $attributeEntity= new ERPAttributeNames();
              $attributeEntity->setName($attribute["Description"]);
              $defaultCompany=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
              $default=$defaultCompany->findOneBy(['vat'=>"B02290443"]);
              $attributeEntity->setCompany($default);
              $attributeEntity->setDateupd(new \DateTime());
              $attributeEntity->setDateadd(new \DateTime());
              $pm=$this->getDoctrine()->getManager();
              $pm->persist($attributeEntity);
              $pm->flush();
            } else $attributeEntity=$attributeExists;
          if (isset($attribute["ValorAtributo"])) foreach($attribute["ValorAtributo"] as $value){
            $valueEntity= new ERPAttributesValues();
            $valueEntity->setName($value);
            $valueEntity->setAttributeName($attributeEntity);
            $defaultCompany=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
            $default=$defaultCompany->findOneBy(['vat'=>"B02290443"]);
            $valueEntity->setCompany($default);
            $valueEntity->setDateupd(new \DateTime());
            $valueEntity->setDateadd(new \DateTime());
            $pm=$this->getDoctrine()->getManager();
            $pm->persist($valueEntity);
            $pm->flush();
          }

        }
      }
    return new Response("Se han importado los atributos");
   }

   /**
    * @Route("/api/navision/product/import/{id}", name="navisionImportProduct", defaults={"id"=0})
    */
    public function navisionImportProduct($id, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $invoice=file_get_contents($this->url.'navisionExport/do-NAVISION-product.php?products=["'.$id.'"]');
      $product=json_decode($invoice, true);
      $product=$product[$id];
      $repository=$this->getDoctrine()->getRepository(ERPProducts::class);
      $productExists=$repository->findOneBy(["code"=>$id]);
      if ($productExists==null) {
      //Creamos el producto en la base de datos
      $productEntity= new ERPProducts();
      $productEntity->setCode($id);
      $productEntity->setName($product["Description"]);
      $productEntity->setWeight($product["Weight"]);
      $defaultCompany=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
 		  $default=$defaultCompany->findOneBy(['vat'=>"B02290443"]);
      $productEntity->setCompany($default);
      //TODO:cambiar categorias, proveedores, etc
      $defaultCategory=$this->getDoctrine()->getRepository(ERPCategories::class);
      $default=$defaultCategory->findOneBy(['name'=>"ParaBabcock"]);
      $productEntity->setCategory($default);
      $defaultSupplier=$this->getDoctrine()->getRepository(ERPSuppliers::class);
      $supplierEntity=$defaultSupplier->findOneBy(['name'=>"ParaBabcock"]);
      $productEntity->setSupplier($supplierEntity);
      //$productEntity->setTaxes();
      //$productEntity->setManufacturer();
      $productEntity->setPVPR($product["ShoppingPrice"]);
      $productEntity->setDateupd(new \DateTime());
      $productEntity->setDateadd(new \DateTime());
      $pm=$this->getDoctrine()->getManager();
      $pm->persist($productEntity);
      $pm->flush();
      } else $productEntity=$productExists;

      if (isset($product["EAN13"]) and strlen($product["EAN13"][0]["EAN13"])<13) {
        $EAN13=$product["EAN13"];
        foreach ($EAN13 as $key){
            $repositoryEAN13=$this->getDoctrine()->getRepository(ERPEAN13::class);
            $EAN13exists=$repositoryEAN13->findOneBy(["name"=>$key["EAN13"]]);
            if($EAN13exists==null) {
            $EAN13Entity=new ERPEAN13();
            $EAN13Entity->setProduct($productEntity);
            $EAN13Entity->setName($key["EAN13"]);
            $EAN13Entity->setType(1);
            $EAN13Entity->setDateupd(new \DateTime());
            $EAN13Entity->setDateadd(new \DateTime());
            $EAN13Entity->setSupplier($productEntity->getSupplier());
            $pm=$this->getDoctrine()->getManager();
            $pm->persist($EAN13Entity);
            $pm->flush();
          }
        }
      }

    /*  if (isset($product["atributos"])) {
        $attributes=$product["atributos"];
        foreach ($attributes as $key){
            $repositoryAttribute=$this->getDoctrine()->getRepository(ERPProductsAttributes::class);
            $attributeExists=$repositoryAttribute->findOneBy(["attributename"=>$key["descripcion"], "product"=>$productEntity]);
            if($attributeExists==null) {
            $attributeProductEntity=new ERPProductsAttributes();
            $attributeProductEntity->setProduct($productEntity);
            $repositoryAttributeName=$this->getDoctrine()->getRepository(ERPAttributeNames::class);
            $attributeName=$repositoryAttributeName->findOneBy(["name"=>$key["descripcion"]]);
            $attributeProductEntity->setAttributename($attributeName);
            $repositoryAttributeValue=$this->getDoctrine()->getRepository(ERPAttributesValues::class);
            $attributeValue=$repositoryAttributeValue->findOneBy(["name"=>$key["valor"]]);
            $attributeProductEntity->setAttributevalue($attributeValue);
            $defaultCompany=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
       		  $attributeCompany=$defaultCompany->findOneBy(['vat'=>"B02290443"]);
            $attributeProductEntity->setCompany($attributeCompany);
            $attributeProductEntity->setDateupd(new \DateTime());
            $attributeProductEntity->setDateadd(new \DateTime());
            $pm=$this->getDoctrine()->getManager();
            $pm->persist($attributeProductEntity);
            $pm->flush();
          }
        }
      } */



      return new Response("El producto se ha importado correctamente");
    }



    /**
     * @Route("/api/navision/supplier/import", name="navisionImportSupplier")
     */
     public function navisionImportSupplier(Request $request){
       //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       $json=file_get_contents($this->url.'navisionExport/do-NAVISION-getSuppliers.php');
       $objects=json_decode($json, true);
       $objects=$objects[0];
       //dump($products["products"]);
       $repositoryCountries=$this->getDoctrine()->getRepository(GlobaleCountries::class);
       $repositoryCurrencies=$this->getDoctrine()->getRepository(GlobaleCurrencies::class);
       $repositoryPaymentMethod=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
       $repositoryStates=$this->getDoctrine()->getRepository(GlobaleStates::class);
       $repository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
       foreach ($objects["class"] as $key=>$object){

         if($object["vat"]==null) continue;


         $obj=$repository->findOneBy(["code"=>$object["code"]]);
         if ($obj==null) {
           $obj=new ERPSuppliers();
           $obj->setCode($object["code"]);
           $obj->setCompany($this->getUser()->getCompany());
           $obj->setDateadd(new \Datetime());
           $obj->setDateupd(new \Datetime());
           $obj->setDeleted(0);
           $obj->setActive(1);
         }
          $country=$repositoryCountries->findOneBy(["alfa2"=>$object["country"]]);
          $state=$repositoryStates->findOneBy(["name"=>$object["state"]]);
          $currency=$repositoryCurrencies->findOneBy(["isocode"=>"EUR"]);
          $paymentMethod=$repositoryPaymentMethod->findOneBy(["id"=>1]);
          $obj->setVat($object["vat"]);
          $obj->setName($object["name"]);
          $obj->setSocialname($object["socialname"]);
          $obj->setAddress(rtrim($object["address1"]." ".$object["address2"]));
          $obj->setCity($object["city"]);
          $obj->setPostcode($object["postcode"]);
          $obj->setPhone($object["phone"]);
          $obj->setWeb($object["web"]);
          $obj->setEmail($object["email"]);
          $obj->setCountry($country);
          $obj->setState($state);
          $obj->setCurrency($currency);
          $obj->setPaymentMethod($paymentMethod);
          $this->getDoctrine()->getManager()->persist($obj);
          $this->getDoctrine()->getManager()->flush();


       }
       return new Response(null);
     }

     /**
      * @Route("/api/navision/customer/import", name="navisionImportCustomer")
      */
      public function navisionImportCustomer(Request $request){
        //$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $json=file_get_contents($this->url.'navisionExport/do-NAVISION-getCustomers.php');
        $objects=json_decode($json, true);
        $objects=$objects[0];
        //dump($products["products"]);
        $repositoryCountries=$this->getDoctrine()->getRepository(GlobaleCountries::class);
        $repositoryCurrencies=$this->getDoctrine()->getRepository(GlobaleCurrencies::class);
        $repositoryPaymentMethod=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
        $repositoryStates=$this->getDoctrine()->getRepository(GlobaleStates::class);
        $repository=$this->getDoctrine()->getRepository(ERPCustomers::class);
        foreach ($objects["class"] as $key=>$object){

          if($object["vat"]==null) continue;


          $obj=$repository->findOneBy(["code"=>$object["code"]]);
          if ($obj==null) {
            $obj=new ERPCustomers();
            $obj->setCode($object["code"]);
            $obj->setCompany($this->getUser()->getCompany());
            $obj->setDateadd(new \Datetime());
            $obj->setDateupd(new \Datetime());
            $obj->setDeleted(0);
            $obj->setActive(1);
          }
           $country=$repositoryCountries->findOneBy(["alfa2"=>$object["country"]]);
           $state=$repositoryStates->findOneBy(["name"=>$object["state"]]);
           $currency=$repositoryCurrencies->findOneBy(["isocode"=>"EUR"]);
           $paymentMethod=$repositoryPaymentMethod->findOneBy(["id"=>1]);
           $obj->setVat($object["vat"]);
           $obj->setName($object["name"]);
           $obj->setSocialname($object["socialname"]);
           $obj->setAddress(rtrim($object["address1"]." ".$object["address2"]));
           $obj->setCity($object["city"]);
           $obj->setPostcode($object["postcode"]);
           $obj->setPhone($object["phone"]);
           $obj->setWeb($object["web"]);
           $obj->setEmail($object["email"]);
           $obj->setCountry($country);
           $obj->setState($state);
           //$obj->setCurrency($currency);
           $obj->setPaymentMethod($paymentMethod);
           $this->getDoctrine()->getManager()->persist($obj);
           $this->getDoctrine()->getManager()->flush();


        }
        return new Response(null);
      }
}
