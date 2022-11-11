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
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPInventoryUtils;
use App\Modules\ERP\Utils\ERPInventoryLinesUtils;
use App\Modules\ERP\Entity\ERPInventory;
use App\Modules\ERP\Entity\ERPInventoryLines;
use App\Modules\ERP\Entity\ERPInventoryLocation;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\Security\Utils\SecurityUtils;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Helpers\XLSXWriter\XLSXWriter;

class ERPInventoryController extends Controller
{
	private $class=ERPInventory::class;
	private $module='ERP';
		private $utilsClass=ERPInventoryUtils::class;

	/**
	  * @Route("/api/inventory/{action}/{id}", name="inventoryws", defaults={"action"="info","id"=0})
   */
  public function inventory($action, $id, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		// Parámetros ------------------------------------
		// Acción
		if ($action==null || $action=='')
			$action = 'info';
		// Id de la acción
		if ($id==null || $id=='' || !ctype_digit(strval($id)))
			$id = 0;
		else
			$id = intval($id);
		// Usuario
		$author_id			= $this->getUser();
		// Compañia
		$company_id			= $this->getUser()->getCompany();

		// Repositorios ------------------------------------
		$erpInventoryRepository			= $this->getDoctrine()->getRepository(ERPInventory::class);
		$erpInventoryLinesRepository= $this->getDoctrine()->getRepository(ERPInventoryLines::class);
		$erpInventoryLocationRepository= $this->getDoctrine()->getRepository(ERPInventoryLocation::class);
		$erpStoresRepository				= $this->getDoctrine()->getRepository(ERPStores::class);
		$erpStoreLocationsRepository= $this->getDoctrine()->getRepository(ERPStoreLocations::class);
		$erpProductsVariantsRepository= $this->getDoctrine()->getRepository(ERPProductsVariants::class);
		$erpStocksRepository				= $this->getDoctrine()->getRepository(ERPStocks::class);
		$globaleCompaniesRepository	= $this->getDoctrine()->getRepository(GlobaleCompanies::class);
		$globaleUsersRepository			= $this->getDoctrine()->getRepository(GlobaleUsers::class);

		// Acciones ----------------------------------------
		$return = [];
		switch ($action) {
			// info -> Obtiene la información del inventario pasado como argumento
			case 'info':
				if ($id>0){
					$oinventory	= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
					if ($oinventory!=null){
						$return['result'] = 1;
						$return['data'] 	= $this->getInventoryResult($oinventory);
						$return['text'] 	= "Inventario - Información obtenida correctamente";
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Identificador no existe'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no válido'];
				break;

			// create -> Para el identificador de almacén dado se comprueba si existe un Inventario
			//					 abierto si es así se devuelve este sino se crea
			case 'create':
				// Parámetros adicionales
				$store_id 	= $request->request->get('store_id');
				$datebegin 	= $request->request->get('datebegin');
				$observation= $request->request->get('observation');
				if ($store_id!=null && $store_id!='' && ctype_digit(strval($store_id)) && intval($store_id)>0){
					$ostore 			= $erpStoresRepository->findOneBy(["id"=>$store_id, "active"=>1, "deleted"=>0]);
					if ($ostore!=null){
						$inventory_id	= $erpInventoryRepository->getInventoryByStore($store_id);
						$oinventory		= $erpInventoryRepository->find($inventory_id);
						if ($oinventory==null){
							$ocompany 	= $globaleCompaniesRepository->find($company_id);
							$oauthor 		= $globaleUsersRepository->find($author_id);
							$oinventory	= new ERPInventory();
							$oinventory->setStore($ostore);
							$oinventory->setCompany($ocompany);
							$oinventory->setAuthor($oauthor);
							$oinventory->setDatebegin(new \DateTime());
							$oinventory->setActive(1);
							$oinventory->setDeleted(0);
							$oinventory->setDateadd(new \DateTime());
						}
						if ($datebegin!=null && $datebegin!='')
							$oinventory->setDatebegin(new \DateTime($datebegin));
						if ($observation!=null)
								$oinventory->setObservation($observation);
						$oinventory->setDateupd(new \DateTime());
						$this->getDoctrine()->getManager()->persist($oinventory);
						$this->getDoctrine()->getManager()->flush();
						$return['result'] = 1;
						$return['data'] 	= $this->getInventoryResult($oinventory);
						$return['text'] 	= "Inventario - Creado/actualizado correctamente";
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Creación - Almacén no válido'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Creación - Almacén no válido'];
				break;

			// open -> Devuelve un array con todos los inventarios abiertos
			case 'open':
				$oinventorys	= $erpInventoryRepository->findBy(["dateend"=>null, "active"=>1, "deleted"=>0],['datebegin' => 'DESC']);
				$return['result'] = 1;
				$return['data'] 	= [];
				foreach ($oinventorys as $key => $value) {
					array_push($return['data'],$this->getInventoryResult($value));
				}
				$return['text'] 	= "Inventario - Inventarios abiertos";
				break;

			// all -> Devuelve un array con todos los inventarios
			case 'all':
				$oinventorys	= $erpInventoryRepository->findBy(["active"=>1, "deleted"=>0],['datebegin' => 'DESC']);
				$return['result'] = 1;
				$return['data'] 	= [];
				foreach ($oinventorys as $key => $value) {
					array_push($return['data'],$this->getInventoryResult($value));
				}
				$return['text'] 	= "Inventario - Todos los Inventarios";
				break;

			// lines -> Para el identificador de inventario pasado como argumento
			//					obtiene los productos
			case 'lines':
				// Parámetros adicionales
				$location_id 	= $request->request->get('location_id');
				$location_name= $request->request->get('location_name');
				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
				if ($oinventory!=null){
					// Todos
					if ($location_id==null && $location_name==null){
						$oinventorylines		= $erpInventoryLinesRepository->findBy(["inventory"=>$oinventory, "active"=>1, "deleted"=>0],['dateadd' => 'ASC']);
						$return['result'] = 1;
						$return['data'] 	= [];
						foreach ($oinventorylines as $key => $value) {
							array_push($return['data'],$this->getInventoryLinesResult($value));
						}
						$return['text'] 	= "Inventario - Todos los productos";
					}else{
					// De una ubicación
						$ostorelocation = null;
						if ($location_id)
							$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
						else
							$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);
						if ($ostorelocation){
							$oinventorylines		= $erpInventoryLinesRepository->findBy(["inventory"=>$oinventory, "location"=>$ostorelocation, "active"=>1, "deleted"=>0],['dateadd' => 'ASC']);
							$return['result'] = 1;
							$return['data'] 	= [];
							foreach ($oinventorylines as $key => $value) {
								array_push($return['data'],$this->getInventoryLinesResult($value));
							}
							$return['text'] 	= "Inventario - Productos de la ubicación: ".$ostorelocation->getName();
						}else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicación - Identificador no válido'];
					}
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no válido'];
				break;

			// nolines -> Para el identificador de inventario pasado como argumento
			//					obtiene los productos que no se han inventariado pero que existian en stock
			//					para la ubicación pasada
			case 'nolines':
				// Parámetros adicionales
				$location_id 	= $request->request->get('location_id');
				$location_name= $request->request->get('location_name');
				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
				if ($oinventory!=null){
					if ($location_id)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
					else
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);
					if ($ostorelocation){
						// Todos los productos definidos para la ubicación en stock
						$ostocks					= $erpStocksRepository->getProductByLocation($ostorelocation->getId());
						// Todos los productos inventariados de la ubicación
						$oinventorylines	= $erpInventoryLinesRepository->getInventoryLinesGroup($oinventory->getId(), $ostorelocation->getId());

						$anolines = [];
						foreach($ostocks as $key=>$ostock){
							$existsline = false;
							for($i=0; $i<count($oinventorylines) && !$existsline; $i++){
								if ($ostock['productvariant_id']==$oinventorylines[$i]['productvariant_id'])
									$existsline=true;
							}
							if (!$existsline)
								array_push($anolines, $ostock);
						}

						$return['result'] = 1;
						$return['data'] 	= $anolines;
						$return['text'] 	= "Inventario - Ubicación - Líneas en stock no inventariadas";
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Ubicación no válida'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no válido'];
				break;

			// locations -> Para el identificador de inventario pasado como argumento
			//					obtiene las ubicaciones ya procesadas
			case 'locations':
				// Parámetros adicionales
				$location_id 	= $request->request->get('location_id');
				$location_name= $request->request->get('location_name');
				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
				if ($oinventory!=null){
					// Todas
					if ($location_id==null && $location_name==null){
						$oinventorylocation		= $erpInventoryLocationRepository->findBy(["inventory"=>$oinventory, "active"=>1, "deleted"=>0],['dateadd' => 'ASC']);
						$return['result'] = 1;
						$return['data'] 	= [];
						foreach ($oinventorylocation as $key => $value) {
							array_push($return['data'],$this->getInventoryLocationResult($value));
						}
						$return['text'] 	= "Inventario - Ubicación - Todas las ubicaciones";
					}else{
					// Comprueba si la ubicación es válida para este inventario sino -1 y mensaje
					// Si es válida pero no esta la base de datos de inventarios/ubicaciones se pone 1 pero data vacio
					// Si existe se devuelve en data
						$ostorelocation = null;
						if ($location_id)
							$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
						else
							$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);
						if ($ostorelocation){
							// Comprobar si es una ubicación válida para este inventario
							if ($ostorelocation->getStore()->getId()==$oinventory->getStore()->getId()){
								$oinventorylocation		= $erpInventoryLocationRepository->findOneBy(["inventory"=>$oinventory, "location"=>$ostorelocation, "active"=>1, "deleted"=>0]);
								$return['result'] = 1;
								$return['data'] 	= [];
								if ($oinventorylocation!= null)
									$return['data'] = $this->getInventoryLocationResult($oinventorylocation);
								$return['text'] 	= "Inventario - Ubicación: ".$ostorelocation->getName();
							}else
								$return = ["result"=>-1, "text"=>'Inventario - Ubicación no válida para el inventario'];
						}else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicación - Identificador no válido'];
					}
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no válido'];
				break;

			// add -> Para el identificador de inventario pasado como argumento
			//				suma o actualiza una línea de producto dentro del inventario
			// 				Si la ubicación no se ha abierto para este inventario se abre
			case 'add':
				// Parámetros adicionales obligatorios
				$location_id 				= $request->request->get('location_id');
				$location_name 			= $request->request->get('location_name'); // Una de las 2
				$productvariant_id 	= $request->request->get('productvariant_id');
				$quantityconfirmed 	= $request->request->get('quantityconfirmed');
				// Parámetro adicional opcional
				$inventoryline_id 	= $request->request->get('inventoryline_id');

				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
				if ($oinventory!=null){
					// Comprueba si la ubicación es válida para este inventario sino -1 y mensaje
					// Si es válida pero no esta la base de datos de inventarios/ubicaciones se pone 1 pero data vacio
					// Si existe se devuelve en data
					$ostorelocation			= null;
					if ($location_id)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
					else
					if ($location_name)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);

					if ($ostorelocation){
						// Comprobar si es una ubicación válida para este inventario
						if ($ostorelocation->getStore()->getId()==$oinventory->getStore()->getId()){
							// Se comprueba si existe ubicación dada de alta para este inventario
							$oinventorylocation		= $erpInventoryLocationRepository->findOneBy(["inventory"=>$oinventory, "location"=>$ostorelocation, "active"=>1, "deleted"=>0]);
							if ($oinventorylocation==null){
								$oinventorylocation = new ERPInventoryLocation();
								$oauthor 						= $globaleUsersRepository->find($author_id);
								$oinventorylocation->setAuthor($oauthor);
								$oinventorylocation->setInventory($oinventory);
								$oinventorylocation->setLocation($ostorelocation);
								$oinventorylocation->setDatebegin(new \DateTime());
								$oinventorylocation->setActive(1);
								$oinventorylocation->setDeleted(0);
								$oinventorylocation->setDateadd(new \DateTime());
								$oinventorylocation->setDateupd(new \DateTime());
								$this->getDoctrine()->getManager()->persist($oinventorylocation);
								$this->getDoctrine()->getManager()->flush();
							}

							// Si se indica producto y cantidad se registra línea sino significa que solo era crear la ubicación
							if ($productvariant_id && $quantityconfirmed){
								// Comprobar que el product y variante existen
								$oproductvariant = $erpProductsVariantsRepository->findOneBy(["id"=>$productvariant_id, "deleted"=>0]);
								if ($oproductvariant){
									if ($quantityconfirmed && floatval($quantityconfirmed)>=0){

										// Se intenta recuperar la línea de producto y si no existe o código incorrecto se crea una nueva línea
										$oinventoryline	= null;
										if ($inventoryline_id && ctype_digit(strval($inventoryline_id)) && intval($inventoryline_id)>=0)
											$oinventoryline	= $erpInventoryLinesRepository->find($inventoryline_id);
										if ($oinventoryline==null){
											$oinventoryline = new ERPInventoryLines();
											$oauthor 						= $globaleUsersRepository->find($author_id);
											$oinventoryline->setAuthor($oauthor);
											$oinventoryline->setInventory($oinventory);
											$oinventoryline->setLocation($ostorelocation);
											$oinventoryline->setProductvariant($oproductvariant);
											$oinventoryline->setActive(1);
											$oinventoryline->setDeleted(0);
											$oinventoryline->setDateadd(new \DateTime());
											// Stock antiguo si no tiene una línea antigua, sino es null
											$oinventorylineold	= $erpInventoryLinesRepository->findOneBy(["inventory"=>$oinventory, "location"=>$ostorelocation, "productvariant"=>$oproductvariant, "active"=>1, "deleted"=>0]);
											if ($oinventorylineold==null){
												$stockold = 0;
												$ocompany = $globaleCompaniesRepository->find($company_id);
												$ostock = $erpStocksRepository->findOneBy(["company"=>$ocompany, "storelocation"=>$ostorelocation, "productvariant"=>$oproductvariant, "active"=>1, "deleted"=>0]);
												if ($ostock)
													$stockold = $ostock->getQuantity();
												$oinventoryline->setStockold($stockold);
											}
										}
										$oinventoryline->setDateupd(new \DateTime());
										$oinventoryline->setQuantityconfirmed($quantityconfirmed);
										$this->getDoctrine()->getManager()->persist($oinventoryline);
										$this->getDoctrine()->getManager()->flush();
										$return['result'] = 1;
										$return['data'] 	= [];
										if ($oinventoryline!= null)
											$return['data'] = $this->getInventoryLinesResult($oinventoryline);
										$return['text'] 	= "Inventario - Línea de producto";
									}else
										$return = ["result"=>-1, "text"=>'Inventario - Cantidad de producto no válida'];
								}else
									$return = ["result"=>-1, "text"=>'Inventario - Producto o variante no válida'];
							}else{
								$return = ["result"=>1, "text"=>'Inventario - Ubicación creada para vaciar su inventario'];
							}
						}else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicación no válida para el inventario'];
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Ubicación - Identificador no válido'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no válido'];
				break;


			// close -> Para el identificador de inventario pasado como argumento
			//				Cierra una ubicación de un inventario, si se ha indicado, o todas las ubicaciones abiertas si no se especifica
			// 				El cierre de uan ubicación actualiza el stock con todas las líneas de producto invnetariadas y
			//				los pone a 0 a los demás productos de la ubicación
			//				Se guardan todos los movimientos en el stockhistory
			case 'close':
				// Parámetros adicionales opcionales
				$location_id 				= $request->request->get('location_id');
				$location_name 			= $request->request->get('location_name'); // Una de las 2

				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "dateend"=>null, "deleted"=>0]);
				if ($oinventory!=null){
					// Comprueba si la ubicación es válida para este inventario sino -1 y mensaje
					// Si es válida pero no esta la base de datos de inventarios/ubicaciones se pone 1 pero data vacio
					// Si existe se devuelve en data
					$ostorelocation				= null;
					$oinventorylocations	= [];
					if ($location_id)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
					else
					if ($location_name)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);

					if ($ostorelocation)
						$oinventorylocations		= $erpInventoryLocationRepository->findBy(["inventory"=>$oinventory, "dateend"=>null, "location"=>$ostorelocation, "active"=>1, "deleted"=>0]);
					else
					if ($location_id==null && $location_name==null)
						$oinventorylocations		= $erpInventoryLocationRepository->findBy(["inventory"=>$oinventory, "dateend"=>null, "active"=>1, "deleted"=>0]);

					if (is_array($oinventorylocations)){
						foreach($oinventorylocations as $key=>$value){
							$oslocation = $value->getLocation();
							// Todos los productos definidos para la ubicación en stock
							$ostocks					= $erpStocksRepository->getProductByLocation($oslocation->getId());
							// Todos los productos inventariados de la ubicación
							$oinventorylines	= $erpInventoryLinesRepository->getInventoryLinesGroup($oinventory->getId(), $ostorelocation->getId());

							// Líneas a actualizar
							$alines 	= [];
							// Líneas de stock no inventariadas
							$anolines = [];
							foreach($ostocks as $key=>$ostock){
								$existsline = false;
								for($i=0; $i<count($oinventorylines) && !$existsline; $i++){
									if ($ostock['productvariant_id']==$oinventorylines[$i]['productvariant_id']){
										$existsline=true;
										$ostock['quantityconfirmed'] = $oinventorylines[$i]['quantityconfirmed'];
										$ostock['stockold'] = $oinventorylines[$i]['stockold'];
									}
								}
								if (!$existsline)
									array_push($anolines, $ostock);
								else
									array_push($alines, $ostock);
							}
							// Líneas inventarias no incluidas en stock
							$anostock = [];
							foreach($oinventorylines as $key=>$oinventoryline){
								$existsline = false;
								for($i=0; $i<count($ostocks) && !$existsline; $i++){
									if ($oinventoryline['productvariant_id']==$ostocks[$i]['productvariant_id'])
										$existsline=true;
								}
								if (!$existsline)
									array_push($anostock, $oinventoryline);
							}

							// Procesar líneas
							foreach($alines as $key=>$line){
								$erpStocksRepository->processInventoryLine($line);
							}
							// Resetear stock a 0 de productos no inventariados

							// Insertar nuevas líneas de stock con productos inventariados y no existentes en stock

							// Cierre de la ubicación


						}
					}

					// Cierre del inventario
/*					if ($location_id==null && $location_name==null){
						$oinventory->setDateend(new \DateTime());
						$oinventory->setDateupd(new \DateTime());
						$this->getDoctrine()->getManager()->persist($oinventory);
						$this->getDoctrine()->getManager()->flush();
						$return = ["result"=>1, "text"=>'Inventario - Cerrado - Ubicaciones cerradas: '.(is_array($oinventorylocations)?count($oinventorylocations):0)];
					}else{
						if ($ostorelocation && !($oinventorylocations && count($oinventorylocations)>0))
							$return = ["result"=>-1, "text"=>'Inventario - La ubicación indicada ya esta cerrada'];
						else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicación cerrada: '.$oinventorylocations[0]->getLocation()->getName()];
					}
					*/
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no válido o ya cerrado'];
				break;

			// Acción no válida
			default:
				$return = ["result"=>-1, "text"=>'Inventario - Acción no válida'];
				break;
		}

		// Resultado ----------------------------------------
    return new JsonResponse($return);
  }


	private function getInventoryResult(ERPInventory $oinventory){
		$return = [];
		$return['id'] = $oinventory->getId();
		$return['company_id'] = $oinventory->getCompany()->getId();
		$return['company_name'] = $oinventory->getCompany()->getName();
		$return['store_id'] = $oinventory->getStore()->getId();
		$return['store_name'] = $oinventory->getStore()->getName();
		$return['author_id'] = $oinventory->getAuthor()->getId();
		$return['author_name'] = $oinventory->getAuthor()->getName().' '.$oinventory->getAuthor()->getLastname();
		$return['datebegin'] = ($oinventory->getDatebegin()!=null?date_format($oinventory->getDatebegin(), "Y/m/d H:i:s"):'');
		$return['dateend'] = ($oinventory->getDateend()!=null?date_format($oinventory->getDateend(), "Y/m/d H:i:s"):'');
		$return['observation'] = ($oinventory->getObservation()!=null?$oinventory->getObservation():'');
		$return['active'] = $oinventory->getActive();
		$return['deleted'] = $oinventory->getDeleted();
		$return['dateadd'] = ($oinventory->getDateadd()!=null?date_format($oinventory->getDateadd(), "Y/m/d H:i:s"):'');
		$return['dateupd'] = ($oinventory->getDateupd()!=null?date_format($oinventory->getDateupd(), "Y/m/d H:i:s"):'');
		return $return;
	}

	private function getInventoryLinesResult(ERPInventoryLines $oinventorylines){
		$return = [];
		$return['id'] = $oinventorylines->getId();
		$return['inventory_id'] = $oinventorylines->getInventory()->getId();
		$return['inventory_name'] = $oinventorylines->getInventory()->getObservation();
		$return['location_id'] = $oinventorylines->getLocation()->getId();
		$return['location_name'] = $oinventorylines->getLocation()->getName();
		$return['author_id'] = $oinventorylines->getAuthor()->getId();
		$return['author_name'] = $oinventorylines->getAuthor()->getName().' '.$oinventorylines->getAuthor()->getLastname();
		$return['productvariant_id'] = $oinventorylines->getProductvariant()->getId();
		$return['product_id'] = $oinventorylines->getProductvariant()->getProduct()->getId();
		$return['product_name'] = $oinventorylines->getProductvariant()->getProduct()->getName();
		$return['variant_id'] = ($oinventorylines->getProductvariant()->getVariant()?$oinventorylines->getProductvariant()->getVariant()->getId():'');
		$return['variant_name'] = ($oinventorylines->getProductvariant()->getVariant()?$oinventorylines->getProductvariant()->getVariant()->getName():'');
		$return['quantityconfirmed'] = $oinventorylines->getQuantityconfirmed();
		$return['stockold'] = ($oinventorylines->getStockold()!=null?$oinventorylines->getStockold():'');
		$return['active'] = $oinventorylines->getActive();
		$return['deleted'] = $oinventorylines->getDeleted();
		$return['dateadd'] = ($oinventorylines->getDateadd()!=null?date_format($oinventorylines->getDateadd(), "Y/m/d H:i:s"):'');
		$return['dateupd'] = ($oinventorylines->getDateupd()!=null?date_format($oinventorylines->getDateupd(), "Y/m/d H:i:s"):'');
		return $return;
	}

	private function getInventoryLocationResult(ERPInventoryLocation $oinventorylocation){
		$return = [];
		$return['id'] = $oinventorylocation->getId();
		$return['inventory_id'] = $oinventorylocation->getInventory()->getId();
		$return['inventory_name'] = $oinventorylocation->getInventory()->getObservation();
		$return['location_id'] = $oinventorylocation->getLocation()->getId();
		$return['location_name'] = $oinventorylocation->getLocation()->getName();
		$return['author_id'] = $oinventorylocation->getAuthor()->getId();
		$return['author_name'] = $oinventorylocation->getAuthor()->getName().' '.$oinventorylocation->getAuthor()->getLastname();
		$return['datebegin'] = ($oinventorylocation->getDatebegin()!=null?date_format($oinventorylocation->getDatebegin(), "Y/m/d H:i:s"):'');
		$return['dateend'] = ($oinventorylocation->getDateend()!=null?date_format($oinventorylocation->getDateend(), "Y/m/d H:i:s"):'');
		$return['active'] = $oinventorylocation->getActive();
		$return['deleted'] = $oinventorylocation->getDeleted();
		$return['dateadd'] = ($oinventorylocation->getDateadd()!=null?date_format($oinventorylocation->getDateadd(), "Y/m/d H:i:s"):'');
		$return['dateupd'] = ($oinventorylocation->getDateupd()!=null?date_format($oinventorylocation->getDateupd(), "Y/m/d H:i:s"):'');
		return $return;
	}

	/**
	 * @Route("/{_locale}/ERP/inventory", name="inventory")
	 */
	public function index(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$utils = new $this->utilsClass();
		$templateLists[]=$utils->formatList($this->getUser());
		$formUtils=new GlobaleFormUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Inventory.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils->formatForm('stores', true, null, $this->class);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'inventoryController',
				'interfaceName' => 'Inventory',
				'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists,
				'forms' => $templateForms
			]);
			}
			return new RedirectResponse($this->router->generate('app_login'));
		}


	/**
	 * @Route("/api/linesInventory/{id}/list", name="linesInventoryList")
	 */
	public function indexlist($id,RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$class=ERPInventoryLines::class;
		$repository = $manager->getRepository($class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/InventoryLines.json"),true);
		$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,$class,
							['p.name'=>'productname', 'SUM(il.quantityconfirmed)'=>'quantityconfirmed', 'il.stockold'=>'stockold', 'concat(u.name," ",u.lastname)'=>'authorname', 'st.name '=>'location'],
							'erpinventory_lines il
							LEFT JOIN erpproducts_variants pv ON il.productvariant_id=pv.id
							LEFT JOIN erpproducts p ON p.id=pv.product_id
							LEFT JOIN erpstore_locations st ON  il.location_id=st.id
							LEFT JOIN globale_users u ON il.author_id=u.id',
							'il.inventory_id='.$id.' and il.active=1 and il.deleted=0 GROUP BY il.productvariant_id, il.location_id');
		return new JsonResponse($return);
	}



	/**
	 * @Route("/{_locale}/ERP/inventory/form/{id}", name="formERPInventory", defaults={"id"=0})
	 */
	public function formERPInventory($id,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$repository=$this->getDoctrine()->getRepository($this->class);
		$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
		$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
		$breadcrumb=$menurepository->formatBreadcrumb('inventory');
		array_push($breadcrumb,$new_breadcrumb);
		$utils = new ERPInventoryLinesUtils();
		$templateLists[]=$utils->formatListByInventory($id);
		return $this->render('@ERP/inventoryLinesList.html.twig', [
			'controllerName' => 'inventoryController',
			'interfaceName' => 'Inventory',
			'optionSelected' => $request->attributes->get('_route'),
			'menuOptions' =>  $menurepository->formatOptions($userdata),
			'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
			'userData' => $userdata,
			'inventory' => $obj,
			'linesInventory' => $templateLists
		]);
	 }

	/**
	 * @Route("/api/exportInventory/{id}", name="exportInventoryId")
	 */
	 public function exportInventoryId($id, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $inventoryRepository=$this->getDoctrine()->getRepository(ERPInventory::class);
		 $linesRepository=$this->getDoctrine()->getRepository(ERPInventoryLines::class);
		 $productsVariantsRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		 $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/temp/'.$this->getUser()->getId().'/';
		 if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
				 mkdir($uploadDir, 0775, true);
		 }
		 $filename = date("YmdHis").'_'.md5(uniqid()).'.xlsx';
		 $errorstyle[] = array('fill'=>"#AA0000");
		 $writer = new XLSXWriter();
		 $header = array("string","string","string","string");
		 $writer->setAuthor($this->getUser()->getName().' '.$this->getUser()->getLastname());
		 $writer->writeSheetHeader('Hoja1', $header, $col_options = ['suppress_row'=>true] );
		 $writer->writeSheetRow('Hoja1', ["CODIGO", "", "", "CANTIDAD","DESCRIPCION"]);
		 $row_number=1;
		 $lines=$linesRepository->getLines($id);
		 	 foreach($lines as $line){
				 if ($line["quantity"]-$line["oldquantity"]<0){
					 $row=["id"=>$line["productcode"], "", "", $line["quantity"]-$line["oldquantity"],$line["productname"]];
					 $writer->writeSheetRow('Hoja1', $row);
					 $row_number++;
				 }

			 }

		 $writer->writeToFile($uploadDir.$filename);
		 $response = new BinaryFileResponse($uploadDir.$filename);
		 $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		 $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'exported_operations.xlsx');
		 return $response;
	 }






}
