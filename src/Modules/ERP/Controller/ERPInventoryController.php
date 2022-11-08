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
use App\Modules\ERP\Entity\ERPInventory;
use App\Modules\ERP\Entity\ERPInventoryLines;
use App\Modules\ERP\Entity\ERPInventoryLocation;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStocksHistory;

class ERPInventoryController extends Controller
{
	private $class=ERPInventory::class;
		private $utilsClass=ERPInventoryUtils::class;

	/**
	  * @Route("/api/inventory/{action}/{id}", name="inventory", defaults={"action"="info","id"=0})
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
		$erpInventoryRepository=$this->getDoctrine()->getRepository(ERPInventory::class);
		$erpStoresRepository=$this->getDoctrine()->getRepository(ERPStores::class);
		$globaleCompaniesRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
		$globaleUsersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);

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
}
