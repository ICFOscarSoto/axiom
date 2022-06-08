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
use App\Modules\ERP\Entity\ERPSupplierCommentLines;
use App\Modules\ERP\Utils\ERPSupplierOrdersDataUtils;
use App\Modules\ERP\Utils\ERPSupplierCommentLinesUtils;
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

		$suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$supplier=$suppliersRepository->findOneBy(["id"=>$id]);
		$supplierOrdersDataRepository=$this->getDoctrine()->getRepository(ERPSupplierOrdersData::class);
		$supplierOrdersData=$supplierOrdersDataRepository->findOneBy(["supplier"=>$id]);
		$this_id=$supplierOrdersData->getId();
		$template=dirname(__FILE__)."/../Forms/SupplierOrdersData.json";
  	$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$formUtils = new GlobaleFormUtils();
		$formUtilsSupplierOrdersData = new ERPSupplierOrdersDataUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),$formUtilsSupplierOrdersData->getExcludedForm([]),$formUtilsSupplierOrdersData->getIncludedForm(["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "id"=>$this_id, "parent" => $id]));

		/*SUPPLIER COMMENTS*/

		/*rappel*/

		$listSupplierCommentLines = new ERPSupplierCommentLinesUtils();
		$formUtils2=new GlobaleFormUtils();
		$formUtils2->initialize($this->getUser(), ERPSupplierCommentLines::class, dirname(__FILE__)."/../Forms/SupplierCommentLinesOrdersData.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils2->formatForm('SupplierCommentLinesOrdersData', true, null, ERPSupplierCommentLines::class);


		$listSupplierCommentLinesRappel = new ERPSupplierCommentLinesUtils();
	  $formUtils3=new GlobaleFormUtils();
	  $formUtils3->initialize($this->getUser(), ERPSupplierCommentLines::class, dirname(__FILE__)."/../Forms/SupplierCommentLinesOrdersDataRappel.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils3->formatForm('SupplierCommentLinesOrdersDataRappel', true, null, ERPSupplierCommentLines::class);

		$listSupplierCommentLines = new ERPSupplierCommentLinesUtils();
		$formUtils4=new GlobaleFormUtils();
		$formUtils4->initialize($this->getUser(), ERPSupplierCommentLines::class, dirname(__FILE__)."/../Forms/SupplierCommentLinesShippings.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils4->formatForm('SupplierCommentLinesShippings', true, null, ERPSupplierCommentLines::class);

		$listSupplierCommentLines = new ERPSupplierCommentLinesUtils();
		$formUtils5=new GlobaleFormUtils();
		$formUtils5->initialize($this->getUser(), ERPSupplierCommentLines::class, dirname(__FILE__)."/../Forms/SupplierCommentLinesPayments.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils5->formatForm('SupplierCommentLinesPayments', true, null, ERPSupplierCommentLines::class);

		$listSupplierCommentLines = new ERPSupplierCommentLinesUtils();
		$formUtils6=new GlobaleFormUtils();
		$formUtils6->initialize($this->getUser(), ERPSupplierCommentLines::class, dirname(__FILE__)."/../Forms/SupplierCommentLinesSpecials.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils6->formatForm('SupplierCommentLinesSpecials', true, null, ERPSupplierCommentLines::class);

		return $this->render('@ERP/supplierordersdata.html.twig', array(
			'controllerName' => 'supplierOrdersDataController',
			'interfaceName' => 'Proveedores',
			'optionSelected' => 'supplierordersdata',
			'userData' => $userdata,
			'id' => $this_id,
			'parent' => $id,
			'form' => $formUtils->formatForm('SupplierOrdersData', true, $this_id, $this->class),
			'suppliercommentsordersdatalist' => $listSupplierCommentLines->formatListBySupplierTypeOrdersData(1,$id),
			'supplierrappelcommentssordersdatalist'=> $listSupplierCommentLines->formatListBySupplierTypeOrdersDataRappel(3,$id),
			'suppliercommentsshippingslist' => $listSupplierCommentLines->formatListBySupplierTypeShippings(4,$id),
			'suppliercommentspaymentslist' => $listSupplierCommentLines->formatListBySupplierTypePayments(5,$id),
			'suppliercommentsspecialslist' => $listSupplierCommentLines->formatListBySupplierTypeSpecials(6,$id),
			'forms' => $templateForms,
			'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
													 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
													 ["type"=>"js",  "path"=>"/js/jquery.nestable.js"]]
		));
  }


	/**
	 * @Route("/{_locale}/supplierordersdata/data/{id}/{parent}/{action}", name="dataSupplierOrdersData", defaults={"id"=0, "parent"=0, "action"="read"})
	 */
	 public function dataSupplierOrdersData($id, $parent, $action, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
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
