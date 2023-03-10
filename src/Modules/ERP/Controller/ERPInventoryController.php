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
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStocksHistory;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachines;
use App\Modules\ERP\Entity\ERPStoresManagersVendingMachinesChannels;
use App\Modules\ERP\Entity\ERPTypesMovements;
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
		// Par??metros ------------------------------------
		// Acci??n
		if ($action==null || $action=='')
			$action = 'info';
		// Id de la acci??n
		if ($id==null || $id=='' || !ctype_digit(strval($id)))
			$id = 0;
		else
			$id = intval($id);
		// Usuario
		$author_id			= $this->getUser();
		// Compa??ia
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
			// info -> Obtiene la informaci??n del inventario pasado como argumento
			case 'info':
				if ($id>0){
					$oinventory	= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
					if ($oinventory!=null){
						$return['result'] = 1;
						$return['data'] 	= $this->getInventoryResult($oinventory);
						$return['text'] 	= "Inventario - Informaci??n obtenida correctamente";
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Identificador no existe'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido'];
				break;

			// create -> Para el identificador de almac??n dado se comprueba si existe un Inventario
			//					 abierto si es as?? se devuelve este sino se crea
			case 'create':
				// Par??metros adicionales
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
							$code = $erpInventoryRepository->getNextCode();
							$oinventory->setCode($code);
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
						$return = ["result"=>-1, "text"=>'Inventario - Creaci??n - Almac??n no v??lido'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Creaci??n - Almac??n no v??lido'];
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
				// Par??metros adicionales
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
					// De una ubicaci??n
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
							$return['text'] 	= "Inventario - Productos de la ubicaci??n: ".$ostorelocation->getName();
						}else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n - Identificador no v??lido'];
					}
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido'];
				break;

			// nolines -> Para el identificador de inventario pasado como argumento
			//					obtiene los productos que no se han inventariado pero que existian en stock
			//					para la ubicaci??n pasada
			case 'nolines':
				// Par??metros adicionales
				$location_id 	= $request->request->get('location_id');
				$location_name= $request->request->get('location_name');
				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
				if ($oinventory!=null){
					if ($location_id)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
					else
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);
					if ($ostorelocation){
						// Todos los productos definidos para la ubicaci??n en stock
						$ostocks					= $erpStocksRepository->getProductByLocation($ostorelocation->getId());
						// Todos los productos inventariados de la ubicaci??n
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
						$return['text'] 	= "Inventario - Ubicaci??n - L??neas en stock no inventariadas";
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n no v??lida'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido'];
				break;

			// locations -> Para el identificador de inventario pasado como argumento
			//					obtiene las ubicaciones ya procesadas
			case 'locations':
				// Par??metros adicionales
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
						$return['text'] 	= "Inventario - Ubicaci??n - Todas las ubicaciones";
					}else{
					// Comprueba si la ubicaci??n es v??lida para este inventario sino -1 y mensaje
					// Si es v??lida pero no esta la base de datos de inventarios/ubicaciones se pone 1 pero data vacio
					// Si existe se devuelve en data
						$ostorelocation = null;
						if ($location_id)
							$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
						else
							$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);
						if ($ostorelocation){
							// Comprobar si es una ubicaci??n v??lida para este inventario
							if ($ostorelocation->getStore()->getId()==$oinventory->getStore()->getId()){
								$oinventorylocation		= $erpInventoryLocationRepository->findOneBy(["inventory"=>$oinventory, "location"=>$ostorelocation, "active"=>1, "deleted"=>0]);
								$return['result'] = 1;
								$return['data'] 	= [];
								if ($oinventorylocation!= null)
									$return['data'] = $this->getInventoryLocationResult($oinventorylocation);
								$return['text'] 	= "Inventario - Ubicaci??n: ".$ostorelocation->getName();
							}else
								$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n no v??lida para el inventario'];
						}else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n - Identificador no v??lido'];
					}
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido'];
				break;

			// add -> Para el identificador de inventario pasado como argumento
			//				suma o actualiza una l??nea de producto dentro del inventario
			// 				Si la ubicaci??n no se ha abierto para este inventario se abre
			case 'add':
				// Par??metros adicionales obligatorios
				$location_id 				= $request->request->get('location_id');
				$location_name 			= $request->request->get('location_name'); // Una de las 2
				$productvariant_id 	= $request->request->get('productvariant_id');
				$productbarcode 		= $request->request->get('productbarcode');
				$quantityconfirmed 	= $request->request->get('quantityconfirmed');
				// Par??metro adicional opcional
				$inventoryline_id 	= $request->request->get('inventoryline_id');
				$force 							= $request->request->get('force');

				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
				if ($oinventory!=null && $oinventory->getDateend()==null){
					// Comprueba si la ubicaci??n es v??lida para este inventario sino -1 y mensaje
					// Si es v??lida pero no esta la base de datos de inventarios/ubicaciones se pone 1 pero data vacio
					// Si existe se devuelve en data
					$ostorelocation			= null;
					if ($location_id)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["id"=>$location_id, "deleted"=>0]);
					else
					if ($location_name)
						$ostorelocation			= $erpStoreLocationsRepository->findOneBy(["store"=>$oinventory->getStore(), "name"=>$location_name, "deleted"=>0]);

					if ($ostorelocation){
						// Comprobar si es una ubicaci??n v??lida para este inventario
						if ($ostorelocation->getStore()->getId()==$oinventory->getStore()->getId()){
							// Se comprueba si existe ubicaci??n dada de alta para este inventario y no este cerrada
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
							if ($oinventorylocation->getDateend()==null){
								$oproductvariant = null;
								// Si viene por barcode
								if ($productvariant_id==null){
									$oproductvariant = $this->getProductVariantByBarcode($productbarcode);
								}else{
									$oproductvariant = $erpProductsVariantsRepository->findOneBy(["id"=>$productvariant_id, "deleted"=>0]);
								}
								// Comprobar que el producto y variante existen
								if ($oproductvariant){
									if ($quantityconfirmed && floatval($quantityconfirmed)>=0){
										// Comprobar si ya existia l??nea de stock para este producto y ubicaci??n
										// sino mensaje de error. Si 'force=1' se crea la nueva l??nea de stock
										$ostock = $erpStocksRepository->findOneBy(["storelocation"=>$ostorelocation, "productvariant"=>$oproductvariant, "deleted"=>0]);
										if ($ostock || $force){
											// Se intenta recuperar la l??nea de producto y si no existe o c??digo incorrecto se crea una nueva l??nea
											$oinventoryline	= null;
											if ($inventoryline_id && ctype_digit(strval($inventoryline_id)) && intval($inventoryline_id)>=0)
												$oinventoryline	= $erpInventoryLinesRepository->find($inventoryline_id);
											if ($oinventoryline==null){
												$oinventoryline = new ERPInventoryLines();
												$oauthor 				= $globaleUsersRepository->find($author_id);
												$oinventoryline->setAuthor($oauthor);
												$oinventoryline->setInventory($oinventory);
												$oinventoryline->setLocation($ostorelocation);
												$oinventoryline->setProductvariant($oproductvariant);
												$oinventoryline->setActive(1);
												$oinventoryline->setDeleted(0);
												$oinventoryline->setDateadd(new \DateTime());
												// Stock antiguo si no tiene una l??nea antigua, sino es null
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
											$return['text'] 	= "Inventario - L??nea de producto";
										}else
											$return = ["result"=>-2, "text"=>'Inventario - Producto nuevo en esta ubicaci??n'];
									}else
										$return = ["result"=>-1, "text"=>'Inventario - Cantidad de producto no v??lida'];
								}else{
									if (($productvariant_id || $productbarcode) && $quantityconfirmed)
										$return = ["result"=>-1, "text"=>'Inventario - Producto o variante no v??lida'];
									else
										$return = ["result"=>1, "text"=>'Inventario - Ubicaci??n creada para vaciar su inventario'];
								}
							}else
								$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n cerrada'];
						}else
							$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n no v??lida para el inventario'];
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n - Identificador no v??lido'];
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido o inventario cerrado'];
				break;

				// delete -> Para el identificador de inventario pasado como argumento
				//				borra la l??nea pasada y si esta era la que ten??a el sotck antiguo
				// 				Se pasa a la siguiente l??nea
				case 'delete':
					// Par??metro adicional obligatorio
					$inventoryline_id 	= $request->request->get('inventoryline_id');
					$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "deleted"=>0]);
					if ($oinventory!=null){
						// Se compruebba que el inventario no este cerrado
						if ($oinventory->getDateend()==null){
							// Comprueba que exista la l??nea y sea de este inventario
							$oinventoryline	= null;
							if ($inventoryline_id && ctype_digit(strval($inventoryline_id)) && intval($inventoryline_id)>=0)
								$oinventoryline	= $erpInventoryLinesRepository->find($inventoryline_id);
							if ($oinventoryline!=null){
								// Comprobar que la ubicaci??n no este cerrada
								$oinventorylocation		= $erpInventoryLocationRepository->findOneBy(["inventory"=>$oinventory, "location"=>$oinventoryline->getLocation(), "active"=>1, "deleted"=>0]);
								if ($oinventorylocation && $oinventorylocation->getDateend()==null){
									if ($oinventoryline->getStockold()!=null){
										// Comprobar si existe m??s lineas del mismo producto para esta ubicaci??n
										$oinventorylines	= $erpInventoryLinesRepository->findBy(["inventory"=>$oinventory,"location"=>$oinventoryline->getLocation(), "productvariant"=>$oinventoryline->getProductvariant(), "active"=>1, "deleted"=>0],["id"=>"ASC"]);
										if ($oinventorylines && count($oinventorylines)>1){
											$changestock = false;
											for($i=0; $i<count($oinventorylines) && !$changestock; $i++){
												$oinventorylineo = $oinventorylines[$i];
												if ($oinventorylineo->getId()!=$oinventoryline->getId()){
													$oinventorylineo->setStockold($oinventoryline->getStockold());
													$this->getDoctrine()->getManager()->persist($oinventorylineo);
													$this->getDoctrine()->getManager()->flush();
													$changestock = true;
												}
											}
										}
									}
									$return = ["result"=>1, "text"=>'Inventario - L??nea borrada correctamente'];
									if ($oinventoryline!= null)
										$return['data'] = $this->getInventoryLinesResult($oinventoryline);
									// Borrado de la l??nea
									$erpInventoryLinesRepository->deleteLine($oinventoryline->getId());

								}else
									$return = ["result"=>-1, "text"=>'Inventario - Ubicaci??n cerrada - borrado no v??lido'];
							}else
								$return = ["result"=>-1, "text"=>'Inventario - L??nea no v??lida'];
						}else
							$return = ["result"=>-1, "text"=>'Inventario cerrado - borrado no v??lido'];
					}else
						$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido'];
					break;

			// close -> Para el identificador de inventario pasado como argumento
			//				Cierra una ubicaci??n de un inventario, si se ha indicado, o todas las ubicaciones abiertas si no se especifica
			// 				El cierre de uan ubicaci??n actualiza el stock con todas las l??neas de producto invnetariadas y
			//				los pone a 0 a los dem??s productos de la ubicaci??n
			//				Se guardan todos los movimientos en el stockhistory
			case 'close':
				// Par??metros adicionales opcionales
				$location_id 				= $request->request->get('location_id');
				$location_name 			= $request->request->get('location_name'); // Una de las 2

				$oinventory		= $erpInventoryRepository->findOneBy(["id"=>$id, "dateend"=>null, "deleted"=>0]);
				if ($oinventory!=null){
					// Comprueba si la ubicaci??n es v??lida para este inventario sino -1 y mensaje
					// Si es v??lida pero no esta la base de datos de inventarios/ubicaciones se pone 1 pero data vacio
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

					// L??neas a actualizar
					$alines 	= [];
					// L??neas de stock no inventariadas
					$anolines = [];
					// L??neas inventarias no incluidas en stock
					$anostock = [];
					if (is_array($oinventorylocations)){
						foreach($oinventorylocations as $key=>$oinventorylocation){
							$oslocation = $oinventorylocation->getLocation();
							// Todos los productos definidos para la ubicaci??n en stock
							$ostocks					= $erpStocksRepository->getProductByLocation($oslocation->getId());
							// Todos los productos inventariados de la ubicaci??n
							$oinventorylines	= $erpInventoryLinesRepository->getInventoryLinesGroup($oinventory->getId(), $oslocation->getId());

							foreach($ostocks as $key=>$ostock){
								$existsline = false;
								for($i=0; $i<count($oinventorylines) && !$existsline; $i++){
									if ($ostock['productvariant_id']==$oinventorylines[$i]['productvariant_id']){
										$existsline=true;
										$ostock['quantityconfirmed'] = $oinventorylines[$i]['quantityconfirmed'];
										$ostock['stockold'] = $oinventorylines[$i]['stockold'];
									}
								}
								if (!$existsline){
									$ostock['quantityconfirmed'] = 0;
									$ostock['stockold'] = $ostock['quantity'];
									array_push($anolines, $ostock);
								}else{
									array_push($alines, $ostock);
								}
							}

							foreach($oinventorylines as $key=>$oinventoryline){
								$existsline = false;
								for($i=0; $i<count($ostocks) && !$existsline; $i++){
									if ($oinventoryline['productvariant_id']==$ostocks[$i]['productvariant_id'])
										$existsline=true;
								}
								if (!$existsline)
									array_push($anostock, $oinventoryline);
							}

							// Procesar l??neas
							foreach($alines as $key=>$line){
								$erpStocksRepository->processInventoryLine($oinventory->getCode(), $author_id, $line);
							}
							// Resetear stock a 0 de productos no inventariado
							foreach($anolines as $key=>$line){
								$erpStocksRepository->processInventoryLine($oinventory->getCode(), $author_id, $line);
							}
							// Insertar nuevas l??neas de stock con productos inventariados y no existentes en stock
							foreach($anostock as $key=>$line){
								$erpStocksRepository->processInventoryLineNoStock($oinventory->getCode(), $author_id, $line);
							}
							// Cierre de la ubicaci??n
							$oinventorylocation->setDateend(new \DateTime());
							$oinventorylocation->setDateupd(new \DateTime());
							$this->getDoctrine()->getManager()->persist($oinventorylocation);
							$this->getDoctrine()->getManager()->flush();

						}
					}

					// Cierre del inventario
					if ($location_id==null && $location_name==null){
						$oinventory->setDateend(new \DateTime());
						$oinventory->setDateupd(new \DateTime());
						$this->getDoctrine()->getManager()->persist($oinventory);
						$this->getDoctrine()->getManager()->flush();
						$return = ["result"=>1, "text"=>'Inventario - Cerrado - Ubicaciones cerradas: '.(is_array($oinventorylocations)?count($oinventorylocations):0)];
					}else{
						if ($ostorelocation && !($oinventorylocations && count($oinventorylocations)>0))
							$return = ["result"=>-1, "text"=>'Inventario - La ubicaci??n indicada ya esta cerrada'];
						else
							$return = ["result"=>-1, 	"data"=> $anolines, "text"=>'Inventario - Ubicaci??n cerrada: '.$oinventorylocations[0]->getLocation()->getName()];
					}
				}else
					$return = ["result"=>-1, "text"=>'Inventario - Identificador no v??lido o ya cerrado'];
				break;

			// Acci??n no v??lida
			default:
				$return = ["result"=>-1, "text"=>'Inventario - Acci??n no v??lida'];
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
		$return['product_code'] = $oinventorylines->getProductvariant()->getProduct()->getCode();
		$return['variant_id'] = ($oinventorylines->getProductvariant()->getVariant()?$oinventorylines->getProductvariant()->getVariant()->getId():'');
		$return['variant_type'] = ($oinventorylines->getProductvariant()->getVariant()?$oinventorylines->getProductvariant()->getVariant()->getVarianttype()->getName():'');
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

	private function getProductVariantByBarcode($barcode){
		$productvariant = null;
		if($barcode){
			$EAN13repository=$this->getDoctrine()->getRepository(ERPEAN13::class);
			$Productrepository=$this->getDoctrine()->getRepository(ERPProducts::class);
			$ProductsVariantsrepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
			// Busqueda de producto por su ID de base de datos (P.)
			if(substr(strtoupper($barcode),0,2)=="P."){
				$product=$Productrepository->findOneBy(["id"=>intval(substr($barcode,2)), "deleted"=>0]);
				$productvariant=$ProductsVariantsrepository->findOneBy(["product"=>$product, "variant"=>null, "deleted"=>0]);
			}else{
				// Busqueda de producto por su ID de productvariant de base de datos (V.)
				if(substr(strtoupper($barcode),0,2)=="V."){
					$productvariant=$ProductsVariantsrepository->findOneBy(["id"=>intval(substr($barcode,2)), "deleted"=>0]);
				}else{
					$EAN13=$EAN13repository->findOneBy(["name"=>$barcode, "deleted"=>0]);
					if($EAN13){
						$productvariant=$EAN13->getProductVariant();
					}else{
						//Try with a lead 0 at start of $barcode
						$EAN13=$EAN13repository->findOneBy(["name"=>'0'.$barcode, "deleted"=>0]);
						if($EAN13){
							$productvariant=$EAN13->getProductVariant();
						}
					}
				}
			}
		}
		return $productvariant;
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
							'il.inventory_id='.$id.' and il.active=1 and il.deleted=0',
							20,
							'il.id',
						  'il.productvariant_id, il.location_id');
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
		 $writer->writeSheetRow('Hoja1', ["C??digo", "", "", "Variaci??n","Descripci??n","Cantidad previa", "Cantidad contada"]);
		 $row_number=1;
		 $lines=$linesRepository->getLines($id);
		 	 foreach($lines as $line){
				$row=["id"=>$line["productcode"], "", "", $line["quantity"]-$line["oldquantity"],$line["productname"],$line["oldquantity"],$line["quantity"]];
				$writer->writeSheetRow('Hoja1', $row);
				$row_number++;
			 }

		 $writer->writeToFile($uploadDir.$filename);
		 $response = new BinaryFileResponse($uploadDir.$filename);
		 $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		 $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'exported_operations.xlsx');
		 return $response;
	 }


	 /**
 	 * @Route("/api/inventoryVengingMachine/{id}", name="inventoryVengingMachine")
 	 */
 	 public function inventoryVengingMachine($id, Request $request){
		$numChannel=$request->request->get('channel');
		$channelRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachinesChannels::class);
		$vendingmachineRepository=$this->getDoctrine()->getRepository(ERPStoresManagersVendingMachines::class);
		$productvariantRepository=$this->getDoctrine()->getRepository(ERPProductsVariants::class);
		$vendingmachine=$vendingmachineRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
		$channel=$channelRepository->findOneBy(["vendingmachine"=>$vendingmachine, "channel"=>$numChannel, "active"=>1, "deleted"=>0]);
		if ($channel){
			$newStock=$request->request->get('newStock')*$channel->getMultiplier();
			$typesRepository=$this->getDoctrine()->getRepository(ERPTypesMovements::class);
			$type=$typesRepository->findOneBy(["name"=>"Ajuste de inventario en maquina"]);
			$stockHistory=new ERPStocksHistory();
			$stockHistory->setProductcode($channel->getProduct()->getCode());
			$stockHistory->setProductname($channel->getProduct()->getName());
			$productvariant=$productvariantRepository->findOneBy(["product"=>$channel->getProduct(), "variant"=>null]);
			$stockHistory->setProductVariant($productvariant);
			if ($channel->getVendingmachine()->getStorelocation()!=null) {
					$stockHistory->setLocation($channel->getVendingmachine()->getStorelocation());
				}
				else {
					$locationRepository=$this->getDoctrine()->getRepository(ERPStoreLocations::class);
					$storeLocation=$locationRepository->findOneBy(["name"=>"EXPEND ALM"]);
					$stockHistory->setLocation($storeLocation);
			}
			$stockHistory->setVendingmachinechannel($channel);
			$stockHistory->setUser($this->getUser());
			$stockHistory->setCompany($this->getUser()->getCompany());
			$stockHistory->setDateadd(new \Datetime());
			$stockHistory->setDateupd(new \Datetime());
			$stockHistory->setQuantity($newStock-($channel->getQuantity()));
			$stockHistory->setPreviousqty($channel->getQuantity());
			$stockHistory->setNewqty($newStock);
			$stockHistory->setType($type);
			$stockHistory->setActive(true);
			$stockHistory->setDeleted(false);
			$this->getDoctrine()->getManager()->persist($stockHistory);
			$channel->setQuantity($newStock);
			$this->getDoctrine()->getManager()->persist($channel);
			$this->getDoctrine()->getManager()->flush();
			$response=["result"=>1, "text"=>'Inventario - Guardado con ??xito'];
		}
		else $response=["result"=>-1, "text"=>'No existe el canal en la m??quina'];

		return new JsonResponse($response);
	 }



}
