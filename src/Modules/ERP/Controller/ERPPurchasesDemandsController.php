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
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSalesOrdersUtils;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Entity\ERPSeries;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPVariants;
use App\Modules\ERP\Entity\ERPPurchasesDemands;
use App\Modules\ERP\Entity\ERPPurchasesDemandsReasons;
use App\Modules\ERP\Entity\ERPStores;
use App\Modules\ERP\Entity\ERPStoreLocations;
use App\Modules\Globale\Entity\GlobaleUsersWidgets;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Security\Utils\SecurityUtils;


class ERPPurchasesDemandsController extends Controller
{
	private $module='ERP';
	private $class=ERPPurchasesDemands::class;
	private $utilsClass=ERPPurchasesDemandsUtils::class;

  /**
	 * @Route("/{_locale}/ERP/purchasesdemands", name="purchasesdemands", defaults={"id"=0}))
	 */
	public function index(RouterInterface $router,Request $request)
	{

		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if($this->getUser()){
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$id=$this->getUser()->getId();

		}
		else return $this->redirect($this->generateUrl('unauthorized'));
	//	else return $this->redirectToRoute();
		$usersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$user=$usersRepository->findOneBy(["id"=>$id]);
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $purchasesdemandsRepository=$this->getDoctrine()->getRepository(ERPPurchasesDemands::class);


    if($request->query->get('code',null)){
			$obj = $purchasesdemandsRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'active'=>1, 'deleted'=>0]);
			if($obj) return $this->redirectToRoute($request->get('_route'), ['id' => $obj->getId()]);
			else return $this->redirectToRoute($request->get('_route'), ['id' => 0]);
		}

    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

    //Search Products
		$classProductsUtils="\App\Modules\ERP\Utils\ERPProductsUtils";
		$productsutils = new $classProductsUtils();
		$productslist=$productsutils->formatList($this->getUser());
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-plus-circle", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    $breadcrumb=$menurepository->formatBreadcrumb('purchasesdemands');
    array_push($breadcrumb,$new_breadcrumb);

		$purchasesdemands=$purchasesdemandsRepository->findBy(["active"=>true,"deleted"=>false]);


/*
    if($purchasesdemands==null){
			$purchasesdemands=new $this->class();
		}*/

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@ERP/purchasesdemands.html.twig', [
      /*  'moduleConfig' => $config,*/
        'controllerName' => 'purchasesdemandsController',
        'interfaceName' => 'PurchasesDemands',
        'optionSelected' => 'genericindex',
        'optionSelectedParams' => ["module"=>"ERP", "name"=>"PurchasesDemands"],
        'menuOptions' =>  $menurepository->formatOptions($userdata),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'purchasesdemandsLines' => $purchasesdemands,
        'productslist' => $productslist
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));

  }


	/**
	 * @Route("/{_locale}/ERP/purchasesdemands/data", name="dataERPPurchasesDemands", defaults={"id"=0}))
	 */
	public function data(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$purchasesdemandsRepository=$this->getDoctrine()->getRepository(ERPPurchasesDemands::class);
		$productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);
		$suppliersRepository=$this->getDoctrine()->getRepository(ERPSuppliers::class);
		$variantsRepository=$this->getDoctrine()->getRepository(ERPVariants::class);
		$reasonsRepository=$this->getDoctrine()->getRepository(ERPPurchasesDemandsReasons::class);



	  $purchasesdemands=$purchasesdemandsRepository->findBy(["active"=>1,"deleted"=>0]);

		//Get content of the json reques
		$fields=json_decode($request->getContent());
			dump($fields);
		$linenumIds=[];
		foreach ($fields->lines as $key => $value) {
			if($value->code!=null)
			{

			$product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$value->code, "deleted"=>0]);
			$supplier=$suppliersRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$value->supplier, "active"=>1, "deleted"=>0]);
			$variant=null;
			if(isset($value->variant) AND $value->variant!="-1"){
				  $variant=$variantsRepository->findOneBy(["id"=>$value->variant]);
				 	$line=$purchasesdemandsRepository->findOneBy(["product"=>$product, "active"=>1, "deleted"=>0,"variant"=>$variant]);
				}
			else $line=$purchasesdemandsRepository->findOneBy(["product"=>$product, "active"=>1, "deleted"=>0]);


			//if(!$product) continue;
			if(!$line){
				$line=new ERPPurchasesDemands();
				$line->setAgent($this->getUser());
				$line->setActive(1);
				$line->setDeleted(0);
				$line->setDateadd(new \DateTime());
			}
				$line->setLinenum($value->linenum);
				$line->setProduct($product);
				$line->setSupplier($supplier);
				$line->setQuantity(floatval($value->quantity));

				if($variant){
						$line->setVariant($variant);
				 }
				 if(isset($value->reason) AND $value->reason!="-1"){
						 $reason=$reasonsRepository->findOneBy(["id"=>$value->reason]);
						 $line->setReason($reason);
					}
				if($value->deleted){
					$line->setActive(0);
					$line->setDeleted(1);
				}
				$line->setDateupd(new \DateTime());
				$this->getDoctrine()->getManager()->persist($line);
				$this->getDoctrine()->getManager()->flush();
				$linenumIds[]=["linenum"=>$value->linenum, "id"=>$line->getId()];
			}
		}
		return new JsonResponse(["result"=>1,"data"=>["id"=>$this->getUser()->getId(), "lines"=>$linenumIds]]);
		//return new JsonResponse(["result"=>1]);
	}


	/**
 * @Route("/api/ERP/purchasesdemands/reasons/get", name="getReasons")
 */
 public function getReasons(RouterInterface $router,Request $request){
	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	$purchasesdemandsReasonsRepository=$this->getDoctrine()->getRepository(ERPPurchasesDemandsReasons::class);
	$reasons=$purchasesdemandsReasonsRepository->findBy(["active"=>true,"deleted"=>false]);


	foreach($reasons as $reason){
		$item['id']=$reason->getId();
		$item['name']=$reason->getName();
		$responseReasons[]=$item;
	}

	return new JsonResponse(["reasons"=>$responseReasons]);

 }


 /**
 * @Route("/api/ERP/purchasesdemands/rejectProductNotification", name="rejectProductNotification")
 */
 public function rejectProductNotification(RouterInterface $router,Request $request){
 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 $nofitication_object=json_decode($request->getContent());
 $agentsRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
 $agent=$agentsRepository->findOneBy(["id"=>$nofitication_object->agent,"active"=>1,"deleted"=>0]);

 $channel=$agent->getDiscordchannel();
 $msg=":shopping_cart: :no_entry_sign: Tu solicitud de compra del producto ".$nofitication_object->code." ha sido rechazada por ".$this->getUser()->getName()." ".$this->getUser()->getLastName();
 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));
 $msg="Motivo :point_right: **".$nofitication_object->reason."**";
 file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$channel.'&msg='.urlencode($msg));

 return new JsonResponse(["result"=>1]);

 }

}
