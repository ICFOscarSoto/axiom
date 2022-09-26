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
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleUsersConfig;
use App\Modules\ERP\Utils\ERPSalesOrdersUtils;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPPaymentMethods;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\ERP\Entity\ERPCustomerGroups;
use App\Modules\ERP\Entity\ERPSalesBudgets;
use App\Modules\ERP\Entity\ERPSalesBudgetsLines;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPSalesOrdersLines;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPFinancialYears;
use App\Modules\ERP\Reports\ERPSalesOrdersReports;
use App\Widgets\Entity\WidgetsERPVendorsorders;
use App\Modules\Globale\Entity\GlobaleUsersWidgets;
use App\Modules\Security\Utils\SecurityUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;


class ERPSalesOrdersController extends Controller
{
	private $module='ERP';
	private $prefix='PED';
	private $class=ERPSalesOrders::class;
	private $classLines=ERPSalesOrdersLines::class;
	private $utilsClass=ERPSalesOrdersUtils::class;

	/**
	 * @Route("/{_locale}/ERP/salesorders/form/{id}", name="ERPSalesOrdersForm", defaults={"id"=0}))
	 */
	public function ERPSalesOrdersForm($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$customerGroupsrepository=$this->getDoctrine()->getRepository(ERPCustomerGroups::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(ERPSeries::class);
		$documentRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPSalesOrdersLines::class);

		if($request->query->get('code',null)){
			$obj = $documentRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		//Search Customers
		$classCustomersUtils="\App\Modules\ERP\Utils\ERPCustomersUtils";
		$customersutils = new $classCustomersUtils();
		$customerslist=$customersutils->formatList($this->getUser());
		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$customerslist["topButtons"]=[];

		//Search Products
		$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
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
		$line=new $this->classLines();
		$line->setTaxperc($config->getDefaulttax()->getTax());

		if($id!=0){
			$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			$documentLines=$documentLinesRepository->findBy(["salesorder"=>$document, "active"=>1,"deleted"=>0]);
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
			array_push($errors, "Debe existir un ejercicio fiscal abierto. Puede crear o abrir uno en el menu <a target='_blank' href='".$this->generateUrl("genericindex",["module"=>"ERP", "name"=>"FinancialYears"])."'>\"Ejercicios Fiscales\"</a>, también tiene que estar establecido como el ejercicio en uso en la <a target='_blank' href='".$this->generateUrl("mycompany")."?tab=ERP'>\"configuración del módulo\"</a>.");

		$warnings=[];
		//Check if the budget is expired
		/*if($id!=0 && $document->getDateofferend()<new \Datetime())
			array_push($warnings, "El periodo de validez del presupuesto ha expirado. Considere generar uno nuevo.");
		if($document->getSalesbudget()!=null)
			array_push($warnings, "Este presupuesto ya esta asociado al pedido número <a href='".$this->generateUrl("ERPSalesOrdersForm",["id"=>$document->getInSalesOrder()->getId()])."'>".$document->getInSalesOrder()->getCode()."</a>, puede editar este presupuesto haciendo click <a href=\"javascript:unlockFields();\">aquí</a> aunque se aconseja generar un nuevo presupuesto o editar directamente el pedido.");*/


		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','SalesOrders');
		array_push($breadcrumb,$new_breadcrumb);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/salesorders.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'categoriesController',
				'interfaceName' => 'SalesOrders',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"ERP", "name"=>"SalesOrders"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'customerslist' => $customerslist,
				'productslist' => $productslist,
				'customerGroups' => $customerGroups,
				'paymentMethods' => $paymentMethods,
				'series' => $series,
				'date' => ($document->getId()==null)?date('d-m-Y'):$document->getDate()->format('d/m/Y'),
				'enddate' => ($document->getId()==null)?date('d-m-Y', strtotime(date('d-m-Y'). ' + '.$config->getBudgetexpiration().' '.$config->getBudgetexpirationtype())):$document->getDateofferend()->format('d/m/Y'),
				'id' => $id,
				'documentType' => 'sales_order',
				'documentPrefix' => $this->prefix,
				'document' => $document,
				'documentLines' => $documentLines,
				'documentReadonly' => $document->getSalesbudget()!=null?true:false,

				'errors' => $errors,
				'warnings' => $warnings,
				'token' => uniqid('sign_').time()
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}


	/**
	 * @Route("/{_locale}/ERP/SalesOrders/data/{id}", name="dataERPSalesOrders", defaults={"id"=0}))
	 */
	public function data($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPSalesOrdersLines::class);
		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$customerGroupsRepository=$this->getDoctrine()->getRepository(ERPCustomerGroups::class);
		$paymentMethodsRepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
		$productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$seriesRepository=$this->getDoctrine()->getRepository(ERPSeries::class);
		$taxesRepository=$this->getDoctrine()->getRepository(GlobaleTaxes::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);

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
			$document=new ERPSalesOrders();
			$document->setNumber($documentRepository->getNextNum($this->getUser()->getCompany()->getId(),$config->getFinancialyear()->getId(),$serie->getId()));
			$document->setCode($config->getFinancialyear()->getCode().$serie->getCode().str_pad($document->getNumber(), 6, '0', STR_PAD_LEFT));
			$document->setFinancialyear($config->getFinancialyear());
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
		$document->setObservations($fields->observations);
		$document->setNotes($fields->notes);
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
				$line=new ERPSalesOrdersLines();
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

	/**
	 * @Route("/{_locale}/ERP/SalesOrders/document/{id}/{mode}", name="ERPSalesOrdersPrint", defaults={"id"=0, "mode"="print"}))
	 */
	public function ERPSalesOrdersPrint($id, $mode, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository($this->class);
		$documentLinesRepository=$this->getDoctrine()->getRepository($this->classLines);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$id, "deleted"=>0]);
		if(!$document) return new JsonResponse(["result"=>-1]);
		$lines=$documentLinesRepository->findBy(["salesorder"=>$document, "deleted"=>0, "active"=>1]);
		$configuration=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$document->getId(), "user"=>$this->getUser(), "document"=>$document, "lines"=>$lines, "configuration"=>$configuration];
		$reportsUtils = new ERPSalesOrdersReports();
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
		return new JsonResponse(["result"=>0]);
	}

	/**
	 * @Route("/{_locale}/ERP/SalesOrders/createorder/{id}", name="ERPOrderFromSalesOrders", defaults={"id"=0}))
	 */
	public function ERPOrderFromSalesOrders($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPSalesOrdersLines::class);
		$orderRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
		$orderLinesRepository=$this->getDoctrine()->getRepository(ERPSalesOrdersLines::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);

		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		if(!$document) return new Response("");
		$lines=$documentLinesRepository->findBy(["salesbudget"=>$document, "deleted"=>0, "active"=>1]);
		$configuration=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);
		if($document->getInSalesOrder()!=null) return new RedirectResponse($this->generateUrl('ERPSalesOrdersForm',["id"=>$document->getInSalesOrder()->getId()]));
		//Create the order
		$order=new ERPSalesOrders();
		$order->setNumber($orderRepository->getNextNum($this->getUser()->getCompany()->getId(),$configuration->getFinancialyear()->getId(),$document->getSerie()->getId()));
		$order->setCode($configuration->getFinancialyear()->getCode().$document->getSerie()->getCode().str_pad($order->getNumber(), 6, '0', STR_PAD_LEFT));
		$order->setFinancialyear($configuration->getFinancialyear());
		$order->setAuthor($this->getUser());
		$order->setAgent($this->getUser());
		$order->setActive(1);
		$order->setDeleted(0);
		$order->setDateadd(new \DateTime());
		$order->setCompany($this->getUser()->getCompany());
		$order->setCurrency($document->getCurrency());
		$order->setPaymentmethod($document->getPaymentmethod());
		$order->setSerie($document->getSerie());
		$order->setCustomergroup($document->getCustomergroup());
		$order->setCustomer($document->getCustomer());
		$order->setVat($document->getVat());
		$order->setCustomername($document->getCustomername());
		$order->setCustomeraddress($document->getCustomeraddress());
		$order->setCustomercountry($document->getCustomercountry());
		$order->setCustomercity($document->getCustomercity());
		$order->setCustomerstate($document->getCustomerstate());
		$order->setCustomerpostcode($document->getCustomerpostcode());
		$order->setCustomerpostbox($document->getCustomerpostbox());
		$order->setCustomercode($document->getCustomerpostbox());
		$order->setDate(new \DateTime());
		$order->setTaxexempt($document->getTaxexempt());
		$order->setSurcharge($document->getSurcharge());
		$order->setIrpf($document->getIrpf());
		$order->setIrpfperc($document->getIrpfperc());
		$order->setTotalnet($document->getTotalnet());
		$order->setTotalbase($document->getTotalbase());
		$order->setTotaldto($document->getTotaldto());
		$order->setTotaltax($document->getTotaltax());
		$order->setTotalsurcharge($document->getTotalsurcharge());
		$order->setTotalirpf($document->getTotalirpf());
		$order->setTotal($document->getTotal());
		$order->setObservations("");
		$order->setNotes("");
		$order->setDateupd(new \DateTime());
		$this->getDoctrine()->getManager()->persist($order);
		$this->getDoctrine()->getManager()->flush();

		$document->setInSalesOrder($order);
		$this->getDoctrine()->getManager()->persist($document);
		$this->getDoctrine()->getManager()->flush();
		//Create the lines
		$linenum=1;
		foreach ($lines as $key => $line) {
			$orderLine=new ERPSalesOrdersLines();
			$orderLine->setSalesorder($order);
			$orderLine->setActive(1);
			$orderLine->setDeleted(0);
			$orderLine->setDateadd(new \DateTime());
			$orderLine->setLinenum($linenum++);
			$orderLine->setProduct($line->getProduct());
			$orderLine->setCode($line->getCode());
			$orderLine->setName($line->getName());
			$orderLine->setUnitprice($line->getUnitprice());
			$orderLine->setQuantity($line->getQuantity());
			$orderLine->setDtoperc($line->getDtoperc());
			$orderLine->setDtounit($line->getDtounit());
			$orderLine->setTaxperc($line->getTaxperc());
			$orderLine->setTaxunit($line->getTaxunit());
			$orderLine->setIrpfperc($line->getIrpfperc());
			$orderLine->setIrpfunit($line->getIrpfunit());
			$orderLine->setSurchargeperc($line->getSurchargeperc());
			$orderLine->setSurchargeunit($line->getSurchargeunit());
			$orderLine->setSubtotal($line->getSubtotal());
			$orderLine->setTotal($line->getTotal());
			$orderLine->setActive(1);
			$orderLine->setDeleted(0);
			$orderLine->setDateupd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($orderLine);
			$this->getDoctrine()->getManager()->flush();
		}

		return new RedirectResponse($this->generateUrl('ERPSalesOrdersForm',["id"=>$order->getId()]));
	}

	/**
	 * @Route("/api/ERP/widget/salesvendor/{id}", name="widgetSalesvendor", defaults={"id"=0})
	 */
	public function widgetSalesvendor($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$widgetUserRepository=$this->getDoctrine()->getRepository(GlobaleUsersWidgets::class);
		$widgetRepository=$this->getDoctrine()->getRepository(WidgetsERPVendorsorders::class);
		$widgetUser=$widgetUserRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
		if(!$widgetUser) return new JsonResponse(["result"=>-1]);
		if($widgetUser->getUser()!=$this->getUser()) return new JsonResponse(["result"=>-1]);
		$widget=$widgetRepository->findOneBy(["userwidget"=>$widgetUser, "active"=>1, "deleted"=>0]);
		if(!$widget) return new JsonResponse(["result"=>-1]);
		if($widget->getStart()!=null){
			$start=$widget->getStart()->format("Y-m-d");
			$from=$widget->getStart()->format('d/m/Y');
		}else{
			$start=(new \Datetime())->sub(new \DateInterval('P1M'))->format("Y-m-d");
			$from=(new \Datetime())->sub(new \DateInterval('P1M'))->format("d/m/Y");
		}
		if($widget->getEnd()!=null){
			$end=$widget->getEnd()->format("Y-m-d");
			$to=$widget->getEnd()->format('d/m/Y');
		}else{
			$end=(new \Datetime())->format("Y-m-d");
			$to=(new \Datetime())->format("d/m/Y");
		}
		$array_orders=$widgetRepository->getOrdersbyVendor($this->getUser()->getCompany(), $start, $end);
		$array_budgets=$widgetRepository->getBudgetsbyVendor($this->getUser()->getCompany(), $start, $end);
		return new JsonResponse(["from"=>$from, "to"=>$to, "orders"=>$array_orders, "budgets"=>$array_budgets]);
	}


		  /**
		   * @Route("/api/salesorders/listwithnumber", name="salesorderlistwithnumber")
		   */
		  public function indexlistwithnumber(RouterInterface $router,Request $request){
		    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		    $user = $this->getUser();
		    $locale = $request->getLocale();
		    $this->router = $router;
		    $manager = $this->getDoctrine()->getManager();
		    $repository = $manager->getRepository($this->class);
		    $listUtils=new GlobaleListUtils();
		    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesOrdersWithNumber.json"),true);
		    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ERPSalesOrders::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
		    return new JsonResponse($return);
		  }


			/**
		 * @Route("/api/ERP/salesorders/products/get/{code}", name="getSalesOrderProducts", defaults={"code"=0}))
		 */
		 public function getSalesOrderProducts($code, RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$orderRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
			$orderLinesRepository=$this->getDoctrine()->getRepository(ERPSalesOrdersLines::class);
			$order=$orderRepository->findOneBy(["code"=>$code,"active"=>1,"deleted"=>0]);

			$lines=$orderLinesRepository->findBy(["salesorder"=>$order,"active"=>1,"deleted"=>0]);

			$response=Array();

			foreach($lines as $line){
				$item['productid']=$line->getProduct()->getId();
				$item['name']=$line->getProduct()->getName();
				$item['linenum']=$line->getLinenum();
				$item['code']=$line->getCode();
				$item['variant']=null;
				$item['quantity']=$line->getQuantity();
				$response[]=$item;
			}

			return new JsonResponse(["lines"=>$response]);

		 }


		 /**
		* @Route("/{_locale}/ERP/salesorders/commissions/", name="salesCommissions")
		* Muestra la ficha de comisiones por vendedor
		*/
		public function salesCommissions(RouterInterface $router,Request $request){
			// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
				return $this->redirect($this->generateUrl('unauthorized'));

			$erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);

			$globaleUsersRepository						= $this->getDoctrine()->getRepository(GlobaleUsers::class);
			$globaleMenuOptionsRepository			= $this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$globaleUsersConfigRepository			= $this->getDoctrine()->getRepository(GlobaleUsersConfig::class);

			// Datos de usuario
			$userdata				= $this->getUser()->getTemplateData($this, $this->getDoctrine());
			$company 				= $this->getUser()->getCompany();

			// Configuración (nº decimales, color...etc)
			$config	= $erpConfigurationRepository->findOneBy(["company"=>$company]);

			// Líneas -------------------------------
			// Búsqueda de vista de usuario
			$tabs 	 = null;
			$tabsUser= $globaleUsersConfigRepository->findOneBy(["element"=>"commissions","view"=>"Defecto","attribute"=>"tabs","active"=>1,"deleted"=>0,"company"=>$company,"user"=>$this->getUser()]);
			if ($tabsUser!=null){
				$tabs = json_encode($tabsUser->getValue());
			}

			// Agentes y usuarios en general (combo)
			$oagents=$globaleUsersRepository->findBy(["company"=>$company, "active"=>1, "deleted"=>0],["name"=>"ASC"]);
			$agents=[];
			$option=[];
			$option["pos"]=0;
			$option["id"]=null;
			$option["text"]="Selecciona Agente...";
			$agents[]=$option;
			$pos = 1;

			foreach($oagents as $item){
				$option=[];
				$option["pos"]=$pos;
				$option["id"]=$item->getId();
				$option["text"]=$item->getName()." ".$item->getLastname();
				$agents[]=$option;
				$pos++;
			}

			// Miga
			$nbreadcrumb=["rute"=>null, "name"=>"Por Vendedor", "icon"=>"fa fa-edit"];
			$breadcrumb=$globaleMenuOptionsRepository->formatBreadcrumb('salesCommissions',null,null);
			array_push($breadcrumb,$nbreadcrumb);

			// Decimales
			$ndecimals = 2;
			if ($config != null && $config->getDecimals()!=null)
				$ndecimals = $config->getDecimals();
			$decimals = str_repeat('0',$ndecimals);

			$lines=[];

			$spreadsheet = [];
			$spreadsheet['name']       = "jexcelcommissions";
			$spreadsheet['options']    = "pagination:500";
		  $spreadsheet['prototipe']  = "{
				id:'',
				deliverynote:'',
				date:'',
				productcode:'',
				productname:'',
				variant:'',
				quantity:1,
				price:'0.$decimals',
				cost:'0.$decimals',
				costlastbuy:'0.$decimals',
				margin:'0.$decimals',
				commission:'0.$decimals',
				importcommission:'0.$decimals',
			}";
			if ($tabs!=null){
				$spreadsheet['tabsload'] = 1;
				$spreadsheet['tabs']   	 = $tabs;
			}else{
				$spreadsheet['tabsload'] = 0;
				$spreadsheet['tabs']   		 =
			 "[
				{ caption:'Comisión',
					columns:[
						{name:'deliverynote'},
						{name:'date'},
						{name:'productcode'},
						{name:'productname'},
						{name:'variant'},
						{name:'quantity'},
						{name:'price'},
						{name:'cost'},
						{name:'costlastbuy'},
						{name:'margin'},
						{name:'type'},
						{name:'commission'},
						{name:'importcommission'}
					]
				}
				]";
			}
			$spreadsheet['columns']    =
			 "[
				 { name: 'id', type: 'numeric', width:'40px', title: 'ID', align: 'left'},
				 { name: 'deliverynote', type: 'text', width:'75px', title: 'Albarán', readOnly:true, align: 'left' },
				 { name: 'date', type: 'text', width:'75px', title: 'Fecha', readOnly:true, align: 'center' },
				 { name: 'productcode', type: 'text', width:'75px', title: 'Código', readOnly:true, align: 'left' },
				 { name: 'productname', type: 'text', width:'300px', title: 'Nombre', readOnly:true, align: 'left' },
				 { name: 'variant', type: 'text', width:'50px', title: 'Variante', readOnly:true, align: 'left' },
				 { name: 'quantity', type: 'numeric', width:'50px', title: 'Cantidad', readOnly:true, align: 'right'  },
				 { name: 'price', type: 'numeric', decimal: '".$ndecimals."', width:'75px', title: 'Precio (€)', readOnly:true, align: 'right'},
				 { name: 'cost', type: 'numeric', decimal: '".$ndecimals."', width:'75px', title: 'Coste (€)', readOnly:true, align: 'right'},
				 { name: 'costlastbuy', type: 'numeric', decimal: '".$ndecimals."', width:'75px', title: 'U. Compra (€)', readOnly:true, align: 'right'},
				 { name: 'margin', type: 'numeric', decimal: '".$ndecimals."', width:'50px', title: 'Margen (%)', readOnly:true, align: 'right'},
				 { name: 'type', type: 'text', width:'50px', title: 'Tipo', readOnly:true, align: 'center'},
				 { name: 'commission', type: 'numeric', decimal: '".$ndecimals."', width:'50px', title: 'Porc. Com. (%)', align: 'right',
				 	options: {
				 		onchange: {
				 			oncomplete: 'calculateCommissionsLine'
				 		}
				 	}},
				 { name: 'importcommission', type: 'numeric', decimal: '".$ndecimals."', width:'75px', title: 'Imp. Com.(€)', readOnly:true, align: 'right'}
			 ]";

			 // Cargar de base de datos
			 $spreadsheet['data']       = json_encode($lines);
			 $spreadsheet['onchangepage'] 	   = "
					var data 		= this.getData();
					for(i=0; i<data.length; i++){
						var color = \"initial\";
						var colorMargin = \"initial\";
						var commission=this.getValueFromKey('commission', i, true);
						if (commission == null || commission == '' || isNaN(commission) || isNaN(parseFloat(commission))) commission = 100;
						if(commission>=2)	color='#bff0d0';
							else if (commission>=1.5) color='#f0edbf';
								else color='#f0bfbf';
						$(\"[data-x='\"+11+\"'][data-y='\"+i+\"']\").css(\"background-color\",color);

						var margin=this.getValueFromKey('margin', i, true);
						if (margin == null || margin == '' || isNaN(margin) || isNaN(parseFloat(margin))) margin = 100;
						if(margin<=0)	colorMargin='#ff6c6c';
						$(\"[data-x='\"+9+\"'][data-y='\"+i+\"']\").css(\"background-color\",colorMargin);
					}
			 ";
			 $spreadsheet['onload'] 	   =
				 "
					var sheet = null;
					if (typeof(document.getElementById('".$spreadsheet['name']."').jexcel[0]) == 'undefined')
					 sheet = document.getElementById('".$spreadsheet['name']."').jexcel;
					else
					 sheet = document.getElementById('".$spreadsheet['name']."').jexcel[0];
					var data    = sheet.getData();
					var columns = sheet.options.columns;
				 ";


			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
					return $this->render('@ERP/sales_commissions.html.twig', [
						'moduleConfig' => $config,
						'controllerName' => 'salesOrdersController',
						'interfaceName' => 'Commissions',
						'optionSelected' => 'salesCommissions',
						'optionSelectedParams' => [],
						'menuOptions' =>  $globaleMenuOptionsRepository->formatOptions($userdata),
						'breadcrumb' =>  $breadcrumb,
						'userData' => $userdata,
						'agents' => $agents,
						'spreadsheet' => $spreadsheet,
						'include_header' => [["type"=>"css", "path"=>"js/jexcel/jexcel.css"],
																 ["type"=>"js",  "path"=>"js/jexcel/jexcel.js"],
																 ["type"=>"css", "path"=>"js/jsuites/jsuites.css"],
																 ["type"=>"js",  "path"=>"js/jsuites/jsuites.js"]
															 	],
						]);
				}
				return new RedirectResponse($this->router->generate('app_login'));

		}

		/**
	 * @Route("/api/ERP/salesorders/commissions/getvendorsales/{id}", name="getvendorsales")
	 */
	 public function getvendorsales($id,RouterInterface $router,Request $request){
		 // El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
			 return $this->redirect($this->generateUrl('unauthorized'));

	   $globaleUsersRepository						= $this->getDoctrine()->getRepository(GlobaleUsers::class);
		 $erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);

		 // Configuración (nº decimales, color...etc)
		 $config	= $erpConfigurationRepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		 $user=$globaleUsersRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
		 if(!$user) return new JsonResponse(["result"=>-1]);

		 $from=$request->request->get('from');
		 $to=$request->request->get('to');

		 // Decimales
		 $ndecimals = 2;
		 if ($config != null && $config->getDecimals()!=null)
			 $ndecimals = $config->getDecimals();
		 $decimals = str_repeat('0',$ndecimals);
 	   $json=file_get_contents('http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-getSalesBySalesperson.php?from='.$from.'&to='.$to.'&salesperson='.$user->getEmail());
		 $olines=json_decode($json, true);
		 if(count($olines)>0)
		 	$olines=$olines[0];
		 $lines = [];
		 if ($olines["class"]!=null){
			for($i=0; $i<count($olines["class"]); $i++){
					$lines[$i] = [];
					$lines[$i]['id'] = $i;
					$lines[$i]['deliverynote'] = $olines["class"][$i]["deliverynote"];
					$lines[$i]['customercode'] = $olines["class"][$i]["customercode"];
					$lines[$i]['date']		= (($olines["class"][$i]["date"]!=null)?substr($olines["class"][$i]["date"]["date"],0,10):"");
					$lines[$i]['productcode'] = $olines["class"][$i]["code"];
					$lines[$i]['productname'] = $olines["class"][$i]["description"];
					$lines[$i]['variant'] = $olines["class"][$i]["variant"];
					$lines[$i]['quantity'] = round($olines["class"][$i]["qty"],1);
					$lines[$i]['price'] = round($olines["class"][$i]["price"],$ndecimals);
					$lines[$i]['cost'] = round($olines["class"][$i]["cost"],$ndecimals);
					$lines[$i]['costlastbuy'] = floatval(round(floatval($olines["class"][$i]["purchase_cost"])*floatval($lines[$i]['quantity']),$ndecimals));
					$lines[$i]['type'] = $olines["class"][$i]["type"];
					$lines[$i]['margin'] = $lines[$i]['costlastbuy']==0?0:round((($lines[$i]['price']/$lines[$i]['costlastbuy'])-1)*100,2);

					if($user->getEmail()=="juanjo.molina@ferreteriacampollano.com"){
						$lines[$i]['commission'] = $lines[$i]['type']=='Directa'?($lines[$i]['margin']>20?1.5:($lines[$i]['margin']>=15?1.5:0)):($lines[$i]['margin']>=15?1.5:0);
					}else{
						//Si el tipo es indirecto siempre y el margen es mayor o igual al 15 1.5%, si es directo: si el margen > 20 -> 2% si esta entre 20 y 15 (incluido) 1.5%, si es menor que 15 el 0%
						$lines[$i]['commission'] = $lines[$i]['type']=='Directa'?($lines[$i]['margin']>20?2:($lines[$i]['margin']>=15?1.5:0)):($lines[$i]['margin']>=15?1.5:0);
					}
					$lines[$i]['importcommission'] = 	round($lines[$i]['price'] * ($lines[$i]['commission']/100),$ndecimals);
			}

			return new JsonResponse(["result"=>1, "data"=>$lines, "topcustomers"=>$olines["topcustomers"]]);
		 }
		 return new JsonResponse(["result"=>-2]);
	 }

	 /**
	* @Route("/{_locale}/ERP/salesdeliverynotes/form/{id}", name="salesdeliverynotes")
	* Muestra el albaran digitalizado de la venta
	*/
 	public function salesdeliverynotes($id,RouterInterface $router,Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 	 	 $code=$request->query->get('code');
		 $dir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'ERPSignedDeliveryNotes'.DIRECTORY_SEPARATOR.'20'.substr($code,0,2).DIRECTORY_SEPARATOR;
		 $iterator = new \RecursiveDirectoryIterator($dir);
		 foreach(new \RecursiveIteratorIterator($iterator) as $file){
		     if(substr(basename($file),13)==$code.'.pdf'){
				 $response = new BinaryFileResponse($file->getPathname());
				 $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
				 if($mimeTypeGuesser->isSupported()){
					$response->headers->set('Content-Type', $mimeTypeGuesser->guess($file->getPathname()));
				 }else{
					$response->headers->set('Content-Type', 'text/plain');
				 }
				 $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$code.'.pdf');
				 return $response;
			 }
		 }
		 return new Response('Albarán no encontrado');
	}



	/**
 * @Route("/{_locale}/ERP/salesorders/signeddeliverynotesfails/", name="signeddeliverynotesfails")
 * Muestra el listado de fallos en el escaneo de albaranes firmados
 */
 public function signeddeliverynotesfails(RouterInterface $router,Request $request){
	 // El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
		 return $this->redirect($this->generateUrl('unauthorized'));

	 $erpConfigurationRepository				= $this->getDoctrine()->getRepository(ERPConfiguration::class);
	 $globaleUsersRepository						= $this->getDoctrine()->getRepository(GlobaleUsers::class);
	 $globaleMenuOptionsRepository			= $this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
	 $globaleUsersConfigRepository			= $this->getDoctrine()->getRepository(GlobaleUsersConfig::class);

	 // Configuración (nº decimales, color...etc)
	 $config	= $erpConfigurationRepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

	 // Miga
	 $nbreadcrumb=["rute"=>null, "name"=>"Archivos", "icon"=>"fa fa-edit"];
	 $breadcrumb=$globaleMenuOptionsRepository->formatBreadcrumb('signeddeliverynotesfails',null,null);
	 array_push($breadcrumb,$nbreadcrumb);

	 // Datos de usuario
	 $userdata				= $this->getUser()->getTemplateData($this, $this->getDoctrine());
	 $company 				= $this->getUser()->getCompany();

	 //Obtener ficheros de escaneo con errores
	 $dir=$_ENV['SIGNEDDELIVERYNOTES_FAIL_PATH'];
	 $files=[];
	 if(!file_exists($dir) || !is_dir($dir)){
		 return $this->render('@Globale/genericerror.html.twig', [
				 'interfaceName' => 'Signed delivery notes fails',
				 'userData' => $userdata,
				 'optionSelected' => 'signeddeliverynotesfails',
				 'menuOptions' =>   $globaleMenuOptionsRepository->formatOptions($userdata),
				 'breadcrumb' =>  $breadcrumb,
				 "error"=>["symbol"=> "entypo-attention",
									 "title" => "Error de archivos",
									 "description"=>"No existe el directorio de trabajo"
								 ]
			 ]);
	 }

	 //Recorremos todos los archivos en el directorio
	 $filesIterator = new \DirectoryIterator($dir);
	 $i=0;
	 foreach ($filesIterator as $fileinfo) {
			 if (!$fileinfo->isDot()) {
				 $file["id"]=$i++;
				 $file["file"]=$fileinfo->getFilename();
				 $file["deliverynote"]="";
				 $file["options"]="";
				 $files[]=$file;
			 }
		}

		$lines=[];

		$spreadsheet = [];
		$spreadsheet['name']       = "jexcelsigneddeliverynotesfails";
		$spreadsheet['options']    = "pagination:25";
		$spreadsheet['prototipe']  = "{
			id:'',
			file:'',
			deliverynote:'',
			options:''
		}";

		$spreadsheet['tabsload'] = 0;
		$spreadsheet['tabs']   		 =
	 "[
		{ caption:'Fallos',
			columns:[
				{name:'file'},
				{name:'deliverynote'},
				{name:'options'}
			]
		}
		]";

		$spreadsheet['columns']    =
		 "[
			 { name: 'id', type: 'numeric', width:'40px', title: 'ID', align: 'left'},
			 { name: 'file', type: 'text', width:'120px', title: 'Archivo', readOnly:true, align: 'left' },
			 { name: 'deliverynote', type: 'text', width:'75px', title: 'Albarán', readOnly:false, align: 'left' },
			 { name: 'options', type: 'text', width:'75px', title: 'Operaciones', readOnly:true, align: 'center'},
		 ]";

		 // Cargar de base de datos
		 $spreadsheet['data']       = json_encode($files);
		 $spreadsheet['onselection'] 	   = "
		 		if(y1==y2){
						$('".'#'."signeddeliverynotesfails-row-id').val(y1);
						$('".'#'."signeddeliverynotesfails-iframe').attr('src', '/api/ERP/salesorders/signeddeliverynotesfails/preview/'+sheet.getValueFromKey('file', y1, true)+'#toolbar=0');
				}
		 ";
		 $spreadsheet['onload'] 	   =
			 "
				var sheet = null;
				if (typeof(document.getElementById('".$spreadsheet['name']."').jexcel[0]) == 'undefined')
				 sheet = document.getElementById('".$spreadsheet['name']."').jexcel;
				else
				 sheet = document.getElementById('".$spreadsheet['name']."').jexcel[0];
				var data    = sheet.getData();
				var columns = sheet.options.columns;
				$('".'#'."jexcelsigneddeliverynotesfails').find('td[data-x=\"2\"]').each(function( index ) {
					if($(this).attr('data-y')>=0){
						$(this).html('<div class=\"btn-group\">	<button attr-id=\"'+$(this).attr('data-y')+'\" attr-file=\"'+sheet.getValueFromKey('file', $(this).attr('data-y'), true)+'\" id=\"signeddeliverynotesfails-savefile-'+$(this).attr('data-y')+'\" type=\"button\" class=\"btn btn-default tooltip-primary\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"\" data-original-title=\" Editar entrada \"><i class=\"fa fa-check-square-o\" aria-hidden=\"true\"></i></button><button attr-id=\"'+$(this).attr('data-y')+'\" attr-file=\"'+sheet.getValueFromKey('file', $(this).attr('data-y'), true)+'\" id=\"signeddeliverynotesfails-deletefile-'+$(this).attr('data-y')+'\" type=\"button\" class=\"btn btn-danger tooltip-primary\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"\" data-original-title=\" Borrar archivo \"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></button></div>');
					}
				});
			 ";

			 $spreadsheet['onchangepage'] 	   = "
					var data 		= this.getData();
					$('".'#'."jexcelsigneddeliverynotesfails').find('td[data-x=\"2\"]').each(function( index ) {
						if($(this).attr('data-y')>=0){
	  					$(this).html('<div class=\"btn-group\">	<button attr-id=\"'+$(this).attr('data-y')+'\" attr-file=\"'+sheet.getValueFromKey('file', $(this).attr('data-y'), true)+'\" id=\"signeddeliverynotesfails-savefile-'+$(this).attr('data-y')+'\" type=\"button\" class=\"btn btn-default tooltip-primary\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"\" data-original-title=\" Editar entrada \"><i class=\"fa fa-check-square-o\" aria-hidden=\"true\"></i></button><button attr-id=\"'+$(this).attr('data-y')+'\" attr-file=\"'+sheet.getValueFromKey('file', $(this).attr('data-y'), true)+'\" id=\"signeddeliverynotesfails-deletefile-'+$(this).attr('data-y')+'\" type=\"button\" class=\"btn btn-danger tooltip-primary\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"\" data-original-title=\" Borrar archivo \"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></button></div>');
						}
					});
			 ";


	 if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			 return $this->render('@ERP/signed_deliverynotes_fails.html.twig', [
				 'moduleConfig' => $config,
				 'controllerName' => 'salesOrdersController',
				 'interfaceName' => 'Signed delivery notes fails',
				 'optionSelected' => 'signeddeliverynotesfails',
				 'optionSelectedParams' => [],
				 'menuOptions' =>  $globaleMenuOptionsRepository->formatOptions($userdata),
				 'breadcrumb' =>  $breadcrumb,
				 'userData' => $userdata,
				 'files' => $files,
				 'spreadsheet' => $spreadsheet,
				 'include_header' => [["type"=>"css", "path"=>"js/jexcel/jexcel.css"],
															["type"=>"js",  "path"=>"js/jexcel/jexcel.js"],
															["type"=>"css", "path"=>"js/jsuites/jsuites.css"],
															["type"=>"js",  "path"=>"js/jsuites/jsuites.js"]
														 ],
				 ]);
		 }
		 return new RedirectResponse($this->router->generate('app_login'));
}


/**
* @Route("/api/ERP/salesorders/signeddeliverynotesfails/preview/{file}", name="signeddeliverynotesfails_preview")
* Previsualiza el pdf de un archivo de fallo
*/
public function signeddeliverynotesfails_preview($file, RouterInterface $router,Request $request){
	// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
		return $this->redirect($this->generateUrl('unauthorized'));

	$dir=$_ENV['SIGNEDDELIVERYNOTES_FAIL_PATH'];
	if(file_exists($dir.$file)){
		$response = new BinaryFileResponse($dir.$file);
		$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
		if($mimeTypeGuesser->isSupported()){
		 $response->headers->set('Content-Type', $mimeTypeGuesser->guess($dir.$file));
		}else{
		 $response->headers->set('Content-Type', 'text/plain');
		}
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$file);
		return $response;
	}
	return new Response('Albarán no encontrado');
}

/**
* @Route("/api/ERP/salesorders/signeddeliverynotesfails/remove/{file}", name="signeddeliverynotesfails_remove")
* Previsualiza el pdf de un archivo de fallo
*/
public function signeddeliverynotesfails_remove($file, RouterInterface $router,Request $request){
	// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
		return $this->redirect($this->generateUrl('unauthorized'));

	$dir=$_ENV['SIGNEDDELIVERYNOTES_FAIL_PATH'];
	if(file_exists($dir.$file)){
		if(unlink($dir.$file)){
			return new JsonResponse(["result"=>1]);
		}
	}
	return new JsonResponse(["result"=>-1]);
}

/**
* @Route("/api/ERP/salesorders/signeddeliverynotesfails/save/{deliverynote}/{file}", name="signeddeliverynotesfails_save")
* Mueve el fichero escaneado a su ubicacion definitiva
*/
public function signeddeliverynotesfails_save($deliverynote, $file, RouterInterface $router,Request $request){
	// El usuario tiene derechos para realizar la acción, sino se va a la página de unauthorized
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine()))
		return $this->redirect($this->generateUrl('unauthorized'));

	$dir=$_ENV['SIGNEDDELIVERYNOTES_FAIL_PATH'];
	$final_dir=$_ENV['SIGNEDDELIVERYNOTES_PATH'];

	$deliverynote=trim($deliverynote);
	$deliverynote=strtoupper($deliverynote);

	//Comprobamos que exista el fichero en la carpeta de fallos
	if(!file_exists($dir.$file)) return new JsonResponse(["result"=>-1]);
	//Obtenemos la fecha del documento y comprobamos que exista
	$url='http://192.168.1.250:9000/navisionExport/axiom/do-NAVISION-getDeliveryNoteDate.php?deliverynote='.$deliverynote;
	$content=file_get_contents($url);
	$json=json_decode($content, true);
	if($json===null) return new JsonResponse(["result"=>-2]);
	$date=\DateTime::createFromFormat('Y-m-d H:i:s.u', $json["date"]["date"]);
	if(!$date) return new JsonResponse(["result"=>-3]);
  $final_dir.=$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;
	$newname=$date->format('Y').'-'.$date->format('m').'-'.$date->format('d').' - '.$deliverynote.'.pdf';
	//Si el fichero de destino existe, lo anexamos
	if(file_exists($final_dir.$newname)){
		rename($final_dir.$newname, $final_dir.'temp_'.$newname);
		$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=\"".$final_dir.$newname."\" \"".$final_dir.'temp_'.$newname."\" \"".$dir.$file."\"";
		$result = shell_exec($cmd);
		unlink($final_dir.'temp_'.$newname);
		if(unlink($dir.$file)){
			return new JsonResponse(["result"=>1]);
		}else return new JsonResponse(["result"=>-5]);
	}else{
		//Si no existe en destino, movemos el fichero
		if(rename($dir.$file, $final_dir.$newname)){
				return new JsonResponse(["result"=>1]);
		}else return new JsonResponse(["result"=>-6]);
	}
	return new JsonResponse(["result"=>-4]);
}


}
