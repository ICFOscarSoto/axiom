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
use App\Modules\Navision\Entity\NavisionSync;
use App\Modules\Security\Utils\SecurityUtils;
use \DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
     $invoices=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice-list.php?start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));

     return $this->render('@Navision/invoices.html.twig', [
       "interfaceName" => "Facturas",
       'optionSelected' => "navisionInvoices",
       'menuOptions' =>  $menurepository->formatOptions($userdata),
       'breadcrumb' =>  "navisionInvoices",
       'userData' => $userdata,
       'start' => $start->format("d/m/Y"),
       'end' => $end->format("d/m/Y"),
       'basiclist' => json_decode ($invoices, true),
       'token' => uniqid('sign_').time(),
       'documentType' => 'sales_invoice',
       'documentPrefix' => '',
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
      $url=$this->url.'navisionExport/axiom/do-NAVISION-invoice-list.php?numfact='.$request->request->get("numfact").'&cif='.$request->request->get("cif").'&customer='.$request->request->get("customer").'&start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d");
      $invoices=file_get_contents($url);
      return new Response($invoices);
    }

  /**
   * @Route("/api/navision/invoice/print/{mode}", name="navisionPrintInvoice", defaults={"id"=0, "mode"="print"})
   */
   public function navisionPrintInvoice($mode, Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $ids=$request->query->get('ids');
     $ids=explode(",",$ids);

     $invoice=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice.php?invoices='.json_encode($ids));
     $invoices=json_decode($invoice, true);
     //dump($this->url.'navisionExport/do-NAVISION-invoice.php?invoices=['.$id.']');
     //$ids=$id;
     $params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "ids"=>$ids, "user"=>$this->getUser(), "invoices"=>$invoices];
     $reportsUtils = new ERPInvoiceReports();
     //dump($invoices);

     switch($mode){
       case "email":
         $tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Email'.DIRECTORY_SEPARATOR;
         if (!file_exists($tempPath) && !is_dir($tempPath)) {
             mkdir($tempPath, 0775, true);
         }
        foreach($ids as $key=>$id){
           $params["ids"]=$id;
           $params["invoices"]=[$invoices[$id]];
           $pdf=$reportsUtils->create($params,'F',$tempPath.$id.'.pdf');
         }
         return new JsonResponse(["result"=>1]);
       break;
       case "temp":
         $tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Others'.DIRECTORY_SEPARATOR;
         if (!file_exists($tempPath) && !is_dir($tempPath)) {
             mkdir($tempPath, 0775, true);
         }
         $pdf=$reportsUtils->create($params,'F',$tempPath.$id.'.pdf');
         return new JsonResponse(["result"=>1]);
       break;
       case "download":
         $pdf=$reportsUtils->create($params,'D','factura.pdf');
         return new JsonResponse(["result"=>1]);
       break;
       case "print":
       case "default":
         $pdf=$reportsUtils->create($params,'I','factura.pdf');
         return new JsonResponse(["result"=>1]);
       break;
     }
     return new JsonResponse(["result"=>0]);




     //$pdf=$reportsUtils->create($params);
     //return new Response("", 200, array('Content-Type' => 'application/pdf'));
   }


   /**
    * @Route("/api/navision/insuredcustomerinvoices", name="navisionInsuredCustomerInvoices")
    */
    public function navisionInsuredCustomerInvoices(Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
      $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
      $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
      $start=new DateTime('first day of this month');
      $end=new DateTime('last day of this month');
    //  $start=new DateTime('first day of january this year');
  //    $end=new DateTime('first day of march this year');

      $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
      $customers=$customersRepository->findInsuredCustomers($this->getUser()->getCompany());

      $array_customers=[];
      foreach($customers as $customer){
         $array_customers[]=$customer["code"];
      }

        $invoices=Array();

        $invoices_customer=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice-list-by-customer.php?customers='.json_encode($array_customers).'&start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
        $invoices_list=json_decode($invoices_customer,true);

        foreach($customers as $customer)
        {
          $code=$customer["code"];
          if($invoices_list[$code]["invoices"]!=NULL){
            foreach($invoices_list[$code]["invoices"] as $invoice){
              $item['Suplemento']=$customer["supplement"];
              $item['Nif']=$customer["vat"];
              $item['Código Cesce']=$customer["cescecode"];
              $item['Fecha Factura']=$invoice['date'];
              $item['Importe']=str_replace(".",",",$invoice['total']);
              $item['Forma de Pago']=$customer["paymentmethod"];
              $item['Vencimiento']=$invoice['due_date'];
              $item['id']=$invoice['id'];
              $invoices[]=$item;
            }
          }
        }


      return $this->render('@Navision/insuredcustomerinvoiceslist.html.twig', [
        "interfaceName" => "Facturas Asegurados",
        'optionSelected' => "navisionInsuredCustomerInvoices",
        'menuOptions' =>  $menurepository->formatOptions($userdata),
        'breadcrumb' =>  "navisionInsuredCustomerInvoices",
        'userData' => $userdata,
        'start' => $start->format("d/m/Y"),
        'end' => $end->format("d/m/Y"),
        'basiclist' => $invoices
      ]);
    }


    /**
     * @Route("/api/navision/get/insuredinvoices", name="navisionGetInsuredInvoices")
     */
     public function navisionGetInsuredInvoices(Request $request){
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       $start=$request->request->get("start");
       $end=$request->request->get("end");
       $start=date_create_from_format('d/m/Y',$start);
       $end=date_create_from_format('d/m/Y',$end);

       $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
       $customers=$customersRepository->findInsuredCustomers($this->getUser()->getCompany());

/*
       $invoices=Array();
       foreach($customers as $customer)
       {

           $invoices_customer=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice-list-by-customer.php?code='.$customer["code"].'&start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
           $invoices_list=json_decode($invoices_customer,true);
           foreach($invoices_list as $invoice){
             $item['supplement']=$customer["supplement"];
             $item['vat']=$customer["vat"];
             $item['cescecode']=$customer["cescecode"];
             $item['date']=$invoice['date'];
             $item['total']=str_replace(".",",",$invoice['total'])."€";
             $item['paymentmethod']=$customer["paymentmethod"];
             $item['id']=$invoice['id'];
             $item['vencimiento']=$invoice['due_date'];
             $invoices[]=$item;
         }
       }


  */
  $array_customers=[];
  foreach($customers as $customer){
     $array_customers[]=$customer["code"];
  }

    $invoices=Array();

    $invoices_customer=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice-list-by-customer.php?customers='.json_encode($array_customers).'&start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
    $invoices_list=json_decode($invoices_customer,true);

    foreach($customers as $customer)
    {
      $code=$customer["code"];
      if($invoices_list[$code]["invoices"]!=NULL){
        foreach($invoices_list[$code]["invoices"] as $invoice){
          $item['supplement']=$customer["supplement"];
          $item['vat']=$customer["vat"];
          $item['cescecode']=$customer["cescecode"];
          $item['date']=$invoice['date'];
          $item['total']=str_replace(".",",",$invoice['total']);
          $item['paymentmethod']=$customer["paymentmethod"];
          $item['vencimiento']=$invoice['due_date'];
          $item['id']=$invoice['id'];
          $invoices[]=$item;
        }
      }
    }

       return new Response(json_encode($invoices,true));

     }


     /**
 		 * @Route("/api/navision/exportinsuredinvoiceslist", name="exportinsuredinvoiceslist")
 		 */
 		 public function exportInsuredInvoicesList(RouterInterface $router,Request $request)
 		 {

       $start=$request->query->get("start");
       $end=$request->query->get("end");
 			 $template=dirname(__FILE__)."/../Forms/InsuredCustomerInvoices.json";
 			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       $start=date_create_from_format('d/m/Y',$start);
       $end=date_create_from_format('d/m/Y',$end);

       $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
       $customers=$customersRepository->findInsuredCustomers($this->getUser()->getCompany());
/*
       $invoices=Array();
       foreach($customers as $customer)
       {

           $invoices_customer=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice-list-by-customer.php?code='.$customer["code"].'&start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
           $invoices_list=json_decode($invoices_customer,true);
           foreach($invoices_list as $invoice){
             $item['Suplemento']=$customer["supplement"];
             $item['Nif']=$customer["vat"];
             $item['Código Cesce']=$customer["cescecode"];
             $item['Fecha Factura']=$invoice['date'];
             $item['Importe']=$invoice['total'];
             $item['Forma de Pago']=$customer["paymentmethod"];
             $item['Vencimiento']=$invoice['due_date'];
             $item['Numero Factura']=$invoice['id'];
             $invoices[]=$item;
         }
       }

       */

       $array_customers=[];
       foreach($customers as $customer){
          $array_customers[]=$customer["code"];
       }

       $invoices=Array();

       $invoices_customer=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-invoice-list-by-customer.php?customers='.json_encode($array_customers).'&start='.$start->format("Y-m-d").'&end='.$end->format("Y-m-d"));
       $invoices_list=json_decode($invoices_customer,true);

        foreach($customers as $customer)
        {
           $code=$customer["code"];
           if($invoices_list[$code]["invoices"]!=NULL){
             foreach($invoices_list[$code]["invoices"] as $invoice){
               $item['supplement']=$customer["supplement"];
               $item['vat']=$customer["vat"];
               $item['cescecode']=$customer["cescecode"];
               $item['date']=$invoice['date'];
               $item['total']=str_replace(".",",",$invoice['total'])."€";
               $item['paymentmethod']=$customer["paymentmethod"];
               $item['vencimiento']=$invoice['due_date'];
               $item['id']=$invoice['id'];
               $invoices[]=$item;
             }
           }
       }
 			$result=$this->exportinvoices($invoices,$template);
 			return $result;

 		 }


     public function exportinvoices($list, $template){
       $this->template=$template;
       $filename='ListadoFacturasAsegurados.csv';
       $array=$list;
       //exclude tags column, last
       $key='_tags';
       array_walk($array, function (&$v) use ($key) {
        unset($v[$key]);
       });
    //	 $array=$this->applyFormats($array);

       $fileContent=$this->createCSV($array);
       $response = new Response($fileContent);
       // Create the disposition of the file
          $disposition = $response->headers->makeDisposition(
              ResponseHeaderBag::DISPOSITION_ATTACHMENT,
              $filename
        );
       // Set the content disposition
       $seconds_to_cache = 0;
       $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
       $response->headers->set("Expires", $ts);
       $response->headers->set("Pragma", "cache");
       $response->headers->set("Cache-Control", "max-age=0, no-cache, must-revalidate, proxy-revalidate");
       $response->headers->set('Content-Type', 'application/force-download');
       $response->headers->set('Content-Type', 'application/octet-stream');
       $response->headers->set('Content-Type', 'application/download');
       $response->headers->set('Content-Disposition', $disposition);
       // Dispatch request
       return $response;

     }

     private function createCSV(array &$array){
        if (count($array) == 0) {
          return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_map("utf8_decode",array_keys(reset($array))));
        foreach ($array as $row) {
           fputcsv($df, array_values (array_map("utf8_decode", $row )));
        }
        fclose($df);
        return ob_get_clean();
    }


    /**
     * @Route("/api/navision/insuredcustomers", name="navisionInsuredCustomers")
     */
     public function navisionInsuredCustomers(Request $request){
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
       $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
       $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
       $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
       $customers=$customersRepository->findInsuredCustomers($this->getUser()->getCompany());


       $insuredcustomers=Array();
       foreach($customers as $customer)
       {
             $item['id']=$customer["code"];
             $item['Razón Social']=$customer["socialname"];
             $item['CIF']=$customer["vat"];
             $insuredcustomers[]=$item;
       }

       return $this->render('@Navision/insuredcustomerlist.html.twig', [
         "interfaceName" => "Facturas Asegurados",
         'optionSelected' => "navisionInsuredCustomers",
         'menuOptions' =>  $menurepository->formatOptions($userdata),
         'breadcrumb' =>  "navisionInsuredCustomers",
         'userData' => $userdata,
         'basiclist' => $insuredcustomers
       ]);
     }


     /**
 		 * @Route("/api/navision/exportinsuredcustomerslist", name="exportinsuredcustomerslist")
 		 */
 		 public function exportInsuredCustomersList(RouterInterface $router,Request $request)
 		 {

       $start=$request->query->get("start");
       $end=$request->query->get("end");
 			 $template=dirname(__FILE__)."/../Forms/InsuredCustomers.json";
 			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

       $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
       $customers=$customersRepository->findInsuredCustomers($this->getUser()->getCompany());

       $insuredcustomers=Array();
       foreach($customers as $customer)
       {
             $item['id']=$customer["code"];
             $item['Razón Social']=$customer["socialname"];
             $item['CIF']=$customer["vat"];
             $insuredcustomers[]=$item;
       }

 			$result=$this->exportcustomers($insuredcustomers,$template);
 			return $result;

 		 }



     public function exportcustomers($list, $template){
       $this->template=$template;
       $filename='ListadoClientesAsegurados.csv';
       $array=$list;
       //exclude tags column, last
       $key='_tags';
       array_walk($array, function (&$v) use ($key) {
        unset($v[$key]);
       });
     //	 $array=$this->applyFormats($array);

       $fileContent=$this->createCSV($array);
       $response = new Response($fileContent);
       // Create the disposition of the file
          $disposition = $response->headers->makeDisposition(
              ResponseHeaderBag::DISPOSITION_ATTACHMENT,
              $filename
        );
       // Set the content disposition
       $seconds_to_cache = 0;
       $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
       $response->headers->set("Expires", $ts);
       $response->headers->set("Pragma", "cache");
       $response->headers->set("Cache-Control", "max-age=0, no-cache, must-revalidate, proxy-revalidate");
       $response->headers->set('Content-Type', 'application/force-download');
       $response->headers->set('Content-Type', 'application/octet-stream');
       $response->headers->set('Content-Type', 'application/download');
       $response->headers->set('Content-Disposition', $disposition);
       // Dispatch request
       return $response;

     }



}
