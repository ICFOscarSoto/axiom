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
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPWebProducts;
use App\Modules\ERP\Entity\ERPEAN13;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\ERP\Entity\ERPProductsAttributes;
use App\Modules\ERP\Entity\ERPManufacturers;
use App\Modules\ERP\Entity\ERPStocks;
use App\Modules\ERP\Entity\ERPStockHistory;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoresManagers;
use App\Modules\ERP\Entity\ERPStoresManagersConsumers;
use App\Modules\ERP\Entity\ERPStoresUsers;
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPStoresManagersOperations;
use App\Modules\ERP\Entity\ERPStoresManagersOperationsLines;
use App\Modules\ERP\Entity\ERPWorkList;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPStoresManagersConsumersUtils;
use App\Modules\ERP\Utils\ERPEAN13Utils;
use App\Modules\ERP\Utils\ERPReferencesUtils;
use App\Modules\ERP\Utils\ERPStocksUtils;
use App\Modules\ERP\Utils\ERPProductsAttributesUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;
use App\Modules\ERP\Utils\ERPStoresManagersUtils;

class ERPStoresManagersOperationsController extends Controller
{
	private $class=ERPStoresManagersOperationsController::class;
	private $utilsClass=ERPStoresManagersOperationsUtils::class;
	private $module='ERP';

    /**
		 * @Route("/api/erp/storesmanagers/operations/create/{id}", name="createOperations", defaults={"id"=0})
		 */
		 public function createOperations($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
			$consumerRepository=$this->getDoctrine()->getRepository(ERPStoresManagersConsumers::class);
			$storeRepository=$this->getDoctrine()->getRepository(ERPStoresUsers::class);
			$store=$storeRepository->findOneBy(["user"=>$this->getUser(),"preferential"=>1,"active"=>1,"deleted"=>0]);
			if(!$store) return new JsonResponse(["result"=>-4, "text"=> "El usuario no tiene almacén preferente"]);
			$consumer=$consumerRepository->findOneBy(["id"=>$id,"active"=>1,"deleted"=>0]);
			if(!$consumer) return new JsonResponse(["result"=>-2, "text"=> "El usuario no existe"]);
			if($consumer->getManager()->getCompany()!=$this->getUser()->getCompany()) return new JsonResponse(["result"=>-3, "text"=> "Operación no autorizada"]);

			$worklistProducts=$worklistRepository->findBy(["user"=>$this->getUser(),"deleted"=>0]);
			if(count($worklistProducts)){
					$operation=new ERPStoresManagersOperations();
					$operation->setCompany($this->getUser()->getCompany());
					$operation->setManager($consumer->getManager());
					$operation->setAgent($this->getUser());
					$operation->setConsumer($consumer);
					$operation->setStore($store->getStore());
					$operation->setDate(new \Datetime());
					$operation->setDateadd(new \Datetime());
					$operation->setDateupd(new \Datetime());
					$operation->setActive(true);
					$operation->setDeleted(false);
					$this->getDoctrine()->getManager()->persist($operation);
					$this->getDoctrine()->getManager()->flush();

					foreach($worklistProducts as $item){
						$line=new ERPStoresManagersOperationsLines();
						$line->setOperation($operation);
						$line->setProduct($item->getProduct());
						$line->setQuantity($item->getQuantity());
						$line->setCode($item->getCode());
						$line->setName($item->getName());
						$line->setVariant($item->getVariant());
						$line->setLocation($item->getLocation());
						$line->setDateadd(new \Datetime());
						$line->setDateupd(new \Datetime());
						$line->setActive(true);
						$line->setDeleted(false);
						$this->getDoctrine()->getManager()->persist($line);
						$this->getDoctrine()->getManager()->flush();
						//Discount quantities

					}

					//Clear worklist
					foreach($worklistProducts as $item){
						$this->getDoctrine()->getManager()->remove($item);
						$this->getDoctrine()->getManager()->flush();
					}

			return new JsonResponse(["result"=>1]);
			}else return new JsonResponse(["result"=>-1, "text"=> "No hay productos para realizar la operación"]);
		}
}
