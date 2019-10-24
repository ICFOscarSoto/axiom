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
use App\Modules\ERP\Entity\ERPOfferPrices;
use App\Modules\ERP\Utils\ERPOfferPricesUtils;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;



class ERPOfferPricesController extends Controller
{
	private $class=ERPOfferPrices::class;
	private $utilsClass=ERPOfferPricesUtils::class;
  
	
	
	/**
	 * @Route("/api/offerprices/{id}/list", name="offerpriceslist")
	 */

	public function indexlist($id,RouterInterface $router,Request $request){
		
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$customerRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$customer = $customerRepository->find($id);
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$class=ERPOfferPrices::class;
		$repository = $manager->getRepository($class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/customerOfferPrices.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $class, [["type"=>"and","column"=>"customer", "value"=>$customer]]);
		return new JsonResponse($return);

	}


  /**
   * @Route("/{_locale}/offerprices/infoCustomerOfferPrices/{id}", name="infoCustomerOfferPrices", defaults={"id"=0,"action"="read"})
   */
  public function infoCustomerOfferPrices($id, $action, Request $request){
		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$customer=$customersRepository->findOneBy(["id"=>$id]);
	//	$customer_id=$customer->getId();
    $offerPricesRepository=$this->getDoctrine()->getRepository($this->class);
    $offerPrices=$offerPricesRepository->offerPricesByCustomer($customer);
		$listOfferPrices = new ERPOfferPricesUtils();
		$formUtilsOfferPrices = new GlobaleFormUtils();
		$formUtilsOfferPrices->initialize($this->getUser(), new ERPOfferPricesUtils(), dirname(__FILE__)."/../Forms/CustomerOfferPrices.json", $request, $this, $this->getDoctrine());
		$forms[]=$formUtilsOfferPrices->formatForm('OfferPrices', true, null, ERPOfferPrices::class);
		return $this->render('@ERP/customerofferprices.html.twig', array(
			'id' => $id,
			'listOfferPrices' => $listOfferPrices->formatListByCustomer($id),
			'forms' => $forms
    ));
	
	 
  }
	
	

  
}