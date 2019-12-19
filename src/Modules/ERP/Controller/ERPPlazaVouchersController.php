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
use App\Modules\ERP\Entity\ERPSuppliers;
use App\Modules\ERP\Entity\ERPCustomers;
use App\Modules\ERP\Entity\ERPPlazaVouchers;
use App\Modules\ERP\Entity\ERPConfiguration;
use App\Modules\ERP\Reports\ERPPlazaVoucherReports;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPPlazaVouchersUtils;
use App\Modules\Security\Utils\SecurityUtils;


class ERPPlazaVouchersController extends Controller
{
	private $class=ERPPlazaVouchers::class;
	private $module="ERP";
  private $utilsClass=ERPPlazaVouchersUtils::class;


  /**
   * @Route("/{_locale}/ERP/plazavouchers/form/{id}", name="formPlazaVouchers", defaults={"id"=0})
   */
   public function formPlazaVouchers($id, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
    $template=dirname(__FILE__)."/../Forms/PlazaVouchers.json";
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('genericindex','ERP','PlazaVouchers');
    array_push($breadcrumb, $new_breadcrumb);
    $repository=$this->getDoctrine()->getRepository($this->class);
    $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
    if($id!=0 && $obj==null){
        return $this->render('@Globale/notfound.html.twig',[
          "status_code"=>404,
          "status_text"=>"Objeto no encontrado"
        ]);
    }
    $entity_name=$obj?'Vale número '.$obj->getId():'';

    return $this->render('@Globale/generictabform.html.twig', array(
            'entity_name' => $entity_name,
            'controllerName' => 'PlazaVouchersController',
            'interfaceName' => $this->get('translator')->trans('PlazaVouchers'),
            'optionSelected' => 'genericindex',
						'optionSelectedParams' => ["module"=>"ERP", "name"=>"PlazaVouchers"],
            'menuOptions' =>  $menurepository->formatOptions($userdata),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $id,
            'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
            'tabs' => [["name" => "data", "caption"=>"Datos", "icon"=>"fa fa-user","active"=>true, "route"=>$this->generateUrl("dataPlazaVouchers",["id"=>$id])]
                      ],
            'include_header' => [["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
            'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
                                 ["type"=>"css", "path"=>"/css/timeline.css"]]
    ));
  }

	/**
   * @Route("/{_locale}/ERP/plazavouchers/data/{id}/{action}", name="dataPlazaVouchers", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $template=dirname(__FILE__)."/../Forms/PlazaVouchers.json";
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request,
                       $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
                       method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
    return $utils->make($id, $this->class, $action, "formPlazaVouchers", "full", "@Globale/form.html.twig", 'formPlazaVouchers', $this->utilsClass);
  }

	/**
	 * @Route("/{_locale}/ERP/plazavouchers/{id}/{mode}", name="ERPPlazaVouchersPrint", defaults={"id"=0, "mode"="print"}))
	 */
	public function ERPPlazaVouchersPrint($id, $mode, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$repository=$this->getDoctrine()->getRepository($this->class);
		$configrepository=$this->getDoctrine()->getRepository(ERPConfiguration::class);
		$document=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		if(!$document) return new JsonResponse(["result"=>-1]);
		$configuration=$configrepository->findOneBy(["company"=>$this->getUser()->getCompany()]);
		$params=["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "id"=>$document->getId(), "user"=>$this->getUser(), "document"=>$document, "configuration"=>$configuration];
		$reportsUtils = new ERPPlazaVoucherReports();
		switch($mode){
			case "email":
				$tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Email'.DIRECTORY_SEPARATOR;
				if (!file_exists($tempPath) && !is_dir($tempPath)) {
						mkdir($tempPath, 0775, true);
				}
				$pdf=$reportsUtils->create($params,'F',$tempPath.'valeplaza_'.$id.'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
			case "temp":
				$tempPath=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.'Others'.DIRECTORY_SEPARATOR;
				if (!file_exists($tempPath) && !is_dir($tempPath)) {
						mkdir($tempPath, 0775, true);
				}
				$pdf=$reportsUtils->create($params,'F',$tempPath.'valeplaza_'.$id.'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
			case "download":
				$pdf=$reportsUtils->create($params,'D','valeplaza_'.$id.'.pdf');
				return new JsonResponse(["result"=>1]);
			break;
			case "print":
			case "default":
			return new Response($reportsUtils->create($params,'I','valeplaza_'.$id.'.pdf'), 200, array(
        'Content-Type' => 'application/pdf'));
			break;
		}
		return new JsonResponse(["result"=>0]);
	}

}
