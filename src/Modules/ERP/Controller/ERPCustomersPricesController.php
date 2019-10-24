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
use App\Modules\ERP\Utils\ERPOfferPricesUtils;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPCustomersPrices;
use App\Modules\ERP\Utils\ERPCustomersPricesUtils;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;



class ERPCustomersPricesController extends Controller
{
	private $class=ERPCustomersPrices::class;
	private $utilsClass=ERPCustomersPricesUtils::class;
  
  /**
   * @Route("/{_locale}/customersprices/infoCustomersPrices/{id}", name="infoCustomersPrices", defaults={"id"=0})
   */
  public function infoCustomersPrices($id, Request $request){
		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$customer=$customersRepository->findOneBy(["id"=>$id]);
		$customer_id=$customer->getId();
    $customersPricesRepository=$this->getDoctrine()->getRepository($this->class);
    $customersPrices=$customersPricesRepository->pricesByCustomer($customer);
		$listCustomersPrices = new ERPCustomersPricesUtils();
		$formUtilsCustomersPrices = new GlobaleFormUtils();
		$formUtilsCustomersPrices->initialize($this->getUser(), new ERPCustomersPricesUtils(), dirname(__FILE__)."/../Forms/CustomersPrices.json", $request, $this, $this->getDoctrine(),$listCustomersPrices->getExcludedForm([]),$listCustomersPrices->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$id, "parent"=>$customer]));
		$forms[]=$formUtilsCustomersPrices->formatForm('CustomersPrices', true, null, ERPCustomersPrices::class);
        
    return $this->render('@Globale/list.html.twig', array(
      'listConstructor'=>$listCustomersPrices->formatListByCustomer($id),
			'forms' => $forms
    ));
  }
	  
}