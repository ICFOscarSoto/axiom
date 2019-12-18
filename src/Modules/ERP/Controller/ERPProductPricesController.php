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
use App\Modules\ERP\Entity\ERPProductPrices;
use App\Modules\ERP\Entity\ERPOfferPrices;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Utils\ERPOfferPricesUtils;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;



class ERPProductPricesController extends Controller
{
	private $class=ERPProductPrices::class;
	private $utilsClass=ERPProductPricesUtils::class;

  /**
   * @Route("/{_locale}/productprices/infoProductPrices/{id}", name="infoProductPrices", defaults={"id"=0})
   */
  public function infoProductPrices($id, Request $request){
		$productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$product=$productsRepository->findOneBy(["id"=>$id]);
		$product_id=$product->getId();
		$suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$default_supplier=$suppliersRepository->findOneBy(["id"=>$product->getSupplier()]);
    $productPricesRepository=$this->getDoctrine()->getRepository($this->class);
		$productPrices=$productPricesRepository->pricesByProductSupplier($product,$default_supplier);
		$listOfferPrices = new ERPOfferPricesUtils();
		$formUtilsOfferPrices = new GlobaleFormUtils();
		$formUtilsOfferPrices->initialize($this->getUser(), new ERPOfferPricesUtils(), dirname(__FILE__)."/../Forms/OfferPrices.json", $request, $this, $this->getDoctrine(),$listOfferPrices->getExcludedForm([]),$listOfferPrices->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$id, "parent"=>$product]));
		$forms[]=$formUtilsOfferPrices->formatForm('OfferPrices', true, null, ERPOfferPrices::class);

    foreach($productPrices as $key=>$item){
      //$productPrices[$key]["Visualizar"]="<a href='/{_locale}/productprices/infoProductPrices/".$id."'>Ir</a>";
    		$productPrices[$key]["Visualizar"]="<a href='/{_locale}/es/generic/ERP/Increments/index'>Ir</a>";
		}

    return $this->render('@ERP/productprices.html.twig', array(
      'productpriceslist'=>$productPrices,
			'id' => $id,
			'offerpriceslist' => $listOfferPrices->formatListByProduct($id),
			'forms' => $forms
    ));
  }

}
