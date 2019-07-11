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
     * @Route("/{_locale}/admin/global/categories", name="categories")
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
			$obj = $this->getDoctrine()->getRepository($this->class)->getTree();
			//dump($obj);

  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@ERP/categories.html.twig', [
  				'controllerName' => 'categoriesController',
  				'interfaceName' => 'Categories',
  				'optionSelected' => 'dashboard',
  				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
  				'breadcrumb' =>  $menurepository->formatBreadcrumb('dashboard'),
  				'userData' => $userdata,
					'categories' => json_encode($obj),
  				'lists' => $templateLists
  				]);
  		}
  		return new RedirectResponse($this->router->generate('app_login'));
    }

}
