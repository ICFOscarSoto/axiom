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
use App\Modules\ERP\Utils\ERPPurchasesOrdersUtils;
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
use App\Modules\ERP\Reports\ERPPurchasesOrdersReports;
use App\Modules\Security\Utils\SecurityUtils;

class ERPPurchasesOrdersController extends Controller
{
	private $module='ERP';
	private $prefix='PED';
	private $class=ERPPurchasesOrders::class;
	private $classLines=ERPPurchasesOrdersLines::class;
	private $utilsClass=ERPPurchasesOrdersUtils::class;

	/**
	 * @Route("/{_locale}/ERP/purchasesorders/form/{id}", name="ERPPurchasesOrdersForm", defaults={"id"=0}))
	 */
	public function ERPPurchasesOrdersForm($id, RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$customerGroupsrepository=$this->getDoctrine()->getRepository(ERPCustomerGroups::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(ERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(ERPSeries::class);
		$documentRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrdersLines::class);

		if($request->query->get('code',null)){
			$obj = $documentRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);

		//Search Suppliers
		$classSuppliersUtils="\App\Modules\ERP\Utils\ERPSuppliersUtils";
		$suppliersutils = new $classSuppliersUtils();
		$supplierslist=$suppliersutils->formatList($this->getUser());
		$supplierslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$supplierslist["topButtons"]=[];

		//Search Products
		$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

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
			$documentLines=$documentLinesRepository->findBy(["purchasesorder"=>$document, "active"=>1,"deleted"=>0]);
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

		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','PurchasesOrders');
		array_push($breadcrumb,$new_breadcrumb);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/purchasesorders.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'categoriesController',
				'interfaceName' => 'PurchasesOrders',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"ERP", "name"=>"PurchasesOrders"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'supplierslist' => $supplierslist,
				'productslist' => $productslist,
				'paymentMethods' => $paymentMethods,
				'series' => $series,
				'date' => ($document->getId()==null)?date('d-m-Y'):$document->getDate()->format('d/m/Y'),
				'enddate' => ($document->getId()==null)?date('d-m-Y', strtotime(date('d-m-Y'). ' + '.$config->getBudgetexpiration().' '.$config->getBudgetexpirationtype())):$document->getDateofferend()->format('d/m/Y'),
				'id' => $id,
				'documentType' => 'purchases_order',
				'documentPrefix' => $this->prefix,
				'document' => $document,
				'documentLines' => $documentLines,
				'documentReadonly' => $document->getPurchasesbudget()!=null?true:false,

				'errors' => $errors,
				'warnings' => $warnings,
				'token' => uniqid('sign_').time()
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}


	/**
	 * @Route("/{_locale}/ERP/PurchasesOrders/data/{id}", name="dataERPPurchasesOrders", defaults={"id"=0}))
	 */
	public function data($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrdersLines::class);
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
			$document=new ERPPurchasesOrders();
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
			$line=$documentLinesRepository->findOneBy(["purchasesbudget"=>$document, "id"=>$value->id]);
			$product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$value->productid, "active"=>1, "deleted"=>0]);
			//if(!$product) continue;
			if($value->code=="") continue;
			if(!$line && $value->deleted) continue;
			if(!$line ){
				$line=new ERPPurchasesOrdersLines();
				$line->setPurchasesbudget($document);
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
	 * @Route("/{_locale}/ERP/PurchasesOrders/document/{id}/{mode}", name="ERPPurchasesOrdersPrint", defaults={"id"=0, "mode"="print"}))
	 */
	public function ERPPurchasesOrdersPrint($id, $mode, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository($this->class);
		$documentLinesRepository=$this->getDoctrine()->getRepository($this->classLines);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$id, "deleted"=>0]);
		if(!$document) return new JsonResponse(["result"=>-1]);
		$lines=$documentLinesRepository->findBy(["purchasesorder"=>$document, "deleted"=>0, "active"=>1]);
		$configuration=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$document->getId(), "user"=>$this->getUser(), "document"=>$document, "lines"=>$lines, "configuration"=>$configuration];
		$reportsUtils = new ERPPurchasesOrdersReports();
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
	 * @Route("/{_locale}/ERP/PurchasesOrders/createorder/{id}", name="ERPOrderFromPurchasesOrders", defaults={"id"=0}))
	 */
	public function ERPOrderFromPurchasesOrders($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$documentRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		$documentLinesRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrdersLines::class);
		$orderRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		$orderLinesRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrdersLines::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);

		$document=$documentRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		if(!$document) return new Response("");
		$lines=$documentLinesRepository->findBy(["purchasesbudget"=>$document, "deleted"=>0, "active"=>1]);
		$configuration=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);
		if($document->getInPurchasesOrder()!=null) return new RedirectResponse($this->generateUrl('ERPPurchasesOrdersForm',["id"=>$document->getInPurchasesOrder()->getId()]));
		//Create the order
		$order=new ERPPurchasesOrders();
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

		$document->setInPurchasesOrder($order);
		$this->getDoctrine()->getManager()->persist($document);
		$this->getDoctrine()->getManager()->flush();
		//Create the lines
		$linenum=1;
		foreach ($lines as $key => $line) {
			$orderLine=new ERPPurchasesOrdersLines();
			$orderLine->setPurchasesorder($order);
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

		return new RedirectResponse($this->generateUrl('ERPPurchasesOrdersForm',["id"=>$order->getId()]));
	}

	/**
	 * @Route("/api/ERP/PurchasesOrders/findproduct/{productid}", name="ERPFindProductInPurchasesOrders", defaults={"productid"=0})
	 */
	public function ERPFindProductInPurchasesOrders($productid, RouterInterface $router,Request $request){
		$purchasesordersRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		$purchasesorder=null;
		$purchasesorder=$purchasesordersRepository->getPurchaseOrderByProduct($productid);
		$item['id']=$purchasesorder['id'];
		$item['code']=$purchasesorder['code'];
		return new JsonResponse(["result"=>$item]);

	}

	/**
	 * @Route("/api/ERP/PurchasesOrders/findpurchaseorder/{productid}", name="ERPFindPurchasesOrderBySupplier", defaults={"productid"=0})
	 */
	public function ERPFindPurchasesOrderBySupplier($productid, RouterInterface $router,Request $request){
		$productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$productid, "active"=>1, "deleted"=>0]);
		$supplierid=$product->getSupplier()->getId();
		$purchasesordersRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		$purchasesorder=null;
		$purchasesorder=$purchasesordersRepository->getPurchaseOrderBySupplier($supplierid);
		$item['id']=$purchasesorder['id'];
		$item['code']=$purchasesorder['code'];
		return new JsonResponse(["result"=>$item]);

	}

	/**
	 * @Route("/api/ERP/PurchasesOrders/addproduct/{productid}/{quantity}/{id}", name="ERPAddProductToPurchaseOrder", defaults={"productid"=0, "id"=0})
	 */
	public function ERPAddProductToPurchaseOrder($productid, $quantity, $id, RouterInterface $router,Request $request){
     $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $purchasesordersRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrders::class);
		 $order=$purchasesordersRepository->findOneBy(["id"=>$id]);
		 $product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$productid, "active"=>1, "deleted"=>0]);
		 $purchasesordersLinesRepository=$this->getDoctrine()->getRepository(ERPPurchasesOrdersLines::class);

			$line=new ERPPurchasesOrdersLines();
			$line->setActive(1);
			$line->setDeleted(0);
			$line->setDateadd(new \DateTime());
			$line->setPurchasesorder($order);
			$line->setLinenum($purchasesordersLinesRepository->getLastLine($id)+1);
			$line->setProduct($product);
			$line->setCode($product->getCode());
			$line->setName($product->getName());
			$line->setUnitprice(floatval($product->getShoppingPrice()));
			$line->setQuantity($quantity);
		//	$line->setDtoperc();
		//	$line->setDtounit();
		//	$line->setTaxperc();
	//		$line->setTaxunit(floatval($value->taxunit));
	//		$line->setIrpfperc(floatval($value->irpfperc));
	//		$line->setIrpfunit(floatval($value->irpfunit));
	//		$line->setSurchargeperc(floatval($value->surchargeperc));
	//		$line->setSurchargeunit(floatval($value->surchargeunit));
	//		$line->setSubtotal(floatval($value->subtotal));
      //$line->setTotal(floatval($value->total));
			$line->setDateupd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($line);
			$this->getDoctrine()->getManager()->flush();
			return new JsonResponse(["result"=>1]);

	}

}
