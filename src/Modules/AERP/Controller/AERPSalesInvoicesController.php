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
use App\Modules\AERP\Utils\AERPSalesInvoicesUtils;
use App\Modules\AERP\Entity\AERPConfiguration;
use App\Modules\AERP\Entity\AERPPaymentMethods;
use App\Modules\AERP\Entity\AERPSeries;
use App\Modules\AERP\Entity\AERPCustomerGroups;
use App\Modules\AERP\Entity\AERPSalesInvoices;
use App\Modules\AERP\Entity\AERPSalesInvoicesLines;
use App\Modules\AERP\Entity\AERPProducts;
use App\Modules\AERP\Entity\AERPFinancialYears;
use App\Modules\AERP\Reports\AERPSalesInvoicesReports;
use App\Modules\Security\Utils\SecurityUtils;

class AERPSalesInvoicesController extends Controller
{
	private $module='AERP';
	private $prefix='FAC';
	private $class=AERPSalesInvoices::class;
	private $classLines=AERPSalesInvoicesLines::class;
	private $utilsClass=AERPSalesInvoicesUtils::class;

	/**
	 * @Route("/{_locale}/AERP/salesinvoices/form/{id}", name="AERPSalesInvoicesForm", defaults={"id"=0}))
	 */
	public function AERPSalesInvoicesForm($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(AERPConfiguration::class);
		$customerGroupsrepository=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(AERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(AERPSeries::class);
		$documentRepository=$this->getDoctrine()->getRepository($this->class);
		$documentLinesRepository=$this->getDoctrine()->getRepository($this->classLines);

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		//Search Customers
		$classCustomersUtils="\App\Modules\AERP\Utils\AERPCustomersUtils";
		$customersutils = new $classCustomersUtils();
		$customerslist=$customersutils->formatList($this->getUser());
		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$customerslist["topButtons"]=[];

		//Search Products
		$classProductsUtils="\App\Modules\AERP\Utils\AERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
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
		$option["text"]="M??todo pago";
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
		$line=new $this->classLines();
		$line->setTaxperc($config->getDefaulttax()->getTax());

		if($id!=0){
			$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			$documentLines=$documentLinesRepository->findBy(["salesinvoice"=>$document, "active"=>1,"deleted"=>0]);
			$line->setLinenum(count($documentLines)+1);
			array_push($documentLines, $line);
		}
		if($document==null){
			$document=new $this->class();
			$documentLines=[$line];
		}

		$errors=[];
		//Check if the financialyear is open
		if($id==0 && ($config->getFinancialyear()==null || $config->getFinancialyear()->getStatus()==0))
			array_push($errors, "Debe existir un ejercicio fiscal abierto. Puede crear o abrir uno en el menu <a target='_blank' href='".$this->generateUrl("genericindex",["module"=>"AERP", "name"=>"FinancialYears"])."'>\"Ejercicios Fiscales\"</a>, tambi??n tiene que estar establecido como el ejercicio en uso en la <a target='_blank' href='".$this->generateUrl("mycompany")."?tab=AERP'>\"configuraci??n del m??dulo\"</a>.");

		$warnings=[];

		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','AERP','SalesInvoices');
		array_push($breadcrumb,$new_breadcrumb);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@AERP/salesinvoices.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'categoriesController',
				'interfaceName' => 'SalesInvoices',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"AERP", "name"=>"SalesInvoices"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'customerslist' => $customerslist,
				'productslist' => $productslist,
				'customerGroups' => $customerGroups,
				'paymentMethods' => $paymentMethods,
				'series' => $series,
				'date' => ($document->getId()==null)?date('d-m-Y'):$document->getDate()->format('d/m/Y'),
				'id' => $id,
				'documentType' => 'sales_invoice',
				'documentPrefix' => $this->prefix,
				'document' => $document,
				'documentLines' => $documentLines,
				'documentReadonly' => false,
				'errors' => $errors,
				'warnings' => $warnings,
				'token' => uniqid('sign_').time()
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}


	/**
	 * @Route("/{_locale}/AERP/salesinvoices/data/{id}", name="dataAERPSalesInvoices", defaults={"id"=0}))
	 */
	public function data($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository($this->class);
		$documentLinesRepository=$this->getDoctrine()->getRepository($this->classLines);
		$customersRepository=$this->getDoctrine()->getRepository(AERPCustomers::class);
		$customerGroupsRepository=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		$paymentMethodsRepository=$this->getDoctrine()->getRepository(AERPPaymentMethods::class);
		$productsRepository=$this->getDoctrine()->getRepository(AERPProducts::class);
		$seriesRepository=$this->getDoctrine()->getRepository(AERPSeries::class);
		$taxesRepository=$this->getDoctrine()->getRepository(GlobaleTaxes::class);
		$configrepository=$this->getDoctrine()->getRepository(AERPConfiguration::class);

		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		//Check if document belongs to company
		if($id!=0 && !$document) return new JsonResponse(["result"=>0]);
		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		//Get content of the json reques
		$fields=json_decode($request->getContent());
		$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->customerid, "active"=>1, "deleted"=>0]);
		if(!$customer) return new JsonResponse(["result"=>0]); //if no customer, do nothing

		$paymentmethod=$paymentMethodsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->paymentmethod, "active"=>1, "deleted"=>0]);
		$serie=$seriesRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->serie]);
		$customergroup=$customerGroupsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->customergroup, "active"=>1, "deleted"=>0]);

		$date=$fields->date?date_create_from_format("d/m/Y",$fields->date):new \DateTime();
		if(!$document){
			$document=new $this->class();
			$document->setNumber($documentRepository->getNextNum($this->getUser()->getCompany()->getId(),$config->getFinancialyear()->getId(),$serie->getId()));
			$document->setCode($config->getFinancialyear()->getCode().$serie->getCode().str_pad($document->getNumber(), 6, '0', STR_PAD_LEFT));
			$document->setFinancialyear($config->getFinancialyear());
			$document->setAuthor($this->getUser());
			$document->setAgent($this->getUser());
			$document->setSerie($serie);
			$document->setDate($date);
			$document->setPaymentmethod($paymentmethod);
			$document->setFinancialyear($config->getFinancialyear());
			$document->setActive(1);
			$document->setDeleted(0);
			$document->setDateadd(new \DateTime());
		}
		$document->setCompany($this->getUser()->getCompany());
		$document->setCurrency($this->getUser()->getCompany()->getCurrency());
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
		$document->setObservations($fields->observations);
		$document->setNotes($fields->notes);
		$document->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($document);
		$this->getDoctrine()->getManager()->flush();
		$linenumIds=[];

		foreach ($fields->lines as $key => $value) {
			$line=$documentLinesRepository->findOneBy(["salesinvoice"=>$document, "id"=>$value->id]);
			$product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$value->productid, "active"=>1, "deleted"=>0]);
			//if(!$product) continue;
			if($value->code=="") continue;
			if(!$line && $value->deleted) continue;
			if(!$line ){
				$line=new $this->classLines();
				$line->setSalesinvoice($document);
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

	/**
	 * @Route("/{_locale}/AERP/salesinvoices/print/{id}/{mode}", name="AERPSalesInvoicesPrint", defaults={"id"=0, "mode"="print"}))
	 */
	public function AERPSalesInvoicesPrint($id, $mode, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository($this->class);
		$documentLinesRepository=$this->getDoctrine()->getRepository($this->classLines);
		$configrepository=$this->getDoctrine()->getRepository(AERPConfiguration::class);

		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$id, "deleted"=>0]);
		if(!$document) return new JsonResponse(["result"=>-1]);
		$lines=$documentLinesRepository->findBy(["salesinvoice"=>$document, "deleted"=>0, "active"=>1]);
		$configuration=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$document->getId(), "user"=>$this->getUser(), "document"=>$document, "lines"=>$lines, "configuration"=>$configuration];
		$reportsUtils = new AERPSalesInvoicesReports();
		switch($mode){
			case "email":
				$tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Email'.DIRECTORY_SEPARATOR;
				if (!file_exists($tempPath) && !is_dir($tempPath)) {
						mkdir($tempPath, 0775, true);
				}
				$pdf=$reportsUtils->create($params,'F',$tempPath.$this->prefix.$document->getCode().'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
			case "temp":
				$tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Others'.DIRECTORY_SEPARATOR;
				if (!file_exists($tempPath) && !is_dir($tempPath)) {
						mkdir($tempPath, 0775, true);
				}
				$pdf=$reportsUtils->create($params,'F',$tempPath.$this->prefix.$document->getCode().'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
			case "download":
				$pdf=$reportsUtils->create($params,'D',$this->prefix.$document->getCode().'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
			case "print":
			case "default":
				$pdf=$reportsUtils->create($params,'I',$this->prefix.$document->getCode().'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
		}
		return new Response("", 200, array('Content-Type' => 'application/pdf'));
	}

}
