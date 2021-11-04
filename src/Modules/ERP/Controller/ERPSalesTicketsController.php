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
use App\Modules\HR\Entity\HRDepartments;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPSalesOrders;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPSalesTicketsStates;
use App\Modules\ERP\Entity\ERPSalesTicketsReasons;
use App\Modules\ERP\Entity\ERPSalesTicketsHistory;
use App\Modules\ERP\Entity\ERPStoreTickets;
use App\Modules\ERP\Entity\ERPStoreTicketsHistory;
use App\Modules\ERP\Entity\ERPStoreTicketsStates;
use App\Modules\ERP\Entity\ERPStoreTicketsReasons;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSalesTicketsUtils;
use App\Modules\Security\Utils\SecurityUtils;


class ERPSalesTicketsController extends Controller
{
	private $class=ERPSalesTickets::class;
	private $utilsClass=ERPSalesTicketsUtils::class;
	private $module='ERP';


	/**
	* @Route("/{_locale}/ERP/salestickets/form/{id}", name="formSalesTickets", defaults={"id"=0})
	*/
	public function formSalesTickets($id, RouterInterface $router, Request $request)
	{

		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
		$SalesTicketsHistoryRepository = $this->getDoctrine()->getRepository(ERPSalesTicketsHistory::class);
		$salesticketsstatesRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsStates::class);
		$salesticketsreasonsRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsReasons::class);
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$departmentsRepository=$this->getDoctrine()->getRepository(HRDepartments::class);
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
			$newid=$salesticketsRepository->getLastID()+1;
			if($newid<10) $code="#V".date("Y")."0000".$newid;
			else if($newid<100) $code="#V".date("Y")."000".$newid;
			else if($newid<1000) $code="#V".date("Y")."00".$newid;
			else if($newid<10000) $code="#V".date("Y")."0".$newid;
		}


		$salesticket=null;
		$products=null;
		$stores_to_check=null;
		if($id!=0){
			$salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			$salesticket->setObservations("");
			//si es una incidencia por fallo de stock decodificamos el json con el listado de productos y almacenes para el Inventario
			if($salesticket->getReason()->getId()=="3")
			{
				$products=json_decode($salesticket->getProducts());
				if($salesticket->getStores()!=null) $stores_to_check=json_decode($salesticket->getStores());
			}

		}

		if($salesticket==null){
			$salesticket=new $this->class();
		}

		//Search Customers
		$classCustomersUtils="\App\Modules\ERP\Utils\ERPCustomersUtils";
		$customersutils = new $classCustomersUtils();
		$customerslist=$customersutils->formatListWithCode($this->getUser());
		$customerslist["fieldButtons"]=[["id"=>"select", "type" => "success", "default"=>true, "icon" => "fas fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$customerslist["topButtons"]=[];
		$customerslist["multiselect"]=false;

		//Search Sales Orders
		$classSalesOrdersUtils="\App\Modules\ERP\Utils\ERPSalesOrdersUtils";
		$salesordersutils = new $classSalesOrdersUtils();
		$salesorderslist=$salesordersutils->formatListWithNumber($this->getUser());

		$salesorderslist["fieldButtons"]=[["id"=>"select", "type" => "success", "default"=>true, "icon" => "fas fa-plus", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$salesorderslist["topButtons"]=[];
		$salesorderslist["multiselect"]=false;


		//sales ticket states
		$objects=$salesticketsstatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$states=[];
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$states[]=$option;
		}

		//sales ticket reasons
		$objects=$salesticketsreasonsRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$reasons=[];
		foreach($objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$reasons[]=$option;
		}

		//agents
		$agent_objects=$agentsRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$agents=[];
		$option=null;
		$option["id"]=null;
		$option["text"]="Elige agente...";
		$agents[]=$option;
		foreach($agent_objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName()." ".$item->getLastname();
			$agents[]=$option;
		}

		//departments
		$department_objects=$departmentsRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
		$departments=[];
		$option=null;
		$option["id"]=null;
		$option["text"]="Elige departamento...";
		$departments[]=$option;
		foreach($department_objects as $item){
			$option["id"]=$item->getId();
			$option["text"]=$item->getName();
			$departments[]=$option;
		}

		//stores
		$store_objects=$storesRepository->getInventoryStores();
		$inventory_stores=[];
		foreach($store_objects as $item){
			$inventory_stores[]=$item;
		}

		//defaul store
		$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
		$storeUser=$StoreUsersrepository->findOneBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0, "preferential"=>1]);
		$default_store=$storeUser->getStore()->getCode();


		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','SalesTickets');
		array_push($breadcrumb,$new_breadcrumb);

		$histories=$SalesTicketsHistoryRepository->findBy(["salesticket"=>$salesticket,"active"=>1,"deleted"=>0],["dateadd"=>"DESC"]);
		foreach($histories as $key=>$item){
		 	$histories[$key]=$item;
		}

		$gallery=[];
		$gallery["name"]="salesticketImage";
		$gallery["cols"]=3;
		$gallery["type"]="gallery";
		$gallery["imageType"]="salestickets";
		$gallery["value"]="getImage";
		$gallery["width"]="100%";
		$gallery["height"]="300px";

		$info=null;
		if($id==0){
			$info[]="Elige el motivo de la incidencia";
		}
		else if($salesticket->getSalesticketstate()->getName()!="Solucionada")	$info[]="Si necesitas ampliar los detalles de la incidencia, puedes hacerlo pinchando en el botón 'Añadir información'. También puedes añadir imágenes si lo necesitas.";


		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@ERP/salestickets.html.twig', [
				'moduleConfig' => $config,
				'controllerName' => 'categoriesController',
				'interfaceName' => 'SalesTickets',
				'optionSelected' => 'genericindex',
				'optionSelectedParams' => ["module"=>"ERP", "name"=>"SalesTickets"],
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $breadcrumb,
				'userData' => $userdata,
				'customerslist' => $customerslist,
				'salesorderslist' => $salesorderslist,
				'default_store' => $default_store,
				'inventory_stores' => $inventory_stores,
				'stores_to_check' => $stores_to_check,
				'states' => $states,
				'reasons' => $reasons,
				'agents' => $agents,
				'departments' => $departments,
				'products' => $products,
				'ticketType' => 'sales_ticket',
				'salesticket' => $salesticket,
				'histories'=> $histories,
				'gallery' => $gallery,
				'id' => $id,
				'code' => $code,
				'info' => $info
			]);
		}
		return new RedirectResponse($this->router->generate('app_login'));


	}


	/**
	* @Route("/{_locale}/ERP/salestickets/data/{id}/{action}", name="dataSalesTickets", defaults={"id"=0, "action"="read"})
	*/
	public function dataSalesTickets($id, $action, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
		$storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
		$salesordersRepository=$this->getDoctrine()->getRepository(ERPSalesOrders::class);
		$customersRepository=$this->getDoctrine()->getRepository(ERPCustomers::class);
		$salesticketsstatesRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsStates::class);
		$salesticketsreasonsRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsReasons::class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$departmentsRepository=$this->getDoctrine()->getRepository(HRDepartments::class);

		//Get content of the json reques
		$fields=json_decode($request->getContent());

		$salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		$customer=$customersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->customercode, "active"=>1, "deleted"=>0]);
		$salesticketstate=$salesticketsstatesRepository->findOneBy(["id"=>$fields->salesticketstate, "active"=>1, "deleted"=>0]);
		$products=null;
		$stores=null;

		$newid=$salesticketsRepository->getLastID()+1;
		if(!$salesticket){
			$salesticket=new ERPSalesTickets();
			$salesticketreason=$salesticketsreasonsRepository->findOneBy(["id"=>$fields->salesticketreason, "active"=>1, "deleted"=>0]);
			$salesticket->setReason($salesticketreason);
			$salesticket->setActive(1);
			$salesticket->setDeleted(0);
			$salesticket->setDateadd(new \DateTime());
			if($newid<10) $salesticket->setCode("#V".date("Y")."0000".$newid);
			else if($newid<100) $salesticket->setCode("#V".date("Y")."000".$newid);
			else if($newid<1000) $salesticket->setCode("#V".date("Y")."00".$newid);
			else if($newid<10000) $salesticket->setCode("#V".date("Y")."0".$newid);

		}


		if($id==0){
			$salesticket->setAuthor($this->getUser());
			if($fields->products!="")
			{
				$products=explode(",",$fields->products);
				$salesticket->setProducts(json_encode($products));
			}
			if($fields->stores!="")
			{
				$stores=explode(",",$fields->stores);
				$salesticket->setStores(json_encode($stores));
			}

		}

		if($fields->salesticketnewagent!=""){

			$newagent=$agentsRepository->findOneBy(["id"=>$fields->salesticketnewagent,"active"=>1,"deleted"=>0]);
			$salesticket->setAgent($newagent);
			$salesticket->setDepartment(null);
		}
		else if($fields->salesticketnewdepartment!=""){
			$newdepartment=$departmentsRepository->findOneBy(["id"=>$fields->salesticketnewdepartment,"active"=>1,"deleted"=>0]);
			$salesticket->setAgent(null);
			$salesticket->setDepartment($newdepartment);
		}
		else {
			$salesticket->setAgent($this->getUser());
			$salesticket->setDepartment(null);

		}

		$salesticket->setCompany($this->getUser()->getCompany());
		$salesticket->setCustomer($customer);
		$salesticket->setCustomername($fields->customername);
		$salesorder=null;
		$salesorder_agent=null;
		if($fields->salesordernumber!="")
		{
			$salesorder=$salesordersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->salesordernumber, "active"=>1, "deleted"=>0]);
			$salesorder_agent=$agentsRepository->findOneBy(["id"=>$salesorder->getAgent()->getId(),"active"=>1,"deleted"=>0]);
			$salesticket->setSalesOrder($salesorder);

		}
		$salesticket->setSalesordernumber($fields->salesordernumber);
		$salesticket->setExternalsalesordernumber($fields->externalsalesordernumber);
		$salesticket->setSalesticketstate($salesticketstate);
		$salesticket->setEmail($fields->email);
		$salesticket->setObservations(str_replace("\n", '<br>', $fields->observations));
		$salesticket->setDateupd(new \DateTime());
		$salesticket->setDatelastnotify(new \DateTime());
		$this->getDoctrine()->getManager()->persist($salesticket);

		$newagent=null;
		$newdepartment=null;
		if($fields->salesticketnewagent!=""){
			if($id==0){

				$newagent=$agentsRepository->findOneBy(["id"=>$fields->salesticketnewagent, "active"=>1, "deleted"=>0]);
				$channel=$newagent->getDiscordchannel();
				$msg=$this->getUser()->getName()." ha solicitado que gestiones la incidencia Nº **"."#V".date("Y")."000".$newid."**";
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
				$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$newid;
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

				//si el agente asignado es diferente al agente que hizo el pedido, a este último le llega una notificación de que hay una incidencia en su pedido
				if($salesorder_agent!=null){
					if($salesorder_agent->getId()!=$fields->salesticketnewagent){
						$channel=$salesorder_agent->getDiscordchannel();
						$msg="Se ha producido una incidencia asociada a tu pedido ".$salesorder->getCode();
						file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
						$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$newid;
						file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
					}
				}
			}
			else{
				$salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
				$newagent=$agentsRepository->findOneBy(["id"=>$fields->salesticketnewagent, "active"=>1, "deleted"=>0]);
				$channel=$newagent->getDiscordchannel();
				$msg=$this->getUser()->getName()." ha solicitado que gestiones la incidencia Nº **".$salesticket->getCode()."**";
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
				$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$id;
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

			}
		}

		if($fields->salesticketnewdepartment!=""){

			if($id==0){

				$newdepartment=$departmentsRepository->findOneBy(["id"=>$fields->salesticketnewdepartment,"active"=>1,"deleted"=>0]);
				$channel=$newdepartment->getDiscordchannel();
				$msg=$this->getUser()->getName()." ha solicitado que este departamento gestione la incidencia Nº **"."#V".date("Y")."000".$newid."**";
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
				$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$newid;
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
			}

			else{
				$salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
				$newdepartment=$departmentsRepository->findOneBy(["id"=>$fields->salesticketnewdepartment,"active"=>1,"deleted"=>0]);
				$channel=$newdepartment->getDiscordchannel();
				$msg=$this->getUser()->getName()." ha solicitado que este departamento gestione la incidencia Nº **".$salesticket->getCode()."**";
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
				$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$id;
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

			}



		}

		if($id!=0){
			if($salesticketstate->getName()=="Solucionada"){
				$author=$salesticket->getAuthor();
				if($author->getId()!=$this->getUser()->getId())
				{
					$channel=$author->getDiscordchannel();
					$msg=$this->getUser()->getName()." ha solucionado la incidencia Nº **".$salesticket->getCode()."**";
					file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
					$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$id;
					file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
				}
			}
		}


		$history_obj=new ERPSalesTicketsHistory();
		$history_obj->setAgent($this->getUser());

		if($fields->salesticketnewagent!=""){
			$history_obj->setNewagent($newagent);
			$history_obj->setNewdepartment(null);
		}
		else 	if($fields->salesticketnewdepartment!=""){
			$history_obj->setNewagent(null);
			$history_obj->setNewdepartment($newdepartment);
		}

		else 	$history_obj->setNewagent($this->getUser());


		$history_obj->setSalesTicket($salesticket);
		if($id==0 and $fields->myself=="1") $history_obj->setObservations($fields->observations."<br><p>Yo me encargo de ajustar el stock</p>");
		else 		$history_obj->setObservations(str_replace("\n", '<br>', $fields->observations));
		$history_obj->setSalesticketstate($salesticketstate);
		$history_obj->setActive(1);
		$history_obj->setDeleted(0);
		$history_obj->setDateupd(new \DateTime());
		$history_obj->setDateadd(new \DateTime());

		$this->getDoctrine()->getManager()->persist($history_obj);
		$this->getDoctrine()->getManager()->flush();


		//en el momento de crear la incidencia de ventas por "Fallo de stock", creamos también una incidencia de almacén a no ser que el usuario haya resuelto la incidencia.

		if($id==0){
			if($salesticketreason->getName()=="Fallo de stock" AND $fields->myself!="1"){
				for($i=0;$i<count($products);$i++){
					for($j=0;$j<count($stores);$j++){
						$cont=1;
						$storeticketsstatesRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsStates::class);
						$storeticketsreasonsRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsReasons::class);
						$productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
						$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);

						$newid=$storeticketsRepository->getLastID()+$cont;
						$storeticket=new ERPStoreTickets();
						$storeticketreason=$storeticketsreasonsRepository->findOneBy(["name"=>"Fallo de stock", "active"=>1, "deleted"=>0]);
						$storeticket->setReason($storeticketreason);
						$storeticket->setActive(1);
						$storeticket->setDeleted(0);
						$storeticket->setDateadd(new \DateTime());
						if($newid<10) $storeticket->setCode("#A".date("Y")."0000".$newid);
						else if($newid<100) $storeticket->setCode("#A".date("Y")."000".$newid);
						else if($newid<1000) $storeticket->setCode("#A".date("Y")."00".$newid);
						else if($newid<10000) $storeticket->setCode("#A".date("Y")."0".$newid);
						$storeticket->setAuthor($this->getUser());

						$product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$products[$i], "deleted"=>0]);
						$product_name=$product->getName();
						$storeticket->setProduct($product);

						$store=$storesRepository->findOneBy(["code"=>$stores[$j], "active"=>1, "deleted"=>0]);
						$store_name=$store->getName();
						$storeticket->setStore($store);

						$inventorymanager=$agentsRepository->findOneBy(["id"=>$store->getInventorymanager()->getId(),"active"=>1,"deleted"=>0]);
						$storeticket->setAgent($inventorymanager);
						$storeticket->setCompany($this->getUser()->getCompany());


						$storeticketstate=$storeticketsstatesRepository->findOneBy(["name"=>"Abierta", "active"=>1, "deleted"=>0]);
						$storeticket->setStoreticketstate($storeticketstate);
						$storeticket->setObservations("Incidencia creada automáticamente por fallo de stock del producto ".$product->getName()." para que se revise el stock en ".$store_name);
						$storeticket->setDateupd(new \DateTime());
						$storeticket->setDatelastnotify(new \DateTime());

						$lastsalesticket=$salesticketsRepository->findOneBy(["id"=>$salesticketsRepository->getLastID(),"active"=>1, "deleted"=>0]);
						$storeticket->setSalesTicket($lastsalesticket);
						$this->getDoctrine()->getManager()->persist($storeticket);
						$this->getDoctrine()->getManager()->flush();



						//en el caso de que sólo se genere una incidencia de almacén, pondremos el vínculo a esa incidencia en la incidencia de venta.
						//En caso contrario, al generar varias incidencias de almacén, no podremos poner este vínculo.

						$laststoreticket=$storeticketsRepository->findOneBy(["id"=>$storeticketsRepository->getLastID(),"active"=>1, "deleted"=>0]);

						if(count($products)==1 AND count($stores)==1)
						{
							$lastsalesticket->setStoreTicket($laststoreticket);
							$this->getDoctrine()->getManager()->persist($lastsalesticket);
							$this->getDoctrine()->getManager()->flush();
						}


						$history_obj=new ERPStoreTicketsHistory();
						$history_obj->setAgent($this->getUser());
						$history_obj->setNewagent($inventorymanager);

						$history_obj->setStoreTicket($laststoreticket);

						$history_obj->setObservations("Incidencia creada automáticamente por fallo de stock del producto ".$product_name." para que se revise el stock en ".$store_name);
						$history_obj->setStoreticketstate($storeticketstate);
						$history_obj->setActive(1);
						$history_obj->setDeleted(0);
						$history_obj->setDateupd(new \DateTime());
						$history_obj->setDateadd(new \DateTime());

						$this->getDoctrine()->getManager()->persist($history_obj);
						$this->getDoctrine()->getManager()->flush();
						$cont++;
					}

				}
			}//finaliza la creación de la incidencia de almacén
		}

		return new JsonResponse(["result"=>1,"data"=>["id"=>$salesticket->getId()]]);

	}

	/**
	* @Route("/api/salestickets/list", name="salesticketslist")
	*/
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTickets.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, SalesTickets::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
		return new JsonResponse($return);
	}



	/**
	* @Route("/api/global/salestickets/{id}/get", name="getSalesTickets")
	*/
	public function getSalesTickets($id){
		$salestickets= $this->getDoctrine()->getRepository($this->class)->findOneById($id);
		if (!$salestickets) {
			throw $this->createNotFoundException('No currency found for id '.$id );
		}
		return new JsonResponse($salestickets->encodeJson());
	}


	/**
	* @Route("/{_locale}/ERP/salestickets/{id}/disable", name="disableSalesTickets")
	*/
	public function disable($id)
	{
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}


	/**
	* @Route("/{_locale}/ERP/salestickets/{id}/enable", name="enableSalesTickets")
	*/
	public function enable($id)
	{
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}


	/**
	* @Route("/{_locale}/ERP/salestickets/{id}/delete", name="deleteSalesTickets")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}


	/**
	* @Route("/api/ERP/salestickets/history/get/{id}", name="getSalesTicketHistory", defaults={"id"=0})
	*/
	public function getSalesTicketHistory($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
		$salesticketsHistoryRepository=$this->getDoctrine()->getRepository(ERPSalesTicketsHistory::class);
		$salesticket=$salesticketsRepository->findOneBy(["id"=>$id]);

		$salesticketshistory=$salesticketsHistoryRepository->findBy(["salesticket"=>$salesticket,"active"=>1,"deleted"=>0],["dateadd"=>"DESC"]);
		$response=Array();

		foreach($salesticketshistory as $line){
			$item['dateadd']=$line->getDateadd()->format('H:i:s d/m/Y');
			$item['dateadd2']=$line->getDateadd();
			$item['agentid']=$line->getAgent()->getId();
			$item['agentname']=$line->getAgent()->getName()." ".$line->getAgent()->getLastName();
			$item['newagentname']=$line->getNewagent()->getName()." ".$line->getNewagent()->getLastName();
			$item['newagentid']=$line->getNewagent()->getId();
			$item['newagentname']=$line->getNewagent()->getName()." ".$line->getNewagent()->getLastName();
			if($line->getNewagent())
			{
				$item['newagentid']=$line->getNewagent()->getId();
				$item['newagentname']=$line->getNewagent()->getName()." ".$line->getNewagent()->getLastName();
			}
			if($line->getNewdepartment())
			{
				$item['newdepartmentid']=$line->getNewdepartment()->getId();
				$item['newagentname']=$line->getNewdepartment()->getName()." ".$line->getNewagent()->getLastName();
			}
			$item['observations']=$line->getObservations();
			$item['state']=$line->getSalesticketstate()->getName();
			$response[]=$item;
		}
		return new JsonResponse(["history"=>$response]);

	}


}
