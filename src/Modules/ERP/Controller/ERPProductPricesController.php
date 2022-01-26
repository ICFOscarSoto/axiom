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
use App\Modules\ERP\Entity\ERPCustomerPrices;
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
		$productsRepository         = $this->getDoctrine()->getRepository(ERPProducts::class);
  	$productPricesRepository		= $this->getDoctrine()->getRepository(ERPProductPrices::class);
		$customerPricesRepository		= $this->getDoctrine()->getRepository(ERPCustomerPrices::class);

		// Producto
		$product=$productsRepository->find($id);
		if ($product!=null){
			if ($product->getSupplier()!=null){
					$productPrices	= $productPricesRepository->pricesByProductSupplier($this->getUser(), $this->getDoctrine(), $product);
					$customerPrices	= $customerPricesRepository->pricesByProductSupplier($this->getUser(), $this->getDoctrine(), $product);
					$listOfferPrices = new ERPOfferPricesUtils();
					$formUtilsOfferPrices = new GlobaleFormUtils();
					$formUtilsOfferPrices->initialize($this->getUser(), new ERPOfferPricesUtils(), dirname(__FILE__)."/../Forms/OfferPrices.json", $request, $this, $this->getDoctrine(),$listOfferPrices->getExcludedForm([]),$listOfferPrices->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$id, "parent"=>$product]));
					$forms[]=$formUtilsOfferPrices->formatForm('OfferPrices', true, null, ERPOfferPrices::class);
					$url_customer = "/es/ERP/customer/form/%s";
					if ($productPrices!= null)
				    foreach($productPrices as $key=>$value){
		          $value["supplier"]='<a href="'. $this->generateUrl('formSupplier',["id"=>$value['supplier_id']]).'">'.$value["supplier"].'</a>';
							unset($value['supplier_id']);
							unset($value['preference']);
							$productPrices[$key] = $value;
						}
					if ($customerPrices!= null)
				    foreach($customerPrices as $key=>$value){
		          $value["supplier"]='<a href="'.$this->generateUrl('formSupplier',["id"=>$value['supplier_id']]).'" class="external">'.$value["supplier"].'</a>';
							$value["customer"]='<a href="'.$this->generateUrl('formCustomer',["id"=>$value['customer_id']]).'" class="external">'.$value["customer"].'</a>';
							if (isset($value['start']) && $value['start']!=null){
								$start=date_create($value['start']);
								$value['start'] = date_format($start,'d/m/Y');
							}
							if (isset($value['end']) && $value['end']!=null){
								$end=date_create($value['end']);
								$value['end'] = date_format($end,'d/m/Y');
							}
							unset($value['supplier_id']);
							unset($value['customer_id']);
							unset($value['preference']);
							$customerPrices[$key] = $value;
						}

			    return $this->render('@ERP/productprices.html.twig',[
			      'productpriceslist'=>$productPrices,
						'customerprices'=>$customerPrices,
						'id' => $id,
						'offerpriceslist' => $listOfferPrices->formatListByProduct($id),
						'forms' => $forms
			    ]);
			}else{
				return $this->render('@Globale/error.html.twig', ["error_result"=>-1, "error_text"=>"No existe proveedor preferente para el producto"]);
			}
		}else{
			return $this->render('@Globale/error.html.twig', ["error_result"=>-1, "error_text"=>"No existe el producto"]);
		}
  }

}
