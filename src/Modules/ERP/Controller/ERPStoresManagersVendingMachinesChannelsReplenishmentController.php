<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannelsReplenishment;
use App\Modules\ERP\Reports\ERPPrintQR;
use \DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Helpers\XLSXWriter\XLSXWriter;
use App\Modules\Navision\Entity\NavisionTransfers;

class ERPStoresManagersVendingMachinesChannelsReplenishmentController extends Controller
{
  private $class=ERPStoresManagersVendingMachinesChannelsReplenishment::class;
  private $utilsClass=ERPStoresManagersVendingMachinesChannelsReplenishmentUtils::class;
  private $module='ERP';

  /**
    * @Route("/api/ERP/downloadReplenishments", name="downloadReplenishments")
    */
  public function downloadReplenishments(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $channelsRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
    $machineRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
    $replenishmentRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannelsReplenishment::class);
    $locationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
    $storeRepository=$this->getDoctrine()->getRepository(ERPStores::class);
    $ids=$request->query->get('ids');
    if ($ids==null) return new Response();
    $ids=explode(",",$ids);
    $names=[];
    $params=[];
    $params["rootdir"]= $this->get('kernel')->getRootDir();
    $params["user"]=$this->getUser();
    $loads=[];
    foreach($ids as $id){
      $replenishmentLine=$replenishmentRepository->findOneBy(["id"=>$id]);
      $machineReplenishment=$replenishmentLine->getChannel()->getVendingmachine();
      $load=["Maquina ".$machineReplenishment->getId()." fecha ".$replenishmentLine->getDateadd()->format('y-m-d')];
      if (array_search($load,$loads)) continue;
      $loads=$load;
      $replenishmentInfo["datesend"]=$replenishmentLine->getDateadd()->format('d-m-Y');
      $replenishmentInfo["origin"]=$machineReplenishment->getStorelocation()->getStore()->getName();
      $replenishmentInfo["destination"]=$machineReplenishment->getName();
      $replenishments=$channelsRepository->getLoadsMachineDate($machineReplenishment->getId(), $replenishmentLine->getDateadd()->format('y-m-d'));
      $lines=[];
      foreach ($replenishments as $replenishment){
        $line['productcode']=$replenishment["productcode"];
        $line['productname']=$replenishment["productname"];
        $line['quantity']=$replenishment["quantity"];
        $line["upload"]=intval($replenishment["upload"]);
        $line["multiplier"]=$replenishment["multiplier"];
        $lines[]=$line;
      }
      $replenishmentInfo["lines"]=$lines;
      $params["transfers"][]=$replenishmentInfo;
    }
    $printQRUtils = new ERPPrintQR();
    $pdf=$printQRUtils->downloadTransfers($params);
    return new Response("", 200, array('Content-Type' => 'application/pdf'));
  }

  /**
   * @Route("/{_locale}/ERP/storesmanagersreplenishment/{id}/list", name="StoresManagersReplenishment")
*
*  public function StoresManagersReplenishment($id, RouterInterface $router,Request $request)
*  {
*    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
*    $user = $this->getUser();
*    $locale = $request->getLocale();
*    $this->router = $router;
*    $manager = $this->getDoctrine()->getManager();
*    $repositoryReplenishment = $manager->getRepository(ERPStoresManagersVendingMachinesChannelsReplenishment::class);
*    $listUtils=new GlobaleListUtils();
*    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoresManagersVendingMachinesChannelsReplenishment.json"),true);
*    //$user,$repository,$request,$manager,$listFields,$classname,$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null)
*    $return=$listUtils->getRecordsSQL($user,$repositoryReplenishment,$request,$manager,$listFields, ERPStoresManagersProducts::class,
*                                    ['r.date'=>'date', 'vm.name'=>'vendingmachine', 'c.name'=>'channel', 'r.productcode'=>'productcode', 'r.productname'=>'productname', 'r.quantity'=>'quantity'],
*                                    'erpstores_managers_vending_machines_channels_replenishment r
*                                    LEFT JOIN erpstores_managers_vending_machines_channels c ON c.id=r.channel_id
*                                    LEFT JOINerpstores_managers_vending_machines vm ON vm.',
*                                    ,
*                                    20,
*                                    'r.id',
*                                    ,
*                              );
*    return new JsonResponse($return);
*  }
 */
}
