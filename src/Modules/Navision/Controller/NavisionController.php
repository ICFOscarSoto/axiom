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

}
