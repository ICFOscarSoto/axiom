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
    $productPricesRepository=$this->getDoctrine()->getRepository($this->class);
		//dump($product);
    $productPrices=$productPricesRepository->pricesByProduct($product);
		$listOfferPrices = new ERPOfferPricesUtils();
		$formUtilsOfferPrices = new GlobaleFormUtils();
		$formUtilsOfferPrices->initialize($this->getUser(), new ERPOfferPricesUtils(), dirname(__FILE__)."/../Forms/OfferPrices.json", $request, $this, $this->getDoctrine());
		$forms[]=$formUtilsOfferPrices->formatForm('OfferPrices', true, null, ERPOfferPrices::class);
    /*
    foreach($productPrices as $key=>$item){
      $stocks[$key]["Acciones"]="<button>Ir</button>";
    }
    */
    return $this->render('@ERP/productprices.html.twig', array(
      'productpriceslist'=>$productPrices,
			'offerpriceslist' => $listOfferPrices->formatListByProduct($id),
			'forms' => $forms
    ));
  }
  
}