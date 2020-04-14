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
use App\Modules\ERP\Entity\ERPSupplierOrdersData;
use App\Modules\ERP\Utils\ERPSupplierOrdersDataUtils;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;


class ERPSupplierOrdersDataController extends Controller
{
	private $class=ERPSupplierOrdersData::class;
	private $utilsClass=ERPSupplierOrdersDataUtils::class;


	/**
   * @Route("/{_locale}/supplierordersdata/infoSupplierOrdersData/{id}", name="infoSupplierOrdersData", defaults={"id"=0})
   */
  public function infoSupplierOrdersData($id, Request $request){
/*
		$suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$supplier=$suppliersRepository->findOneBy(["id"=>$id]);
  	$supplier_id=$supplier->getId();*/
		$supplierOrdersDataRepository=$this->getDoctrine()->getRepository(ERPSupplierOrdersData::class);
		$supplierOrdersData=$supplierOrdersDataRepository->findOneBy(["supplier"=>$id]);
		$this_id=$supplierOrdersData->getId();
		$template=dirname(__FILE__)."/../Forms/SupplierOrdersData.json";
  	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$formUtils = new GlobaleFormUtils();
		$formUtilsSupplierOrdersData = new ERPSupplierOrdersDataUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsSupplierOrdersData->getExcludedForm([]),$formUtilsSupplierOrdersData->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$this_id, "parent" => $id]));
		//	$listCustomersPrices = new ERPCustomersPricesUtils();
		//$listCustomersCommentLines = new ERPCustomerCommentLinesUtils();
	//	$formUtilsCustomersPrices = new GlobaleFormUtils();
	//$formUtilsCustomersPrices->initialize($this->getUser(), new ERPCustomersPrices(), dirname(__FILE__)."/../Forms/CustomersPrices.json", $request, $this, $this->getDoctrine());
//		$forms[]=$formUtilsCustomersPrices->formatForm('CustomersPrices', true, null, ERPCustomersPrices::class);


	//	$supplierRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
//		$supplier=$supplierRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0, "company"=>$this->getUser()->getCompany()]);

		return $this->render('@ERP/supplierordersdata.html.twig', array(
			'controllerName' => 'supplierOrdersDataController',
			'interfaceName' => 'Proveedores',
			'optionSelected' => 'supplierordersdata',
			'userData' => $userdata,
			'id' => $this_id,
			'parent' => $id,
		/*	'id_object' => $id,*/
			'form' => $formUtils->formatForm('SupplierOrdersData', true, $this_id, $this->class),
			'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
													 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
													 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
			/*,
			'listSuppliersPrices' => $listCustomersPrices->formatListByCustomer($id),
			'listCustomersCommentLines' => $listCustomersCommentLines->formatListByCustomer($id)
			//'forms' => $forms
			*/
		));
  }


	/**
	 * @Route("/{_locale}/supplierordersdata/data/{id}/{parent}/{action}", name="dataSupplierOrdersData", defaults={"id"=0, "parent"=0, "action"="read"})
	 */
	 public function dataSupplierOrdersData($id, $parent, $action, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $this->denyAccessUnlessGranted('ROLE_ADMIN');
	 $template=dirname(__FILE__)."/../Forms/SupplierOrdersData.json";
	 $utils = new GlobaleFormUtils();
	 $obj = new $this->class();
	 $suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
	 $supplier=$suppliersRepository->findOneBy(["id"=>$parent]);
	 $obj->setSupplier($supplier);
	 //$default= new GlobaleCountries();
	 //$default=$default->findById(64);
	 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
	 $utils->values(["supplier"=>$supplier]);
	 $make=$utils->make($id, $this->class, $action, "formSupplierOrdersData", "modal", "@ERP/supplierordersdata.html.twig");
	 return $make;
}


}
