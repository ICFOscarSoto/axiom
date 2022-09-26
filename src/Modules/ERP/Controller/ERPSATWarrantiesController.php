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
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPCarriers;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPSATWarranties;
use App\Modules\ERP\Entity\ERPSATWarrantiesStates;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPProviders;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSATWarrantiesUtils;

use App\Modules\Security\Utils\SecurityUtils;

class ERPSATWarrantiesController extends Controller
{
	private $module='ERP';
	private $class=ERPSATWarranties::class;
	private $utilsClass=ERPSATWarrantiesUtils::class;

	/**
	* @Route("/{_locale}/ERP/satwarranties/form/{id}", name="formSATWarranties", defaults={"id"=0})
	*/
	public function formSATWarranties($id, RouterInterface $router, Request $request)
	{

		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$satwarrantiesRepository=$this->getDoctrine()->getRepository(ERPSATWarranties::class);
		$satwarrantiessstatesRepository=$this->getDoctrine()->getRepository(ERPSATWarrantiesStates::class);
		$carriersRepository=$this->getDoctrine()->getRepository(ERPCarriers::class);
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$salesmanagersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);


		if($request->query->get('code',null)){
			$obj = $documentRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

		$code="";
		if($id==0){
			$newid=$satwarrantiesRepository->getLastID()+1;
			if($newid<10) $code="#G".date("Y")."0000".$newid;
			else if($newid<100) $code="#G".date("Y")."000".$newid;
			else if($newid<1000) $code="#G".date("Y")."00".$newid;
			else if($newid<10000) $code="#G".date("Y")."0".$newid;
		}


		$satwarranty=null;
		$products=null;
		if($id!=0) $satwarranty=$satwarrantiesRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);

		if($satwarranty==null){
			$satwarranty=new $this->class();
		}

		//Search Customers
		$classCustomersUtils="\App\Modules\ERP\Utils\ERPCustomersUtils";
		$customersutils = new $classCustomersUtils();
		$customerslist=$customersutils->formatListWithCode($this->getUser());
		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "success", "default"=>true, "icon" => "fas fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$customerslist["topButtons"]=[];
		$customerslist["multiselect"]=false;


		//Search Products
		$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-plus-circle", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

		//SAT waranties states
		$objects=$satwarrantiessstatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$states=[];
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$states[]=$option;
		}

		//carriers
		$carrier_objects=$carriersRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$carriers=[];
		$option=null;
		$option["id"]=null;
		$option["text"]="Elige transportista...";
		$carriers[]=$option;
		foreach($carrier_objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$carriers[]=$option;
		}


		//SALES MANAGER
		$salesmanagers_objects=$salesmanagersRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$salesmanagers=[];
		$option=null;
		$option["id"]=null;
		$option["text"]="Elige gestor de ventas...";
		$salesmanagers[]=$option;
		foreach($salesmanagers_objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName()." ".$item->getLastname();
			$salesmanagers[]=$option;
		}



		//stores
		$store_objects=$storesRepository->findBy(["active"=>1,"deleted"=>0]);
		$stores=[];
		$option=null;
		$option["id"]=null;
		$option["text"]="Selecciona Almacén...";
		$stores[]=$option;
		foreach($store_objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$stores[]=$option;
		}

		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','SalesTickets');
		array_push($breadcrumb,$new_breadcrumb);


		$gallery=[];
		$gallery["name"]="satwarrantyImage";
		$gallery["cols"]=3;
		$gallery["type"]="gallery";
		$gallery["imageType"]="satwarranties";
		$gallery["value"]="getImage";
		$gallery["width"]="100%";
		$gallery["height"]="300px";


		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/satwarranties.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'SATWarrantiesController',
				'interfaceName' => 'SATWarranties',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"ERP", "name"=>"SATWarranties"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'type' => 'satwarranty',
				'customerslist' => $customerslist,
				'states' => $states,
				'carriers' => $carriers,
				'salesmanagers' => $salesmanagers,
				'productslist' => $productslist,
				'stores' => $stores,
				'satwarranty' => $satwarranty,
				'gallery' => $gallery,
				'id' => $id,
				'code' => $code
			]);
		}
		return new RedirectResponse($this->router->generate('app_login'));


	}

	/**
	 * @Route("/{_locale}/ERP/satwarranties/data/{id}/{action}", name="dataSATWarranties", defaults={"id"=0, "action"="read"})
	 */
	 public function dataSATWarranties($id, $action, Request $request){

		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $satwarrantiesRepository=$this->getDoctrine()->getRepository(ERPSATWarranties::class);
		 $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		 $customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		 $variantsRepository=$this->getDoctrine()->getRepository(ERPVariants::class);
		 $satwarrantiessstatesRepository=$this->getDoctrine()->getRepository(ERPSATWarrantiesStates::class);
		 $storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		 $carriersRepository=$this->getDoctrine()->getRepository(ERPCarriers::class);
		 $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
		 $configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		 $salesmanagersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);



		 //Get content of the json reques
		  $fields=json_decode($request->getContent());
		  $product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->productcode, "deleted"=>0]);
		  $product_name=$product->getName();
			$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->customercode, "deleted"=>0]);

			$newid=$satwarrantiesRepository->getLastID()+1;

			$satwarranty=$satwarrantiesRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
			if($satwarranty==null)
			{
				$satwarranty=new ERPSATWarranties();
				$satwarranty->setActive(1);
				$satwarranty->setDeleted(0);
				$satwarranty->setDateadd(new \DateTime());
			}

			if($newid<10) $satwarranty->setRepairnumber("#G".date("Y")."0000".$newid);
			else if($newid<100) $satwarranty->setRepairnumber("#G".date("Y")."000".$newid);
			else if($newid<1000) $satwarranty->setRepairnumber("#G".date("Y")."00".$newid);
			else if($newid<10000) $satwarranty->setRepairnumber("#G".date("Y")."0".$newid);

			$satwarranty->setCompany($this->getUser()->getCompany());
			$satwarranty->setProduct($product);
			$satwarranty->setCustomer($customer);

			$store=$storesRepository->findOneBy(["id"=>$fields->store, "active"=>1, "deleted"=>0]);
			$storelocation=$storeLocationsRepository->findOneBy(["id"=>$fields->storelocation, "active"=>1, "deleted"=>0]);
			$satwarranty->setStore($store);
			$satwarranty->setStoreLocation($storelocation);
			$salesmanager=$salesmanagersRepository->findOneBy(["id"=>$fields->salesmanager, "active"=>1, "deleted"=>0]);

			$satwarranty->setSalesmanager($salesmanager);
			$satwarranty->setDescription($fields->description);
			$satwarrantystate=$satwarrantiessstatesRepository->findOneBy(["id"=>$fields->state, "active"=>1, "deleted"=>0]);

			$carrier=$carriersRepository->findOneBy(["id"=>$fields->carrier, "active"=>1, "deleted"=>0]);
			$satwarranty->setCarrier($carrier);
			$satwarranty->setState($satwarrantystate);
			$satwarranty->setQuantity($fields->quantity);
			$satwarranty->setDateupd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($satwarranty);
			$this->getDoctrine()->getManager()->flush();


		 return new JsonResponse(["result"=>1,"data"=>["id"=>$satwarranty->getId()]]);

	 }

}
