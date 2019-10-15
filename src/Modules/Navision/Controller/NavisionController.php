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
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\AERP\Utils\AERPCustomersUtils;
use App\Modules\ERP\Reports\ERPInvoiceReports;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPEAN13;

class NavisionController extends Controller
{
  private $url="http://icf.edindns.es:9000/";
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


      if (isset($product["EAN13"])) {
        $EAN13=$product["EAN13"];
        foreach ($EAN13 as $key=>$value){
          if (strlen($key)==13) {
            $EAN13Entity=new ERPEAN13();
            $EAN13Entity->setProduct($productEntity);
            $EAN13Entity->setName($key);
            $EAN13Entity->setType(1);
            $EAN13Entity->setDateupd(new \DateTime());
            $EAN13Entity->setDateadd(new \DateTime());
            $EAN13Entity->setSupplier($supplierEntity);
            $pm=$this->getDoctrine()->getManager();
            $pm->persist($EAN13Entity);
            $pm->flush();
          }
        }
    }
}

      return new Response(null);
    }
}
