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
use App\Modules\ERP\Entity\ERPCustomerCommercialTerms;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;
use App\Modules\ERP\Utils\ERPCustomerCommercialTermsUtils;
use App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;


class ERPCustomerCommercialTermsController extends Controller
{
	private $class=ERPCustomerCommercialTerms::class;
	private $utilsClass=ERPCustomerCommercialTermsUtils::class;


	/**
   * @Route("/{_locale}/customercommercialterms/infoCustomerCommercialTerms/{id}", name="infoCustomerCommercialTerms", defaults={"id"=0})
   */
  public function infoCustomerCommercialTerms($id, Request $request){

		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$customer=$customersRepository->findOneBy(["id"=>$id]);
		$customerCommercialTermsRepository=$this->getDoctrine()->getRepository(ERPCustomerCommercialTerms::class);
		$customerCommercialTerms=$customerCommercialTermsRepository->findOneBy(["customer"=>$id]);
		if($customerCommercialTerms) $this_id=$customerCommercialTerms->getId();
		else $this_id=0;
		$template=dirname(__FILE__)."/../Forms/CustomerCommercialTerms.json";
  	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$formUtils = new GlobaleFormUtils();
		$formUtilsCustomerCommercialTerms = new ERPCustomerCommercialTermsUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsCustomerCommercialTerms->getExcludedForm([]),$formUtilsCustomerCommercialTerms->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$this_id, "parent" => $id]));



		return $this->render('@ERP/customercommercialterms.html.twig', array(
			'controllerName' => 'customerCommercialTermsController',
			'interfaceName' => 'Clientes',
			'optionSelected' => 'customercommercialterms',
			'userData' => $userdata,
			'id' => $this_id,
			'parent' => $id,
		/*	'id_object' => $id,*/
			'form' => $formUtils->formatForm('CustomerCommercialTerms', true, $this_id, $this->class),
		/*	'forms2' => $forms2,*/
			'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
													 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
													 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
		));
  }


	/**
	 * @Route("/{_locale}/customercommercialterms/data/{id}/{parent}/{action}", name="dataCustomerCommercialTerms", defaults={"id"=0, "parent"=0, "action"="read"})
	 */
	 public function datacustomerCommercialTerms($id, $parent, $action, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $template=dirname(__FILE__)."/../Forms/CustomerCommercialTerms.json";
	 $utils = new GlobaleFormUtils();
	 $obj = new $this->class();
	 $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
	 $customer=$customersRepository->findOneBy(["id"=>$parent]);
	 $obj->setCustomer($customer);
	 //$default= new GlobaleCountries();
	 //$default=$default->findById(64);
	 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
	 $utils->values(["customer"=>$customer]);
	 $make=$utils->make($id, $this->class, $action, "formCustomerCommercialTerms", "modal", "@ERP/customercommercialterms.html.twig");
	 return $make;
}


}
