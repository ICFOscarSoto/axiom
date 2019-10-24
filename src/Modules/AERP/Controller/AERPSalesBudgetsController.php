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
use App\Modules\AERP\Entity\AERPPaymentMethods;
use App\Modules\AERP\Entity\AERPSeries;
use App\Modules\AERP\Entity\AERPCustomerGroups;
use App\Modules\AERP\Entity\AERPSalesBudgets;
use App\Modules\AERP\Entity\AERPSalesBudgetsLines;
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
		$customerGroupsrepository=$this->getDoctrine()->getRepository(AERPCustomerGroups::class);
		$paymentMethodsrepository=$this->getDoctrine()->getRepository(AERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(AERPSeries::class);

		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;

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

		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','AERP','SalesBudgets');
		array_push($breadcrumb,$new_breadcrumb);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@AERP/salesbudgets.html.twig', [
				'controllerName' => 'categoriesController',
				'interfaceName' => 'Sales Budgets',
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
				'date' => date('d-m-Y'),
				'enddate' => date('d-m-Y', strtotime(date('d-m-Y'). ' + 30 days')),
				'id' => $id
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
		$customersRepository=$this->getDoctrine()->getRepository(AERPCustomers::class);
		$paymentMethodsRepository=$this->getDoctrine()->getRepository(AERPPaymentMethods::class);
		$seriesRepository=$this->getDoctrine()->getRepository(AERPSeries::class);
		$taxesRepository=$this->getDoctrine()->getRepository(GlobaleTaxes::class);

		$fields=json_decode($request->getContent());
		$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->customerid]);
		if(!$customer) JsonResponse(["result"=>0]);

		$paymentmethod=$paymentMethodsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->paymentmethod]);
		$serie=$seriesRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$fields->serie]);

		$document=new AERPSalesBudgets();
		$document->setCompany($this->getUser()->getCompany());
		$document->setAuthor($this->getUser());
		$document->setAgent($this->getUser());
		$document->setNumber($documentRepository->getNextNum($this->getUser()->getCompany()->getId()));
		$document->setCurrency(null);
		$document->setCode($code='PRE-'.'-'.str_pad($document->getNumber(), 8, '0', STR_PAD_LEFT));
		$document->setPaymentmethod($paymentmethod);
		$document->setSerie($serie);
		$document->setCustomer($customer);
		$document->setVat($customer->getVat());
		$document->setCustomername($customer->getName());
		$document->setCustomeraddress($customer->getAddress());
		$document->setCustomercountry($customer->getCountry());
		$document->setCustomercity($customer->getCity());
		$document->setCustomerstate($customer->getState());
		$document->setCustomerpostcode($customer->getPostcode());
		$document->setCustomerpostbox($customer->getPostbox());
		$document->setCustomercode($customer->getCode());

		$document->setDate($fields->date?date_create_from_format("d/m/Y",$fields->date):null);
		$document->setDateofferend($fields->dateofferend?date_create_from_format("d/m/Y",$fields->dateofferend):null);

		//if($paymentmethod)


		foreach ($fields->lines as $key => $value) {

		}
		dump($document);
		return new JsonResponse(["result"=>1]);
	}

}
