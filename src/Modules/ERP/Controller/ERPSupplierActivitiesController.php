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
use App\Modules\ERP\Entity\ERPSupplierActivities;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPSupplierActivitiesUtils;
use App\Modules\Security\Utils\SecurityUtils;


class ERPSupplierActivitiesController extends Controller
{
	  private $class=ERPSupplierActivities::class;
	  private $module='ERP';

    /**
     * @Route("/{_locale}/ERP/supplieractivities", name="supplieractivities")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new ERPsupplierActivitiesUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
			$obj = $this->getDoctrine()->getRepository($this->class)->getTree($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/SupplierActivities.json", $request, $this, $this->getDoctrine(),["parentid"]);
			$templateForms[]=$formUtils->formatForm('supplieractivities', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@ERP/supplieractivities.html.twig', [
  				'controllerName' => 'supplieractivitiesController',
  				'interfaceName' => 'SupplierActivities',
  				'optionSelected' => 'dashboard',
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb('dashboard'),
  				'userData' => $userdata,
					'workactivities' => json_encode($obj),
					'forms' => $templateForms,
  				'lists' => $templateLists
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
     * @Route("/api/ERP/supplieractivities/gettree", name="supplieractivitiesGetTree")
     */
    public function supplieractivitiesGetTree(RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$obj = $this->getDoctrine()->getRepository($this->class)->getTree($this->getUser());
			return new JsonResponse($obj);
		}

		/**
     * @Route("/api/ERP/supplieractivities/dragchange", name="supplieractivitiesDragChange")
     */
    public function supplieractivitiesDragChange(RouterInterface $router,Request $request){

			$id = $request->request->get("id");
			$parent = $request->request->get("parent");
			$position = $request->request->get("position");

			$repository=$this->getDoctrine()->getRepository($this->class);
			$supplieractivity=$repository->findOneBy(["id"=>$id]);
			if($supplieractivity){
				$parent=$repository->findOneBy(["id"=>$parent]);
					$supplieractivity->setParentid($parent);
			//		$supplieractivity->setPosition($position);
					$supplieractivity->setDateupd(new \DateTime());
					$manager=$this->getDoctrine()->getManager();
					$manager->persist($supplieractivity);
			    $manager->flush();
					return new JsonResponse(["result"=>1]);
			}else return new JsonResponse(["result"=>-1]);

		}

		/**
	   * @Route("/{_locale}/ERP/supplieractivities/data/{id}/{action}", name="dataSupplierActivities", defaults={"id"=0, "action"="read"})
	   */
	   public function data($id, $action, Request $request){
	   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	   $this->denyAccessUnlessGranted('ROLE_ADMIN');
	   $template=dirname(__FILE__)."/../Forms/SupplierActivities.json";
	   $utils = new GlobaleFormUtils();
	   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),["parentid"]);
	   return $utils->make($id, $this->class, $action, "formSupplierActivities", "modal");
	  }

}
