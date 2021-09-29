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
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPInfoStocks;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPInfoStocksUtils;


class ERPInfoStocksController extends Controller
{
  private $url="http://192.168.1.250:9000/";

  /**
   * @Route("/es/infoStocks/{id}/list", name="infoStockslist")
   */
  public function indexlist($id,RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product = $productRepository->find($id);
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $class=ERPInfoStocks::class;
    $repository = $manager->getRepository($class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/InfoStocks.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"product", "value"=>$product]]);
    return new JsonResponse($return);
  }


    /**
     * @Route("/{_locale}/listInfoStocks/{id}", name="listInfoStocks", defaults={"id"=0})
     */
    public function listInfoStocks($id, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $listInfoStocks = new ERPInfoStocksUtils();
      $formUtils=new GlobaleFormUtils();
      $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "product"=>null];
      $formUtils->initialize($this->getUser(), ERPInfoStocks::class,
      dirname(__FILE__)."/../Forms/InfoStocks.json", $request, $this, $this->getDoctrine(),
      method_exists($listInfoStocks,'getExcludedForm')?$listInfoStocks->getExcludedForm($params):[],
      method_exists($listInfoStocks,'getIncludedForm')?$listInfoStocks->getIncludedForm($params):[]);
      $templateForms[]=$formUtils->formatForm('infoStocks', true, null, ERPInfoStocks::class);
      return $this->render('@Globale/list.html.twig', array(
        'listConstructor' => $listInfoStocks->formatListByProduct($id),
        'id_object'=>$id,
        'forms' => $templateForms
      ));
    }


    /**
     * @Route("/{_locale}/infoStocks/data/{id}/{action}/{idproduct}", name="dataInfoStocks", defaults={"id"=0, "action"="read", "idproduct"=0})
     */
     public function data($id, $action, $idproduct, Request $request)
     {
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $template=dirname(__FILE__)."/../Forms/InfoStocks.json";
     $utils = new GlobaleFormUtils();
     $utilsObj=new ERPInfoStocksUtils();
     $defaultProduct=$this->getDoctrine()->getRepository(ERPProducts::class);
     $EAN13Repository=$this->getDoctrine()->getRepository(ERPInfoStocks::class);
     $obj=new ERPInfoStocks();
     if($id==0){
      if($idproduct==0 ) $idproduct=$request->query->get('idproduct');
      if($idproduct==0 || $idproduct==null) $idproduct=$request->request->get('id-parent',0);

      $product = $defaultProduct->find($idproduct);
     }else $obj = $EAN13Repository->find($id);


     $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(),"product"=>$id==0?$product:$obj->getProduct(),
     "productvariant"=>$id==0?null:$obj->getProductVariant()];

     $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),
                            method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
                            method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
     if($id==0) $utils->values(["product"=>$product]);
     $make=$utils->make($id, ERPInfoStocks::class, $action, "infoStocks", "modal");

     return $make;
    }


  /**
   * @Route("/{_locale}/updateStocksManageds", name="updateStocksManageds")
   * store => codigo del almacén en AXIOM
   * date => fecha de inicio de los movimientos en formato dd/mm/aaaa
   * date2 => fecha de inicio de los movimientos en formato aaaa/mm/dd
   */
    public function updateStocksManageds(RouterInterface $router,Request $request){
    $storeName=$request->query->get('store',null);
    $date=$request->query->get('date',null);
    $date2=$request->query->get('date2',null);
    $usersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
    $user=$usersRepository->findOneBy(["email"=>"oscar.soto@ferreteriacampollano.com", "deleted"=>0]);
    $infoRepository=$this->getDoctrine()->getRepository(ERPInfoStocks::class);
    $productRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
    $storeRepository=$this->getDoctrine()->getRepository(ERPStores::class);
    $stockRepository=$this->getDoctrine()->getRepository(ERPStocks::class);
    if ($storeName=='ALI') {
      $storeLocation=$storeLocationsRepository->findOneBy(["name"=>"GESTOR ALI"]);
      $store=$storeRepository->findOneBy(["code"=>"GESTOR ALI"]);
      $infoStocks=$infoRepository->getOperations("GESTOR ALI",$date2);
    }
    else {
      $storeLocation=$storeLocationsRepository->findOneBy(["name"=>$storeName]);
      $store=$storeRepository->findOneBy(["code"=>$storeName]);
      $infoStocks=$infoRepository->getOperations($storeName,$date2);
    }
    foreach($infoStocks as $infoStock){
      $product=$productRepository->findOneBy(["code"=>$infoStock["code"]]);
      $stock=$stockRepository->findOneBy(["storelocation"=>$storeLocation->getId(), "product"=>$product->getId()]);
      if ($stock==NULL) continue;
      $quantity=$stock->getQuantity()-$infoStock["vendido"];
      $stockHistory=new ERPStockHistory();
      $stockHistory->setProduct($product);
      $stockHistory->setLocation($storeLocation);
      $stockHistory->setStore($store);
      $stockHistory->setUser($user);
      $stockHistory->setPreviousqty($stock->getQuantity());
      $stockHistory->setNewqty($quantity);
      $stockHistory->setDateadd(new \Datetime());
      $stockHistory->setDateupd(new \Datetime());
      $stockHistory->setActive(true);
      $stockHistory->setDeleted(false);
      $this->getDoctrine()->getManager()->persist($stockHistory);
      $stock->setQuantity($quantity);
      $this->getDoctrine()->getManager()->persist($stock);
    }
    $json=file_get_contents($this->url.'navisionExport/axiom/do-NAVISION-getTransfersByStore.php?store='.$storeName.'&date='.$date);
    $objects=json_decode($json, true);
    $objects=$objects[0]["class"];
    foreach ($objects as $object){
      $product=$productRepository->findOneBy(["code"=>$object["code"]]);
      $stock=$stockRepository->findOneBy(["storelocation"=>$storeLocation->getId(), "product"=>$product->getId()]);
      if ($stock!=null){
      $quantity=$stock->getQuantity()+$object["stock"];
      $stockHistory=new ERPStockHistory();
      $stockHistory->setProduct($product);
      $stockHistory->setLocation($storeLocation);
      $stockHistory->setStore($store);
      $stockHistory->setUser($user);
      $stockHistory->setPreviousqty($stock->getQuantity());
      $stockHistory->setNewqty($quantity);
      $stockHistory->setDateadd(new \Datetime());
      $stockHistory->setDateupd(new \Datetime());
      $stockHistory->setActive(true);
      $stockHistory->setDeleted(false);
      $this->getDoctrine()->getManager()->persist($stockHistory);
      $stock->setQuantity($quantity);
      $this->getDoctrine()->getManager()->persist($stock);}
    }

    $this->getDoctrine()->getManager()->flush();
    return new JsonResponse(["result"=>1, "text"=>"Se ha ajustado el stock"]);
    }

}
