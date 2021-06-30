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
use App\Modules\ERP\Entity\ERPStoreTickets;
use App\Modules\ERP\Entity\ERPStoreTicketsHistory;
use App\Modules\ERP\Entity\ERPStoreTicketsStates;
use App\Modules\ERP\Entity\ERPStoreTicketsReasons;
use App\Modules\ERP\Entity\ERPSalesTickets;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPVariantsValues;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPStoreTicketsUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPStoreTicketsController extends Controller
{
		private $class=ERPStoreTickets::class;
		private $utilsClass=ERPStoreTicketsUtils::class;
		private $module='ERP';


		/**
		 * @Route("/{_locale}/ERP/storetickets/form/{id}", name="formStoreTickets", defaults={"id"=0})
		 */
		 public function formStoreTickets($id, RouterInterface $router, Request $request)
		 {

			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			 $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			 $configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		   $storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
			 $storeTicketsHistoryRepository = $this->getDoctrine()->getRepository(ERPStoreTicketsHistory::class);
			 $storeticketsstatesRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsStates::class);
			 $storeticketsreasonsRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsReasons::class);
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
				$newid=$storeticketsRepository->getLastID()+1;
				if($newid<10) $code="#A".date("Y")."0000".$newid;
				else if($newid<100) $code="#A".date("Y")."000".$newid;
				else if($newid<1000) $code="#A".date("Y")."00".$newid;
				else if($newid<10000) $code="#A".date("Y")."0".$newid;
			}


			$storeticket=null;
			if($id!=0){
				$storeticket=$storeticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
				$storeticket->setObservations("");
			}
			if($storeticket==null){
				$storeticket=new $this->class();
			}


			//Search Products
			$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
			$productsutils = new $classProductsUtils();
			$productslist=$productsutils->formatList($this->getUser());
			$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-plus-circle", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
			$productslist["topButtons"]=[];


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

			//stores for inventory
			$inventory_store_objects=$storesRepository->getInventoryStores();
			$inventory_stores=[];
			foreach($inventory_store_objects as $item){
				$inventory_stores[]=$item;
			}

			//defaul store
			$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$storeUser=$StoreUsersrepository->findOneBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0, "preferential"=>1]);
			$default_store=$storeUser->getStore()->getCode();

			//store ticket states
			$objects=$storeticketsstatesRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
			$states=[];
			foreach($objects as $item){
				$option["id"]=$item->getId();
				$option["text"]=$item->getName();
				$states[]=$option;
			}

			//store ticket reasons
			$objects=$storeticketsreasonsRepository->findBy(["active"=>1,"deleted"=>0],["name"=>"ASC"]);
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

			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','StoreTickets');
			array_push($breadcrumb,$new_breadcrumb);

			$histories=$storeTicketsHistoryRepository->findBy(["storeticket"=>$storeticket,"active"=>1,"deleted"=>0],["dateadd"=>"DESC"]);
			foreach($histories as $key=>$item){
				$histories[$key]=$item;
			}

			 $gallery=[];
			 $gallery["name"]="storeticketImage";
			 $gallery["cols"]=3;
			 $gallery["type"]="gallery";
			 $gallery["imageType"]="storetickets";
			 $gallery["value"]="getImage";
			 $gallery["width"]="100%";
			 $gallery["height"]="300px";

			 $info=null;
			 if($id==0){
				 $info[]="Elige el motivo de la incidencia";
			 }
			 else if($storeticket->getStoreticketstate()->getName()!="Solucionada" AND $storeticket->getStoreticketstate()->getId()!="1")	$info[]="Para completar los detalles de la incidencia, tienes que pinchar en el botón 'Añadir información'. También puedes añadir imágenes si lo necesitas.";


			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
					return $this->render('@ERP/storetickets.html.twig', [
						'moduleConfig' => $config,
						'controllerName' => 'categoriesController',
						'interfaceName' => 'StoreTickets',
						'optionSelected' => 'genericindex',
						'optionSelectedParams' => ["module"=>"ERP", "name"=>"StoreTickets"],
						'menuOptions' =>  $menurepository->formatOptions($userdata),
						'breadcrumb' =>  $breadcrumb,
						'userData' => $userdata,
						'productslist' => $productslist,
						'stores' => $stores,
						'default_store' => $default_store,
						'inventory_stores' => $inventory_stores,
						'states' => $states,
						'reasons' => $reasons,
						'agents' => $agents,
						'departments' => $departments,
						'ticketType' => 'store_ticket',
						'storeticket' => $storeticket,
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
			* @Route("/{_locale}/ERP/storetickets/data/{id}/{action}", name="dataStoreTickets", defaults={"id"=0, "action"="read"})
			*/
			public function dataStoreTickets($id, $action, Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
			 $salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
			 $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			 $variantsRepository=$this->getDoctrine()->getRepository(ERPVariantsValues::class);
			 $storeticketsstatesRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsStates::class);
			 $storeticketsreasonsRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsReasons::class);
			 $storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
			 $storeLocationsRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
			 $configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
			 $agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
			 $departmentsRepository=$this->getDoctrine()->getRepository(HRDepartments::class);

			 $storeticket=$storeticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);

			 //Get content of the json reques
			 $fields=json_decode($request->getContent());
			 $product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$fields->productcode, "deleted"=>0]);
			 $product_name=$product->getName();
			 if($id==0 AND $fields->storeticketreason=="1")  $storeticketstate=$storeticketsstatesRepository->findOneBy(["id"=>"1", "active"=>1, "deleted"=>0]);
			 else $storeticketstate=$storeticketsstatesRepository->findOneBy(["id"=>$fields->storeticketstate, "active"=>1, "deleted"=>0]);
			 $stores=null;
			 $stores=explode(",",$fields->stores);
			 $variants=null;
			 $variants=explode(",",$fields->variants);

			 if($fields->stores!=""){
				 	//incidencias por fallo de stock que se manden a hacer inventario (variantes de producto)
					if($fields->variants!=""){
					 			for($i=0;$i<count($stores);$i++)
								{
									for($j=0;$j<count($variants);$j++)
									{
										$store=$storesRepository->findOneBy(["code"=>$stores[$i]]);
										if($storeticketsRepository->notPendingInventory($product->getId(),$store->getId(),$variants[$j]))
										{
												$cont=1;
												$newid=$storeticketsRepository->getLastID()+$cont;

												$storeticket=new ERPStoreTickets();
												$storeticketreason=$storeticketsreasonsRepository->findOneBy(["id"=>$fields->storeticketreason, "active"=>1, "deleted"=>0]);
												$storeticket->setReason($storeticketreason);
												$storeticket->setActive(1);
												$storeticket->setDeleted(0);
												$storeticket->setDateadd(new \DateTime());
												if($newid<10) $storeticket->setCode("#A".date("Y")."0000".$newid);
												else if($newid<100) $storeticket->setCode("#A".date("Y")."000".$newid);
												else if($newid<1000) $storeticket->setCode("#A".date("Y")."00".$newid);
												else if($newid<10000) $storeticket->setCode("#A".date("Y")."0".$newid);
												$storeticket->setAuthor($this->getUser());

												$storeticket->setDepartment(null);
												$storeticket->setCompany($this->getUser()->getCompany());
												$storeticket->setProduct($product);
												$variant=$variantsRepository->findOneBy(["id"=>$variants[$j]]);
												$storeticket->setVariant($variant);

												$storeticket->setStore($store);

												$inventorymanager=$inventorymanager=$store->getInventorymanager();
												$storeticket->setAgent($inventorymanager);
												$storeticket->setObservations("Hacer inventario de la variante ".$variant->getName()." del producto  ".$product_name." en el almacén ".$store->getName());
												$storeticket->setStoreticketstate($storeticketstate);
												$storeticket->setObservations(str_replace("\n", '<br>', $fields->observations));
												$storeticket->setDateupd(new \DateTime());
												$storeticket->setDatelastnotify(new \DateTime());
												$this->getDoctrine()->getManager()->persist($storeticket);

												$history_obj=new ERPStoreTicketsHistory();
												$history_obj->setAgent($this->getUser());


												$inventorymanager=$store->getInventorymanager();
												$history_obj->setNewagent($inventorymanager);
												$history_obj->setNewdepartment(null);
												$history_obj->setStoreTicket($storeticket);
												$history_obj->setObservations("Hacer inventario de la variante ".$variant->getName()." del producto  ".$product_name." en el almacén ".$store->getName());
												$history_obj->setStoreticketstate($storeticketstate);
												$history_obj->setActive(1);
												$history_obj->setDeleted(0);
												$history_obj->setDateupd(new \DateTime());
												$history_obj->setDateadd(new \DateTime());
												$this->getDoctrine()->getManager()->persist($history_obj);
												$this->getDoctrine()->getManager()->flush();

										}

										else return new JsonResponse(["result"=>0]);
									}
								}

							}
						//incidencias por fallo de stock que se manden a hacer inventario (producto simple)
							else{
								for($i=0;$i<count($stores);$i++){
									$store=$storesRepository->findOneBy(["code"=>$stores[$i]]);
									if($storeticketsRepository->notPendingInventory($product->getId(),$store->getId(),null))
									{
											$cont=1;
											$newid=$storeticketsRepository->getLastID()+$cont;

											$storeticket=new ERPStoreTickets();
											$storeticketreason=$storeticketsreasonsRepository->findOneBy(["id"=>$fields->storeticketreason, "active"=>1, "deleted"=>0]);
											$storeticket->setReason($storeticketreason);
											$storeticket->setActive(1);
											$storeticket->setDeleted(0);
											$storeticket->setDateadd(new \DateTime());
											if($newid<10) $storeticket->setCode("#A".date("Y")."0000".$newid);
											else if($newid<100) $storeticket->setCode("#A".date("Y")."000".$newid);
											else if($newid<1000) $storeticket->setCode("#A".date("Y")."00".$newid);
											else if($newid<10000) $storeticket->setCode("#A".date("Y")."0".$newid);
											$storeticket->setAuthor($this->getUser());

											$storeticket->setDepartment(null);
											$storeticket->setCompany($this->getUser()->getCompany());
											$storeticket->setProduct($product);

											$storeticket->setStore($store);

											$inventorymanager=$inventorymanager=$store->getInventorymanager();
											$storeticket->setAgent($inventorymanager);
											$storeticket->setObservations("Hacer inventario del producto  ".$product_name." en el almacén ".$store->getName());
											$storeticket->setStoreticketstate($storeticketstate);
											$storeticket->setObservations(str_replace("\n", '<br>', $fields->observations));
											$storeticket->setDateupd(new \DateTime());
											$storeticket->setDatelastnotify(new \DateTime());
											$this->getDoctrine()->getManager()->persist($storeticket);

											$history_obj=new ERPStoreTicketsHistory();
											$history_obj->setAgent($this->getUser());


											$inventorymanager=$store->getInventorymanager();
											$history_obj->setNewagent($inventorymanager);
											$history_obj->setNewdepartment(null);
											$history_obj->setStoreTicket($storeticket);
											$history_obj->setObservations("Hacer inventario del producto  ".$product_name." en el almacén ".$store->getName());
											$history_obj->setStoreticketstate($storeticketstate);
											$history_obj->setActive(1);
											$history_obj->setDeleted(0);
											$history_obj->setDateupd(new \DateTime());
											$history_obj->setDateadd(new \DateTime());
											$this->getDoctrine()->getManager()->persist($history_obj);
											$this->getDoctrine()->getManager()->flush();

									}

									else return new JsonResponse(["result"=>0]);
								}

							}

							//aparte de mandar hacer inventario, él se encarga de arreglar el stock en su almacén por defecto
							if($fields->myself=="1"){
								$newid=$storeticketsRepository->getLastID()+1;

								$storeticket=new ERPStoreTickets();
								$storeticketreason=$storeticketsreasonsRepository->findOneBy(["id"=>$fields->storeticketreason, "active"=>1, "deleted"=>0]);
								$storeticket->setReason($storeticketreason);
								$storeticket->setActive(1);
								$storeticket->setDeleted(0);
								$storeticket->setDateadd(new \DateTime());
								if($newid<10) $storeticket->setCode("#A".date("Y")."0000".$newid);
								else if($newid<100) $storeticket->setCode("#A".date("Y")."000".$newid);
								else if($newid<1000) $storeticket->setCode("#A".date("Y")."00".$newid);
								else if($newid<10000) $storeticket->setCode("#A".date("Y")."0".$newid);
								$storeticket->setAuthor($this->getUser());

								$storeticket->setDepartment(null);
								$storeticket->setCompany($this->getUser()->getCompany());
								$storeticket->setProduct($product);
								$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
								$storeUser=$StoreUsersrepository->findOneBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0, "preferential"=>1]);
								$default_store=$storeUser->getStore();
								$storeticket->setStore($default_store);


								$storeticket->setAgent($this->getUser());
								$storeticket->setObservations("Stock arreglado para el producto ".$product_name." en el almacén ".$default_store->getName());
								$state_resolved=$storeticketsstatesRepository->findOneBy(["id"=>2, "active"=>1, "deleted"=>0]);
								$storeticket->setStoreticketstate($state_resolved);
								$storeticket->setObservations(str_replace("\n", '<br>', $fields->observations));
								$storeticket->setDateupd(new \DateTime());
								$storeticket->setDatelastnotify(new \DateTime());
								$this->getDoctrine()->getManager()->persist($storeticket);

								$history_obj=new ERPStoreTicketsHistory();
								$history_obj->setAgent($this->getUser());

								$history_obj->setNewagent($this->getUser());
								$history_obj->setNewdepartment(null);
								$history_obj->setStoreTicket($storeticket);
								$history_obj->setObservations("Stock arreglado para el producto ".$product_name." en el almacén ".$default_store->getName());
								$history_obj->setStoreticketstate($state_resolved);
								$history_obj->setActive(1);
								$history_obj->setDeleted(0);
								$history_obj->setDateupd(new \DateTime());
								$history_obj->setDateadd(new \DateTime());
								$this->getDoctrine()->getManager()->persist($history_obj);
								$this->getDoctrine()->getManager()->flush();

							}

			 }

			 //incidencias en las que no se manda a hacer inventario
			 else{
				 //resto de incidencias excepto las de fallo de stock en las que él mismo arregla el stock
				 	if($fields->myself!="1"){

							$newid=$storeticketsRepository->getLastID()+1;
							if(!$storeticket){
								$storeticket=new ERPStoreTickets();
								$storeticketreason=$storeticketsreasonsRepository->findOneBy(["id"=>$fields->storeticketreason, "active"=>1, "deleted"=>0]);
								$storeticket->setReason($storeticketreason);
								$storeticket->setActive(1);
								$storeticket->setDeleted(0);
								$storeticket->setDateadd(new \DateTime());
								if($newid<10) $storeticket->setCode("#A".date("Y")."0000".$newid);
								else if($newid<100) $storeticket->setCode("#A".date("Y")."000".$newid);
								else if($newid<1000) $storeticket->setCode("#A".date("Y")."00".$newid);
								else if($newid<10000) $storeticket->setCode("#A".date("Y")."0".$newid);
							}

							if($id==0){
								$storeticket->setAuthor($this->getUser());
							}

								if($fields->storeticketnewagent!=""){

									$newagent=$agentsRepository->findOneBy(["id"=>$fields->storeticketnewagent,"active"=>1,"deleted"=>0]);
									$storeticket->setAgent($newagent);
									$storeticket->setDepartment(null);
								}
								else if($fields->storeticketnewdepartment!=""){
											$newdepartment=$departmentsRepository->findOneBy(["id"=>$fields->storeticketnewdepartment,"active"=>1,"deleted"=>0]);
											$storeticket->setAgent(null);
											$storeticket->setDepartment($newdepartment);
								}
								else {
									$storeticket->setAgent($this->getUser());
									$storeticket->setDepartment(null);

								}

							$storeticket->setCompany($this->getUser()->getCompany());
							$storeticket->setProduct($product);

							if(isset($fields->productvariant) AND $fields->productvariant!="-1"){
									$variant=$variantsRepository->findOneBy(["id"=>$fields->productvariant]);
									$storeticket->setVariant($variant);
							 }

							 /*
							 En las indicencias por fallo de stock, el almacén lo seleccionamos de un listado en una modal donde sólo aparecen
							 los almacenes en los que se realizan inventarios. En cambio, en el resto en el resto usamos un select con todos los almacenes.
							 Esta distinción hace que el almacen seleccionado lo recojamos en dos campos diferentes "$fields->store" y "$fields->storestockfailed"
							 que en ningún caso ambos serán distintos de null.
							 */

							 if(isset($fields->store) AND $fields->store!="-1" AND $fields->store!=null){
									$store=$storesRepository->findOneBy(["id"=>$fields->store]);
									$storeticket->setStore($store);
								}
								else if(isset($fields->storestockfailed) AND $fields->storestockfailed!=null){
									$store=$storesRepository->findOneBy(["code"=>$fields->storestockfailed]);
									$storeticket->setStore($store);

								}

						 if(isset($fields->storelocation) AND $fields->storelocation!="-1"){
											$storelocation=$storeLocationsRepository->findOneBy(["id"=>$fields->storelocation]);
											$storeticket->setStorelocation($storelocation);
								}

							//para los fallos de stock, ponemos el estado "Abierta" por defecto.


							$storeticket->setStoreticketstate($storeticketstate);
							$storeticket->setObservations(str_replace("\n", '<br>', $fields->observations));
							$storeticket->setDateupd(new \DateTime());
							$storeticket->setDatelastnotify(new \DateTime());
							$this->getDoctrine()->getManager()->persist($storeticket);

							$newagent=null;
							$newdepartment=null;

							//si la incidencia se ha traspasado a otro agente
							if($fields->storeticketnewagent!=""){

								if($id==0){
										if($fields->storeticketreason!="1")
										{
											//si la incidencia se acaba de crear y no es un "fallo de stock" se envia un mensaje por discord al otro agente
											$newagent=$agentsRepository->findOneBy(["id"=>$fields->storeticketnewagent, "active"=>1, "deleted"=>0]);
											$channel=$newagent->getDiscordchannel();
											$msg=$this->getUser()->getName()." ha solicitado que gestiones la incidencia Nº **"."#A".date("Y")."000".$newid."**";
											file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
											$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$newid;
											file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
										}

								 }
								else{

									$storeticket=$storeticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
									$newagent=$agentsRepository->findOneBy(["id"=>$fields->storeticketnewagent, "active"=>1, "deleted"=>0]);
									$channel=$newagent->getDiscordchannel();
									$msg=$this->getUser()->getName()." ha solicitado que gestiones la incidencia Nº **".$storeticket->getCode()."**";
									file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
									$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$id;
									file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

								}
							}

							//si la incidencia se traspasa un departamento
							if($fields->storeticketnewdepartment!=""){

									$newdepartment=$departmentsRepository->findOneBy(["id"=>$fields->storeticketnewdepartment,"active"=>1,"deleted"=>0]);
									$channel=$newdepartment->getDiscordchannel();
									$msg=$this->getUser()->getName()." ha solicitado que este departamento gestione la incidencia Nº **".$storeticket->getCode()."**";
									file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
									$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$id;
									file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

							}

							if($id!=0){
							 if($storeticketstate->getName()=="Solucionada"){
								 //en todas las incidencias que no sean fallo de stock, se comprueba si la persona que la ha solucionada es la misma
								 //que la que la ha creado. De no ser así, se le envía un mensaje por discord.
								 if($fields->storeticketreason!="1")
								 {
									 $author=$storeticket->getAuthor();
									 if($author->getId()!=$this->getUser()->getId())
									 {
										 //si la incidencia la ha solucionado alguien distinto al autor de la misma, se le envía un discord a creador.
										 $channel=$author->getDiscordchannel();
										 $msg=$this->getUser()->getName()." ha solucionado la incidencia Nº **".$storeticket->getCode()."**";
										 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
										 $msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$id;
										 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
									 }
								 }
								 /*en el caso de las incidencias por fallo de stock, primero se comprueba si la incidencia viene de una incidencia de venta para avisar
								 en este caso al creador de la incidencia de venta.*/
								 else{
									 if($storeticketstate->getSalesTicket()!=null){

										 $salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$storeticket->getSalesTicket()->getId(), "active"=>1,"deleted"=>0]);
										 $channel=$salesticket->getAuthor()->getDiscordchannel();
										 $msg=$this->getUser()->getName()." ha hecho el inventario del producto ".$storeticket->getProduct()->getCode()." asociado a la incidencia Nº **".$salesticket->getCode()."**";
										 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
										 $msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salesticket/form/'.$salesticket->getId();
										 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

									 }
									 else{

										 $author=$storeticket->getAuthor();
										 if($author->getId()!=$this->getUser()->getId())
										 {
											 //si la incidencia la ha solucionado alguien distinto al autor de la misma, se le envía un discord a creador.
											 $channel=$author->getDiscordchannel();
											 $msg=$this->getUser()->getName()." ha hecho el inventario del producto ".$storeticket->getProduct()->getCode()." asociado a la incidencia Nº **".$storeticket->getCode()."**";
											 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
											 $msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/storetickets/form/'.$id;
											 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
										 }


									 }

								 }
							 }



						 }

								$history_obj=new ERPStoreTicketsHistory();
								$history_obj->setAgent($this->getUser());

								if($fields->storeticketnewagent!=""){
									$history_obj->setNewagent($newagent);
									$history_obj->setNewdepartment(null);
								}
								else if($fields->storeticketnewdepartment!=""){
									$history_obj->setNewagent(null);
									$history_obj->setNewdepartment($newdepartment);
								}

								else $history_obj->setNewagent($this->getUser());

								$history_obj->setStoreTicket($storeticket);
								$history_obj->setObservations(str_replace("\n", '<br>', $fields->observations));
								$history_obj->setStoreticketstate($storeticketstate);
								$history_obj->setActive(1);
								$history_obj->setDeleted(0);
								$history_obj->setDateupd(new \DateTime());
								$history_obj->setDateadd(new \DateTime());
								$this->getDoctrine()->getManager()->persist($history_obj);
								$this->getDoctrine()->getManager()->flush();


				}
				//incidencias en las que el propio usuario se ha encargado de arreglar el stock
				else{

								$newid=$storeticketsRepository->getLastID()+1;

								$storeticket=new ERPStoreTickets();
								$storeticketreason=$storeticketsreasonsRepository->findOneBy(["id"=>$fields->storeticketreason, "active"=>1, "deleted"=>0]);
								$storeticket->setReason($storeticketreason);
								$storeticket->setActive(1);
								$storeticket->setDeleted(0);
								$storeticket->setDateadd(new \DateTime());
								if($newid<10) $storeticket->setCode("#A".date("Y")."0000".$newid);
								else if($newid<100) $storeticket->setCode("#A".date("Y")."000".$newid);
								else if($newid<1000) $storeticket->setCode("#A".date("Y")."00".$newid);
								else if($newid<10000) $storeticket->setCode("#A".date("Y")."0".$newid);
								$storeticket->setAuthor($this->getUser());

								$storeticket->setDepartment(null);
								$storeticket->setCompany($this->getUser()->getCompany());
								$storeticket->setProduct($product);
								$StoreUsersrepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
								$storeUser=$StoreUsersrepository->findOneBy(["user"=>$this->getUser(), "active"=>1, "deleted"=>0, "preferential"=>1]);
								$default_store=$storeUser->getStore();
								$storeticket->setStore($default_store);


								$storeticket->setAgent($this->getUser());
								$storeticket->setObservations("Stock arreglado para el producto ".$product_name." en el almacén ".$default_store->getName());
								$state_resolved=$storeticketsstatesRepository->findOneBy(["id"=>2, "active"=>1, "deleted"=>0]);
								$storeticket->setStoreticketstate($state_resolved);
								$storeticket->setObservations(str_replace("\n", '<br>', $fields->observations));
								$storeticket->setDateupd(new \DateTime());
								$storeticket->setDatelastnotify(new \DateTime());
								$this->getDoctrine()->getManager()->persist($storeticket);

								$history_obj=new ERPStoreTicketsHistory();
								$history_obj->setAgent($this->getUser());

								$history_obj->setNewagent($this->getUser());
								$history_obj->setNewdepartment(null);
								$history_obj->setStoreTicket($storeticket);
								$history_obj->setObservations("Stock arreglado para el producto ".$product_name." en el almacén ".$default_store->getName());
								$history_obj->setStoreticketstate($state_resolved);
								$history_obj->setActive(1);
								$history_obj->setDeleted(0);
								$history_obj->setDateupd(new \DateTime());
								$history_obj->setDateadd(new \DateTime());
								$this->getDoctrine()->getManager()->persist($history_obj);
								$this->getDoctrine()->getManager()->flush();
				}

			 }


			 return new JsonResponse(["result"=>1,"data"=>["id"=>$storeticket->getId()]]);

		 }


		 /**
	 	 * @Route("/api/storetickets/list", name="storeticketslist")
	 	 */
	 	public function indexlist(RouterInterface $router,Request $request){
	 		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 		$user = $this->getUser();
	 		$locale = $request->getLocale();
	 		$this->router = $router;
	 		$manager = $this->getDoctrine()->getManager();
	 		$repository = $manager->getRepository($this->class);
	 		$listUtils=new GlobaleListUtils();
	 		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StoreTickets.json"),true);
	 		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, StoreTickets::class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
	 		return new JsonResponse($return);
	 	}



	 	/**
	 	* @Route("/api/global/storetickets/{id}/get", name="getStoreTickets")
	 	*/
	 	public function getStoreTickets($id){
	 		$storetickets= $this->getDoctrine()->getRepository($this->class)->findOneById($id);
	 		if (!$storetickets) {
	 					throw $this->createNotFoundException('No currency found for id '.$id );
	 				}
	 	  return new JsonResponse($storetickets->encodeJson());
	 	}

	 /**
	 * @Route("/{_locale}/ERP/storetickets/{id}/disable", name="disableStoreTickets")
	 */
	 public function disable($id)
	  {
	  $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	  $entityUtils=new GlobaleEntityUtils();
	  $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	  return new JsonResponse(array('result' => $result));
	 }


	 /**
	 * @Route("/{_locale}/ERP/storetickets/{id}/enable", name="enableStoreTickets")
	 */
	 public function enable($id)
	  {
	  $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	  $entityUtils=new GlobaleEntityUtils();
	  $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	  return new JsonResponse(array('result' => $result));
	 }


	 /**
	 * @Route("/{_locale}/ERP/storetickets/{id}/delete", name="deleteStoreTickets")
	 */
	 public function delete($id){
	  $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	  $entityUtils=new GlobaleEntityUtils();
	  $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	  return new JsonResponse(array('result' => $result));
	 }


	 /**
	 * @Route("/api/ERP/storetickets/history/get/{id}", name="getStoreTicketHistory", defaults={"id"=0})
	 */
	 public function getStoreTicketHistory($id, RouterInterface $router,Request $request){
			 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			 $storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
			 $storeticketsHistoryRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsHistory::class);
			 $storeticket=$storeticketsRepository->findOneBy(["id"=>$id]);

			 $storeticketshistory=$storeticketsHistoryRepository->findBy(["storeticket"=>$storeticket,"active"=>1,"deleted"=>0],["dateadd"=>"DESC"]);
			 $response=Array();

			 foreach($storeticketshistory as $line){

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
			 	$item['state']=$line->getStoreticketstate()->getName();
			 	$response[]=$item;

			 }

			 return new JsonResponse(["history"=>$response]);

	 }


	 /**
	 * @Route("/{_locale}/ERP/storetickets/solved/{id}", name="storeticketsolved")
	 */
	 public function storeticketsolved($id){
		  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$config=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);
			$storeticket=$storeticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
			$code="";
			$code=$storeticket->getCode();

			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
							 return $this->render('@ERP/storeticket_solving.html.twig', [
								 'moduleConfig' => $config,
								 'interfaceName' => 'storeticketsolved',
								 'optionSelected' => 'storeticketsolved',
								 'menuOptions' =>  $menurepository->formatOptions($userdata),
								 'id' => $id,
								 'storeticket' => $storeticket
								 ]);
						 }
	   return new RedirectResponse($this->router->generate('app_login'));
	 }


	 /**
	 * @Route("/api/ERP/storetickets/setsolved/{id}", name="storeticketsetsolved")
	 */
	 public function storeticketsetsolved($id){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
		 $salesticketsRepository=$this->getDoctrine()->getRepository(ERPSalesTickets::class);
		 $storeticketsstatesRepository=$this->getDoctrine()->getRepository(ERPStoreTicketsStates::class);
		 $storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		 $storeticket=null;
		 $salesticket=null;
		 $storeticket=$storeticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);
		 if($storeticket->getSalesTicket()!=null) $salesticket=$salesticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$storeticket->getSalesTicket()->getId(), "active"=>1,"deleted"=>0]);
		 if($storeticket==null) return new JsonResponse(["result"=>0]);
		 $state=$storeticketsstatesRepository->findOneBy(["id"=>2, "active"=>1,"deleted"=>0]);
		 $storeticket->setStoreticketstate($state);
		 $this->getDoctrine()->getManager()->persist($storeticket);

		 //hay una incidencia de venta asociada, luego hay que avisar al gestor que ha creado dicha incidencia de que ya se ha hecho el inventario.
		 if($salesticket){
			 $channel=$salesticket->getAuthor()->getDiscordchannel();
			 $msg="Ya se ha hecho el inventario del producto **".$storeticket->getProduct()->getName()."** asociado a la incidencia **".$salesticket->getCode()."**";
			 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
			 $msg="\n\nRevisa tu incidencia en: \n".'https://axiom.ferreteriacampollano.com/es/ERP/salestickets/form/'.$salesticket->getId();
			 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

		 }


		 $history_obj=new ERPStoreTicketsHistory();
		 $history_obj->setAgent($storeticket->getAgent());

		 $store=$storesRepository->findOneBy(["code"=>$storeticket->getStore()->getId()]);
		 $history_obj->setNewagent($storeticket->getAgent());
		 $history_obj->setNewdepartment(null);

		 $history_obj->setStoreTicket($storeticket);
		 $history_obj->setObservations("Inventario completado");
		 $history_obj->setStoreticketstate($state);
		 $history_obj->setActive(1);
		 $history_obj->setDeleted(0);
		 $history_obj->setDateupd(new \DateTime());
		 $history_obj->setDateadd(new \DateTime());

		 $this->getDoctrine()->getManager()->persist($history_obj);
		 $this->getDoctrine()->getManager()->flush();

		 return new JsonResponse(["result"=>1]);


	 }


	 /**
	* @Route("/api/ERP/storetickets/checksolved/{id}", name="storeticketchecksolved")
	*/
	public function storeticketchecksolved($id){

		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$storeticketsRepository=$this->getDoctrine()->getRepository(ERPStoreTickets::class);
		$storesRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$storeticket=null;
		$storeticket=$storeticketsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "active"=>1,"deleted"=>0]);

		if($storeticket->getStoreticketstate()->getId()==2) return new JsonResponse(["result"=>1]);
		else return new JsonResponse(["result"=>0]);

	}



}
