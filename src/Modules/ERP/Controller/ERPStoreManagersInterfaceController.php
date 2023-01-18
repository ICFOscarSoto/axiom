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
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProductsAttributes;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStoresManagersConsumers;
use App\Modules\ERP\Entity\ERPStoresManagersProducts;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesLogs;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannelsReplenishment;
use App\Modules\ERP\Entity\ERPStoresManagersUsers;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPStoresManagersUsersStores;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPTypesMovements;
use App\Modules\ERP\Controller\ERPStocksController;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersConsumersUtils;
use App\Modules\ERP\Utils\ERPStoresManagersProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersUsersUtils;
use App\Modules\ERP\Utils\ERPStoresManagersVendingMachinesUtils;
use App\Modules\ERP\Utils\ERPStoresManagersVendingMachinesChannelsUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\ERP\Utils\ERPStoresManagersVendingMachinesLogsUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Reports\ERPPrintQR;
use App\Modules\ERP\Utils\ERPStoresManagersUtils;
use App\Modules\IoT\Entity\IoTSensors;
use App\Modules\IoT\Entity\IoTData;
use \DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Helpers\XLSXWriter\XLSXWriter;
use App\Modules\Navision\Entity\NavisionTransfers;



class ERPStoreManagersInterfaceController extends Controller
{
	private $class=ERPStoresManagers::class;
	private $utilsClass=ERPStoresManagersUtils::class;
	private $module='ERP';

  /**
   * @Route("/{_locale}/erp/storesmanagersinterface", name="formStoresManagersInterface")
   */
   public function formStoresManagersInterface(Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
    $new_breadcrumb=["rute"=>null, "name"=>"StoresManagersInterface", "icon"=>"fa fa-new"];
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','StoresManagers');
    array_push($breadcrumb, $new_breadcrumb);
    $repository=$this->getDoctrine()->getRepository($this->class);

    $tabs=[
      ["name" => "history", "caption"=>"historyManager", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("listStocksHistoryManagerByUser",["idUser"=>$this->getUser()->getId()])],
      ["name" => "storesmanagersvendingmachines", "caption"=>"Expendedoras", "icon"=>"fa-th","route"=>$this->generateUrl("listStoresManagersVendingMachinesByUser",["idUser"=>$this->getUser()->getId()])],
      //["name" => "storesmanagersconsumers", "caption"=>"Consumidores", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("listStoresManagersConsumers",["user"=>$this->getUser()->getId()])],
      //["name" => "transfers", "caption"=>"Transfers", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("generictablist",["function"=>"formatList","module"=>"Navision","name"=>"Transfers"])],
      //["name" => "loads", "caption"=>"Loads List", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("listStoresManagersReplenishment",["user"=>$this->getUser()->getId()])],
      //["name" => "historyVM", "caption"=>"historyVendingMachines", "icon"=>"fa-address-card-o", "route"=>$this->generateUrl("listStocksHistoryVM",["user"=>$this->getUser()->getId()])],
    ];
    //$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
    //$obj_name=$obj?$obj->getName():'';

      return $this->render('@Globale/generictabform.html.twig', array(
                'entity_name' => $this->getUser()->getName(),
                'controllerName' => 'StoresManagersController',
                'interfaceName' => 'Gestores',
                'optionSelected' => 'genericindex',
                'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoresManagers"],
                'menuOptions' =>  $menurepository->formatOptions($userdata),
                'breadcrumb' => $breadcrumb,
                'userData' => $userdata,
                'id' => $this->getUser()->getId(),
                'tab' => $request->query->get('tab','history'), //Show initial tab, by default data tab
                'tabs' => $tabs,
                'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
                                    ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
                'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                                     ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
                                     ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
        ));

  }

}
