<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleTaxes;
use App\Modules\ERP\Entity\ERPProviders;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPPurchasesBudgetsUtils;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPPurchasesBudgets;
use App\Modules\ERP\Entity\ERPPurchasesBudgetsLines;
use App\Modules\ERP\Entity\ERPPurchasesOrders;
use App\Modules\ERP\Entity\ERPPurchasesOrdersLines;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPFinancialYears;
use App\Modules\ERP\Entity\ERPInputs;
use App\Modules\ERP\Utils\ERPInputsUtils;
use App\Modules\ERP\Reports\ERPPurchasesBudgetsReports;
use App\Modules\Security\Utils\SecurityUtils;

class ERPPurchasesDeliveryNotesController extends Controller
{
	private $module='ERP';
	private $prefix='ALB';
	private $class=ERPPurchasesDeliveryNotes::class;
	private $classLines=ERPPurchasesDeliveryLines::class;
	private $utilsClass=ERPPurchasesDeliveryUtils::class;

	/**
	 * @Route("/{_locale}/ERP/inputs/form/{id}", name="inputsForm", defaults={"id"=0})
	 */
	public function inputsForm($id,RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$breadcrumb=$menurepository->formatBreadcrumb('inputs');
		$inputsRepository=$this->getDoctrine()->getRepository(ERPInputs::class);

		if($request->query->get('code',null)){
			$obj = $inputsRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

		$obj = $inputsRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
		$entity_name=$obj?($obj->getSupplier()->getName().' ('.$obj->getCode().')'):'';
		return $this->render('@Globale/generictabform.html.twig', array(
						'entity_name' => $entity_name,
						'controllerName' => 'CustomersController',
						'interfaceName' => 'Inputs',
						'optionSelected' => 'genericindex',
						'optionSelectedParams' => ["module"=>"ERP", "name"=>"Inputs"],
						'menuOptions' =>  $menurepository->formatOptions($userdata),
						'breadcrumb' => $breadcrumb,
						'userData' => $userdata,
						'id' => $id,
						'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
						'tabs' => [["name" => "data", "icon"=>"fa fa-file", "caption"=>"Datos", "active"=>true, "route"=>$this->generateUrl("dataInput",["id"=>$id])],
											 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"ERPInputs", "module" => "ERP", "types"=>json_encode(["Albarán Proveedor","Recibo Transportista", "Etiqueta Expedición", "Otros"])])]
										],

								'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"],
																		["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"]],
								'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
																		 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]]
								));
	}

	/**
	 * @Route("/{_locale}/ERP/inputs/data/{id}/{action}", name="dataInput", defaults={"id"=0, "action"="read"})
	 */
	 public function dataInput($id, $action, Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $template=dirname(__FILE__)."/../Forms/Inputs.json";
	 $utils = new GlobaleFormUtils();
	 $utilsObj=new ERPInputsUtils();

	 $repository=$this->getDoctrine()->getRepository(ERPInputs::class);
	 $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
	 if($id!=0 && $obj==null){
			return $this->render('@Globale/notfound.html.twig',[]);
	 }
	 $classUtils=new ERPInputsUtils();
	 $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj];
	 $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine(),$classUtils->getExcludedForm($params),$classUtils->getIncludedForm($params),null,[],[],[],true);
	 $make = $utils->make($id, ERPInputs::class, $action, "formInput", "full", "@Globale/form.html.twig",'inputsForm');
	 return $make;
	}


	/**
	 * @Route("/{_locale}/ERP/inputs/discord_notify/{id}", name="inputsDiscordNotify", defaults={"id"=0})
	 */
	public function inputsDiscordNotify($id,RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$breadcrumb=$menurepository->formatBreadcrumb('inputs');
		$inputsRepository=$this->getDoctrine()->getRepository(ERPInputs::class);
		$input=$inputsRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0, "company"=>$this->getUser()->getCompany() ]);

	}
}
