<?php

namespace App\Modules\AERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleTaxes;
use App\Modules\AERP\Entity\AERPProviders;
use App\Modules\AERP\Entity\AERPCustomers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\AERP\Utils\AERPSalesBudgetsUtils;
use App\Modules\AERP\Entity\AERPConfiguration;
use App\Modules\AERP\Entity\AERPPaymentMethods;
use App\Modules\AERP\Entity\AERPSeries;
use App\Modules\AERP\Entity\AERPCustomerGroups;
use App\Modules\AERP\Entity\AERPSalesBudgets;
use App\Modules\AERP\Entity\AERPSalesBudgetsLines;
use App\Modules\AERP\Entity\AERPProducts;
use App\Modules\AERP\Entity\AERPFinancialYears;
use App\Modules\Security\Utils\SecurityUtils;

class AERPSalesBudgetsController extends Controller
{
	private $module='AERP';
	private $class=AERPSalesBudgets::class;
	private $utilsClass=AERPSalesBudgetsUtils::class;

	/**
	 * @Route("/{_locale}/AERP/salesbudgets/form/{id}", name="AERPSalesBudgetsForm", defaults={"id"=0}))
	 */
	public function AERPSalesBudgetsForm($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(AERPConfiguration::class);
		$customerGroupsrepository=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(AERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(AERPSeries::class);
		$documentRepository=$this->getDoctrine()->getRepository(AERPSalesBudgets::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(AERPSalesBudgetsLines::class);

		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;

		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		//Search Customers
		$classCustomersUtils="\App\Modules\AERP\Utils\AERPCustomersUtils";
		$customersutils = new $classCustomersUtils();
		$customerslist=$customersutils->formatList($this->getUser());
		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "default", "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$customerslist["topButtons"]=[];

		//Search Products
		$classProductsUtils="\App\Modules\AERP\Utils\AERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

		//Customer groups combo
		$objects=$customerGroupsrepository->findBy(["company"=>$this->getUser()->getCompany(),"active"=>1,"deleted"=>0]);
		$customerGroups=[];
		$option["id"]=0;
		$option["text"]="Grupo Cliente";
		$customerGroups[]=$option;
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$customerGroups[]=$option;
		}

		//Customer payment methods combo
		$objects=$paymentMethodsrepository->findBy(["company"=>$this->getUser()->getCompany(),"active"=>1,"deleted"=>0]);
		$paymentMethods=[];
		$option["id"]=null;
		$option["text"]="Método pago";
		$paymentMethods[]=$option;
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$paymentMethods[]=$option;
		}

		//Series combo
		$objects=$seriesRepository->findBy(["company"=>$this->getUser()->getCompany(),"active"=>1,"deleted"=>0]);
		$series=[];
		$option["id"]=null;
		$option["text"]="Serie";
		$series[]=$option;
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$series[]=$option;
		}
		//Recover document from persistence
		$document=null;
		$line=new AERPSalesBudgetsLines();
		$line->setTaxperc($config->getDefaulttax()->getTax());

		if($id!=0){
			$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			$documentLines=$documentLinesRepository->findBy(["salesbudget"=>$document, "active"=>1,"deleted"=>0]);
			$line->setLinenum(count($documentLines)+1);
			array_push($documentLines, $line);
		}
		if($document==null){
			$document=new AERPSalesBudgets();
			$documentLines=[$line];
		}

		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','AERP','SalesBudgets');
		array_push($breadcrumb,$new_breadcrumb);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@AERP/salesbudgets.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'categoriesController',
				'interfaceName' => 'SalesBudgets',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"AERP", "name"=>"SalesBudgets"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'customerslist' => $customerslist,
				'productslist' => $productslist,
				'customerGroups' => $customerGroups,
				'paymentMethods' => $paymentMethods,
				'series' => $series,
				'date' => ($document->getId()==null)?date('d-m-Y'):$document->getDate()->format('d/m/Y'),
				'enddate' => ($document->getId()==null)?date('d-m-Y', strtotime(date('d-m-Y'). ' + 30 days')):$document->getDateofferend()->format('d/m/Y'),
				'id' => $id,
				'document' => $document,
				'documentLines' => $documentLines
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}


	/**
	 * @Route("/{_locale}/AERP/salesbudgets/data/{id}", name="dataAERPSalesBudgets", defaults={"id"=0}))
	 */
	public function data($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository(AERPSalesBudgets::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(AERPSalesBudgetsLines::class);
		$customersRepository=$this->getDoctrine()->getRepository(AERPCustomers::class);
		$customerGroupsRepository=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		$paymentMethodsRepository=$this->getDoctrine()->getRepository(AERPPaymentMethods::class);
		$productsRepository=$this->getDoctrine()->getRepository(AERPProducts::class);
		$seriesRepository=$this->getDoctrine()->getRepository(AERPSeries::class);
		$taxesRepository=$this->getDoctrine()->getRepository(GlobaleTaxes::class);

		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		//Check if document belongs to company
		if($id!=0 && !$document) return new JsonResponse(["result"=>0]);


		//Get content of the json reques
		$fields=json_decode($request->getContent());
		$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->customerid, "active"=>1, "deleted"=>0]);
		if(!$customer) return new JsonResponse(["result"=>0]); //if no customer, do nothing

		$paymentmethod=$paymentMethodsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->paymentmethod, "active"=>1, "deleted"=>0]);
		$serie=$seriesRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->serie]);
		$customergroup=$customerGroupsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->customergroup, "active"=>1, "deleted"=>0]);


		$date=$fields->date?date_create_from_format("d/m/Y",$fields->date):new \DateTime();
		if(!$document){
			$document=new AERPSalesBudgets();
			$document->setNumber($documentRepository->getNextNum($this->getUser()->getCompany()->getId()));
			$document->setCode($code='PRE'.$date->format('y').'/'.str_pad($document->getNumber(), 6, '0', STR_PAD_LEFT));
			$document->setAuthor($this->getUser());
			$document->setAgent($this->getUser());
			$document->setActive(1);
			$document->setDeleted(0);
			$document->setDateadd(new \DateTime());
		}
		$document->setCompany($this->getUser()->getCompany());
		$document->setCurrency($this->getUser()->getCompany()->getCurrency());
		$document->setPaymentmethod($paymentmethod);
		$document->setSerie($serie);
		$document->setCustomergroup($customergroup);
		$document->setCustomer($customer);
		$document->setVat($customer->getVat());
		$document->setCustomername($customer->getName());
		$document->setCustomeraddress($customer->getAddress());
		$document->setCustomercountry($customer->getCountry());
		$document->setCustomercity($customer->getCity());
		$document->setCustomerstate($customer->getState());
		$document->setCustomerpostcode($customer->getPostcode());
		$document->setCustomerpostbox($customer->getPostbox());
		$document->setCustomercode($fields->customercode);
		$document->setDate($date);
		$document->setDateofferend($fields->dateofferend?date_create_from_format("d/m/Y",$fields->dateofferend):null);
		$document->setTaxexempt(($fields->taxexempt!="")?filter_var($fields->taxexempt, FILTER_VALIDATE_BOOLEAN):0);
		$document->setSurcharge(($fields->surcharge!="")?filter_var($fields->surcharge, FILTER_VALIDATE_BOOLEAN):0);
		$document->setIrpf(($fields->irpf!="")?filter_var($fields->irpf, FILTER_VALIDATE_BOOLEAN):0);
		$document->setIrpfperc(floatval($fields->irpfperc));
		$document->setTotalnet(floatval($fields->totalnet));
		$document->setTotalbase(floatval($fields->totalnet-$fields->totaldto));
		$document->setTotaldto(floatval($fields->totaldto));
		$document->setTotaltax(floatval($fields->totaltax));
		$document->setTotalsurcharge(floatval($fields->totalsurcharge));
		$document->setTotalirpf(floatval($fields->totalirpf));
		$document->setTotal(floatval($fields->total));
		$document->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($document);
		$this->getDoctrine()->getManager()->flush();
		$linenumIds=[];

		foreach ($fields->lines as $key => $value) {
			$line=$documentLinesRepository->findOneBy(["salesbudget"=>$document, "id"=>$value->id]);
			$product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$value->productid, "active"=>1, "deleted"=>0]);
			//if(!$product) continue;
			if($value->code=="") continue;
			if(!$line && $value->deleted) continue;
			if(!$line ){
				$line=new AERPSalesBudgetsLines();
				$line->setSalesbudget($document);
				$line->setActive(1);
				$line->setDeleted(0);
				$line->setDateadd(new \DateTime());
			}
				$line->setLinenum($value->linenum);
				$line->setProduct($product);
				$line->setCode($value->code);
				$line->setName($value->name);
				$line->setUnitprice(floatval($value->unitprice));
				$line->setQuantity(floatval($value->quantity));
				$line->setDtoperc(floatval($value->disccountperc));
				$line->setDtounit(floatval($value->disccountunit));
				$line->setTaxperc(floatval($value->taxperc));
				$line->setTaxunit(floatval($value->taxunit));
				$line->setIrpfperc(floatval($value->irpfperc));
				$line->setIrpfunit(floatval($value->irpfunit));
				$line->setSurchargeperc(floatval($value->surchargeperc));
				$line->setSurchargeunit(floatval($value->surchargeunit));
				$line->setSubtotal(floatval($value->subtotal));
				$line->setTotal(floatval($value->total));
				if($value->deleted){
					$line->setActive(0);
					$line->setDeleted(1);
				}
				$line->setDateupd(new \DateTime());
				$this->getDoctrine()->getManager()->persist($line);
				$this->getDoctrine()->getManager()->flush();
				$linenumIds[]=["linenum"=>$value->linenum, "id"=>$line->getId()];
		}
		return new JsonResponse(["result"=>1,"data"=>["id"=>$document->getId(), "code"=>$document->getCode(), "date"=>$date->format('d/m/Y'), "lines"=>$linenumIds]]);
		//return new JsonResponse(["result"=>1]);
	}

}
