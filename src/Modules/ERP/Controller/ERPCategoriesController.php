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
use App\Modules\ERP\Entity\ERPCategories;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPCategoriesUtils;
use App\Modules\Security\Utils\SecurityUtils;


class ERPCategoriesController extends Controller
{
	  private $class=ERPCategories::class;
	  private $module='ERP';

    /**
     * @Route("/{_locale}/ERP/categories", name="categories")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
  		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new ERPCategoriesUtils();
  		$templateLists[]=$utils->formatList($this->getUser());
			$obj = $this->getDoctrine()->getRepository($this->class)->getTree($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Categories.json", $request, $this, $this->getDoctrine(),["parentid"]);
			$templateForms[]=$formUtils->formatForm('categories', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@ERP/categories.html.twig', [
  				'controllerName' => 'categoriesController',
  				'interfaceName' => 'Categories',
  				'optionSelected' => 'dashboard',
  				'menuOptions' =>  $menurepository->formatOptions($userdata),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb('dashboard'),
  				'userData' => $userdata,
					'categories' => json_encode($obj),
					'forms' => $templateForms,
  				'lists' => $templateLists
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
     * @Route("/api/ERP/categories/gettree", name="categoriesGetTree")
     */
    public function categoriesGetTree(RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$obj = $this->getDoctrine()->getRepository($this->class)->getTree($this->getUser());
			return new JsonResponse($obj);
		}

		/**
     * @Route("/api/ERP/categories/dragchange", name="categoriesDragChange")
     */

		 // OSCAR: comento el m??todo para que no puedan cambiar categor??as de posici??n
    public function categoriesDragChange(RouterInterface $router,Request $request){

		/*	$id = $request->request->get("id");
			$parent = $request->request->get("parent");
			$position = $request->request->get("position");

			$repository=$this->getDoctrine()->getRepository($this->class);
			$category=$repository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
			if($category){
				$parent=$repository->findOneBy(["id"=>$parent, "company"=>$this->getUser()->getCompany()]);
					$category->setParentid($parent);
					$category->setPosition($position);
					$category->setDateupd(new \DateTime());
					$manager=$this->getDoctrine()->getManager();
					$manager->persist($category);
			    $manager->flush();
					return new JsonResponse(["result"=>1]);
			}else */return new JsonResponse(["result"=>-1]);

		}

		/**
	   * @Route("/{_locale}/ERP/categories/data/{id}/{parentid_id}/{action}", name="dataCategories", defaults={"id"=0, "parentid_id"=0, "action"="read"})
	   */
	   public function data($id, $parentid_id, $action, Request $request){
	   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	   $template=dirname(__FILE__)."/../Forms/Categories.json";
	   $utils = new GlobaleFormUtils();
		 $utilsObj=new ERPCategoriesUtils();
		 $categoriesRepository=$this->getDoctrine()->getRepository(ERPCategories::class);
		 $parent=$categoriesRepository->findOneBy(['id'=>$parentid_id, 'active'=>1, 'deleted'=>0]);
		 $params= ["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "parentid_id"=>$parent];
		 if ($parentid_id!=0)  {
		   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
       method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
 			 method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		 }
		 else $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
	   return $utils->make($id, $this->class, $action, "formCategories", "modal");
	  }

		/**
		 * @Route("/{_locale}/ERP/categories/new/{id}/{action}", name="newCategories", defaults={"id"=0, "action"="read"})
		 */
		 public function new($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $template=dirname(__FILE__)."/../Forms/Categories.json";
		 $utils = new GlobaleFormUtils();
		 $utilsObj=new ERPCategoriesUtils();
		 $categoriesRepository=$this->getDoctrine()->getRepository(ERPCategories::class);
		 $parent=$categoriesRepository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
		 $params= ["doctrine"=>$this->getDoctrine(), "user"=>$this->getUser(), "parentid_id"=>$parent];
		 if ($id!=0)  {
				$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),
			 method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
			 method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		 }
		 else $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make(0, $this->class, $action, "formCategories", "modal");
		}


}
