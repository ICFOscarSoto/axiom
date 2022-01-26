<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Modules\ERP\Entity\ERPShoppingDiscounts;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsSuppliers;
use App\Modules\ERP\Entity\ERPShoppingPrices;

use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPTransfersUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPShoppingDiscountsController extends Controller
{

  private $module='ERP';
  /**
   * @Route("/{_locale}/admin/global/updaterate", name="updaterate")
   */
  public function index(RouterInterface $router,Request $request)
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    //if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
    $categoryRepository=$this->getDoctrine()->getRepository(ERPCategories::class);
    $info[]="Elige el proveedor";
    return $this->render('@ERP/shoppingDiscounts.html.twig',[
      'optionSelected' => 'genericindex',
      'optionSelectedParams' => ["module"=>"ERP", "name"=>"Suppliers"],
      'menuOptions' =>  $menurepository->formatOptions($userdata),
      'userData' => $userdata,
      'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
      'interfaceName' => 'UpdateRate',
      'suppliers' => $suppliersRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]),
      'category' => $categoryRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]),
      'info' => $info,
      'tempPath' => str_replace("\\","\\\\", $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'shoppingDiscount'.DIRECTORY_SEPARATOR)
    ]);
  }



  /**
   * @Route("/{_locale}/productprices/infoProductRates/{id}", name="infoProductRates", defaults={"id"=0})
   */
  public function infoProductRates($id, Request $request){
		$productsRepository         = $this->getDoctrine()->getRepository(ERPProducts::class);
    $productsSuppliersRepository= $this->getDoctrine()->getRepository(ERPProductsSuppliers::class);
    // Producto
		$product=$productsRepository->find($id);
    if ($product!=null){
      // Todos los proveedores del producto
      $suppliers=$productsSuppliersRepository->getDiscountSuppliersByProduct($this->getUser(), $this->getDoctrine(), $product);
      if ($suppliers!=null && count($suppliers)>0){
        // Quitar columnas que no se muestran y formatear fechas
        foreach ($suppliers as $key=>$value){
          $value["supplier"]='<a href="'.$this->generateUrl('formSupplier',["id"=>$value['supplier_id']]).'" class="external">'.$value["supplier"].'</a>';
          if (isset($value['start']) && $value['start']!=null){
            $start=date_create($value['start']);
            $value['start'] = date_format($start,'d/m/Y');
          }
          if (isset($value['end']) && $value['end']!=null){
            $end=date_create($value['end']);
            $value['end'] = date_format($end,'d/m/Y');
          }
          unset($value['supplier_id']);
          unset($value['preference']);
          $suppliers[$key] = $value;
        }
        return $this->render('@ERP/productrates.html.twig', [
          'productrateslist'=>$suppliers,
    			'id' => $id,
    			'forms' => null
        ]);
      }else{
        return $this->render('@Globale/error.html.twig', ["error_result"=>-2, "error_text"=>"No existen proveedores para este producto"]);
      }
    }else{
      return $this->render('@Globale/error.html.twig', ["error_result"=>-1, "error_text"=>"No existe el producto"]);
    }
  }

  /**
  * @Route("/api/ERP/shoppingDiscounts/save", name="saveDiscounts")
  */
  public function saveDiscounts(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $new_item=json_decode($request->getContent());
    $shoppingDiscountsRepository=$this->getDoctrine()->getRepository(ERPShoppingDiscounts::class);
    $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
    $product=$productsRepository->findOneById($new_item->id);
    if ($new_item->netprice) {
      if ($new_item->shoppingPrice!=$product->getShoppingPrice() and $new_item->shoppingPrice!=0){
        $product->setShoppingPrice($new_item->shoppingPrice);
      }
      else {
        return new JsonResponse(["result"=>-1]);
      }
    }
    else {
      if ($new_item->PVP!=$product->getPVP() and $new_item->PVP!=0)
      $product->setPVP($new_item->PVP);
      else return new JsonResponse(["result"=>-1]);
    }
    $manager=$this->getDoctrine()->getManager();
    $manager->persist($product);
    $manager->flush();
    $product->priceCalculated($this->getDoctrine());
    return new JsonResponse(["result"=>1]);

  }

    /**
    * @Route("/api/ERP/shoppingDiscounts/readcsv", name="readcsv")
    */
    public function readCSV(RouterInterface $router,Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
      $referencesRepository=$this->getDoctrine()->getRepository(ERPReferences::class);
      $eanRepository=$this->getDoctrine()->getRepository(ERPEAN13::class);
      $item=json_decode($request->getContent());
      $supplier=$suppliersRepository->findOneById($item->supplier);
      $csv=fopen($item->fileSelect,"r");
      $data=fgetcsv($csv,0,';');
      $updates=[];
      while (($data=fgetcsv($csv,0,';')) != false) {
        if ($data[1]!=null) {
          $reference=$referencesRepository->findProduct($supplier->getId(), '%'.$data[1]);
          if ($reference!=null) $product=$reference[0]["product_id"];
          else continue;
        } else if ($data[2]!=null) {
          $ean=$eanRepository->findOneBy(["supplier"=>$supplier, "name"=>$data[2]]);
          if ($ean!=null) $product=$ean->getProduct()->getId();
          else continue;
        } else continue;
        $update["id"]=$product;
        //$update["product_reference"]=$data[1];
        //$update["product_ean"]=$data[2];
        //$update["qty"]=$data[3];
        $update["shopping_price"]=str_replace(',','.',$data[3]);
        //$update["packing"]=$data[5];
        //$update["weight"]=$data[6];
        //$update["manufacturer"]=$data[7];
        //$update["min_qty"]=$data[8];
        $update["PVP"]=str_replace(',','.',$data[4]);
        $updates[]=$update;
      };
      unlink($item->fileSelect);
      return new JsonResponse(["result"=>1, "data"=>$updates]);
    }




}
