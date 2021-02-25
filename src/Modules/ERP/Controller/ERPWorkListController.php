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
use App\Modules\ERP\Entity\ERPWorkList;
use App\Modules\Globale\Entity\GlobaleUsersWidgets;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Security\Utils\SecurityUtils;


class ERPWorkListController extends Controller
{
	private $module='ERP';
	private $class=ERPWorkList::class;
	private $utilsClass=ERPWorkListUtils::class;

  /**
	 * @Route("/{_locale}/ERP/worklist", name="worklist", defaults={"id"=0}))
	 */
	public function index(RouterInterface $router,Request $request)
	{
    $id=$this->getUser()->getId();
		$usersRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$user=$usersRepository->findOneBy(["id"=>$id]);
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);

    if($request->query->get('code',null)){
			$obj = $worklistRepository->findOneBy(['code'=>$request->query->get('code',null), 'company'=>$this->getUser()->getCompany(), 'active'=>1, 'deleted'=>0]);
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
		$productslist["fieldButtons"]=[["id"=>"select", "type" => "default", "default"=>true, "icon" => "fa fa-dot-circle-o", "name" => "editar", "route" => null, "actionType" => "background", "modal"=>"", "confirm" => false, "tooltip" =>""]];
		$productslist["topButtons"]=[];

    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    $breadcrumb=$menurepository->formatBreadcrumb('worklist');
    array_push($breadcrumb,$new_breadcrumb);

    $worklist=null;
    if($id!=0){
			$worklist=$worklistRepository->findBy(["user"=>$user, "active"=>true,"deleted"=>false]);
			//	$worklist=$worklistRepository->findByUser($id);
			dump($worklist);
		}


    if($worklist==null){
			$worklist=new $this->class();
		}

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@ERP/worklist.html.twig', [
      /*  'moduleConfig' => $config,*/
        'controllerName' => 'worklistController',
        'interfaceName' => 'WorkList',
        'optionSelected' => 'genericindex',
        'optionSelectedParams' => ["module"=>"ERP", "name"=>"WorkList"],
        'menuOptions' =>  $menurepository->formatOptions($userdata),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'worklistLines' => $worklist,
        'productslist' => $productslist,
        'id' => $id
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));

  }


  /**
   * @Route("/{_locale}/ERP/worklist/data/{id}", name="dataERPWorkList", defaults={"id"=0}))
   */
  public function data($id, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $worklistRepository=$this->getDoctrine()->getRepository(ERPWorkList::class);
    $productsRepository=$this->getDoctrine()->getRepository(ERPProducts::class);

    $worklist=$worklistRepository->findAll(["user"=>$this->getUser()->getCompany(),"deleted"=>0]);

    //Get content of the json reques
    $fields=json_decode($request->getContent());
		dump($fields);
		$linenumIds=[];
    foreach ($fields->lines as $key => $value) {
			if($value->code!=null)
			{
      $product=$productsRepository->findOneBy(["company"=>$this->getUser()->getCompany(), "code"=>$value->code, "active"=>1, "deleted"=>0]);
      $line=$worklistRepository->findOneBy(["product"=>$product]);

      //if(!$product) continue;
      if(!$line ){
        $line=new ERPWorkList();
        $line->setUser($this->getUser());
        $line->setActive(1);
        $line->setDeleted(0);
        $line->setDateadd(new \DateTime());
      }
		  	$line->setLinenum($value->linenum);
        $line->setProduct($product);
        $line->setCode($value->code);
        $line->setName($value->name);
        $line->setQuantity(floatval($value->quantity));
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



}
