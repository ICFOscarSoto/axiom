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
use \DateTime;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Utils\ERPStocksHistoryUtils;
use App\Modules\Security\Utils\SecurityUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class ERPStocksHistoryController extends Controller
{
  private $class=ERPStocksHistory::class;
  private $utilsClass=ERPStocksHistoryUtils::class;
  private $module='ERP';
  /**
   * @Route("/{_locale}/erp/stockshistoryVM/{storemanager}/list", name="StocksHistoryVMlist")
   *
   */
  public function StocksHistoryVMlist($storemanager, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStocksHistory::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksHistoryVM.json"),true);
    //$user,$repository,$request,$manager,$listFields,$classname,
    //$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null)
    $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields, ERPStocksHistory::class,
                                    ['tm.name'=>'type','tm.departure'=>'departure', 'sh.dateadd'=>'dateoperation', 'vm.name'=>'vendingmachine', 'c.name'=>'channel',
                                      'sh.productcode'=>'productcode', 'sh.productname'=>'productname', 'sh.quantity'=>'quantity', 'sh.previousqty'=>'previousqty', 'sh.newqty'=>'newqty', 'sh.id'=>'id'],
                                    'erpstocks_history sh
                                    LEFT JOIN erptypes_movements tm ON tm.id=sh.type_id
                                    LEFT JOIN erpstores_managers_vending_machines_channels c ON c.id=sh.vendingmachinechannel_id
                                    LEFT JOIN erpstores_managers_vending_machines vm ON vm.id=c.vendingmachine_id',
                                    'sh.active=1 and sh.deleted=0 and vm.manager_id='.$storemanager,
                                    50,
                                    'sh.dateadd',
                                  );
    return new JsonResponse($return);
  }

  /**
   * @Route("/{_locale}/erp/stockshistoryVM/{manager}/vm", name="listStocksHistoryVM")
   */
  public function listStocksHistoryVM($manager,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;
    $repository=$this->getDoctrine()->getRepository($this->class);
    $obj=$repository->findOneBy(["id"=>$manager, "deleted"=>0]);
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $utils=new ERPStocksHistoryUtils();

    $templateLists=$utils->formatListVMbyManager($manager);
  /*  $formUtils=new GlobaleFormUtils();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
    $formUtils->initialize($this->getUser(), new ERPStoresManagersConsumers(), dirname(__FILE__)."/../Forms/StoresManagersConsumers.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
  */
    $templateForms=[];
    return $this->render('@Globale/list.html.twig', [
      'id' => $manager,
      'listConstructor' => $templateLists,
      'forms' => $templateForms,
      'userData' => $userdata,
      ]);
    return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
   * @Route("/{_locale}/erp/stockshistorymanager/{storemanager}/list", name="StocksHistoryManagerlist")
   *
   */
  public function StocksHistoryManagerlist($storemanager, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository(ERPStocksHistory::class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksHistoryManager.json"),true);
    //$user,$repository,$request,$manager,$listFields,$classname,
    //$select_fields,$from,$where,$maxResults=null,$orderBy="id",$groupBy=null)
    $return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields, ERPStocksHistory::class,
                                    ['tm.name'=>'type', 'sh.dateadd'=>'dateoperation', 'sh.num_operation'=>'transfer', 'st.name'=>'store', 'vm.name'=>'vendingmachine', 'c.name'=>'channel',
                                      'sh.productcode'=>'productcode', 'sh.productname'=>'productname', 'sh.quantity'=>'quantity', 'sh.previousqty'=>'previousqty', 'sh.newqty'=>'newqty', 'sh.id'=>'id'],
                                    'erpstocks_history sh
                                    LEFT JOIN erptypes_movements tm ON tm.id=sh.type_id
                                    LEFT JOIN erpstores_managers_vending_machines_channels c ON c.id=sh.vendingmachinechannel_id
                                    LEFT JOIN erpstores_managers_vending_machines vm ON vm.id=c.vendingmachine_id
                                    LEFT JOIN erpstore_locations sl ON sl.id=sh.location_id
                                    LEFT JOIN erpstores st ON st.id=sl.store_id',
                                    'sh.active=1 and sh.deleted=0 and (vm.manager_id='.$storemanager.' or st.managed_by_id='.$storemanager.')',
                                    50,
                                    'sh.dateadd',
                                  );
    return new JsonResponse($return);
  }

  /**
   * @Route("/{_locale}/erp/stockshistorymanager/{manager}", name="listStocksHistoryManager")
   */
  public function listStocksHistoryManager($manager,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;
    $repository=$this->getDoctrine()->getRepository($this->class);
    $obj=$repository->findOneBy(["id"=>$manager, "deleted"=>0]);
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $utils=new ERPStocksHistoryUtils();

    $templateLists=$utils->formatListbyManager($manager);
  /*  $formUtils=new GlobaleFormUtils();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
    $formUtils->initialize($this->getUser(), new ERPStoresManagersConsumers(), dirname(__FILE__)."/../Forms/StoresManagersConsumers.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
  */
    $templateForms=[];
    return $this->render('@Globale/list.html.twig', [
      'id' => $manager,
      'listConstructor' => $templateLists,
      'forms' => $templateForms,
      'userData' => $userdata,
      ]);
    return new RedirectResponse($this->router->generate('app_login'));
  }

}
