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

class ERPCategoriesController extends Controller
{
	private $class=ERPCategories::class;
    /**
     * @Route("/{_locale}/ERP/categories", name="categories")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
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
     * @Route("/api/ERP/categories/dragchange", name="categoriesDragChange")
     */
    public function categoriesDragChange(RouterInterface $router,Request $request){

			$id = $request->request->get("id");
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
			}else return new JsonResponse(["result"=>-1]);

		}

		/**
	   * @Route("/{_locale}/ERP/categories/data/{id}/{action}", name="dataCategories", defaults={"id"=0, "action"="read"})
	   */
	   public function data($id, $action, Request $request){
	   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	   $this->denyAccessUnlessGranted('ROLE_ADMIN');
	   $template=dirname(__FILE__)."/../Forms/Categories.json";
	   $utils = new GlobaleFormUtils();
	   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),["parentid"]);
	   return $utils->make($id, $this->class, $action, "formCategories", "modal");
	  }

}
