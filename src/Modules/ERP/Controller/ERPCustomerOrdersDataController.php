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
use App\Modules\ERP\Entity\ERPCustomerOrdersData;
use App\Modules\ERP\Entity\ERPCustomerCommentLines;
use App\Modules\ERP\Utils\ERPCustomerOrdersDataUtils;
use App\Modules\ERP\Utils\ERPCustomerCommentLinesUtils;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;


class ERPCustomerOrdersDataController extends Controller
{
	private $class=ERPCustomerOrdersData::class;
	private $utilsClass=ERPCustomerOrdersDataUtils::class;


	/**
   * @Route("/{_locale}/customerordersdata/infoCustomerOrdersData/{id}", name="infoCustomerOrdersData", defaults={"id"=0})
   */
  public function infoCustomerOrdersData($id, Request $request){

		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$customer=$customersRepository->findOneBy(["id"=>$id]);
		$customerOrdersDataRepository=$this->getDoctrine()->getRepository(ERPCustomerOrdersData::class);
		$customerOrdersData=$customerOrdersDataRepository->findOneBy(["customer"=>$id]);
		$this_id=$customerOrdersData->getId();
		$template=dirname(__FILE__)."/../Forms/CustomerOrdersData.json";
  	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$formUtils = new GlobaleFormUtils();
		$formUtilsCustomerOrdersData = new ERPCustomerOrdersDataUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsCustomerOrdersData->getExcludedForm([]),$formUtilsCustomerOrdersData->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$this_id, "parent" => $id]));

		/*CUSTOMER COMMENTS*/

		/*orders*/
		$listCustomerCommentLines = new ERPCustomerCommentLinesUtils();
		$formUtilsCustomerCommentLines = new GlobaleFormUtils();
		$formUtilsCustomerCommentLines->initialize($this->getUser(), new ERPCustomerCommentLines(), dirname(__FILE__)."/../Forms/CustomerCommentLines.json", $request, $this, $this->getDoctrine(),$listCustomerCommentLines->getExcludedForm([]),$listCustomerCommentLines->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$customer]));
		$listCustomerCommentLines->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(),"id"=>$this_id, "parent"=>$customer, "type"=>1]);
		$forms[]=$formUtilsCustomerCommentLines->formatForm('CustomerCommentLines', true, null, ERPCustomerCommentLines::class);


		return $this->render('@ERP/customerordersdata.html.twig', array(
			'controllerName' => 'customerOrdersDataController',
			'interfaceName' => 'Clientes',
			'optionSelected' => 'customerordersdata',
			'userData' => $userdata,
			'id' => $this_id,
			'parent' => $id,
		/*	'id_object' => $id,*/
			'form' => $formUtils->formatForm('CustomerOrdersData', true, $this_id, $this->class),
			'customerorderscommentlineslist' => $listCustomerCommentLines->formatListByCustomerType($id,1),
			'forms' => $forms,
		/*	'forms2' => $forms2,*/
			'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
													 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
													 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
		));
  }


	/**
	 * @Route("/{_locale}/customerordersdata/data/{id}/{parent}/{action}", name="dataCustomerOrdersData", defaults={"id"=0, "parent"=0, "action"="read"})
	 */
	 public function datacustomerOrdersData($id, $parent, $action, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $this->denyAccessUnlessGranted('ROLE_ADMIN');
	 $template=dirname(__FILE__)."/../Forms/CustomerOrdersData.json";
	 $utils = new GlobaleFormUtils();
	 $obj = new $this->class();
	 $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
	 $customer=$customersRepository->findOneBy(["id"=>$parent]);
	 $obj->setCustomer($customer);
	 //$default= new GlobaleCountries();
	 //$default=$default->findById(64);
	 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
	 $utils->values(["customer"=>$customer]);
	 $make=$utils->make($id, $this->class, $action, "formCustomerOrdersData", "modal", "@ERP/customerordersdata.html.twig");
	 return $make;
}


}
