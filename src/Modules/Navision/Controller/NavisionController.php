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
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\AERP\Utils\AERPCustomersUtils;
use App\Modules\ERP\Reports\ERPInvoiceReports;

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

}
