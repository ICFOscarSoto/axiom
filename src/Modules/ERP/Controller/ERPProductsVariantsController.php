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
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPProductsUtils;
use App\Modules\ERP\Utils\ERPProductsVariantsUtils;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\ERP\Reports\ERPEan13Reports;

class ERPProductsVariantsController extends Controller
{
  private $class=ERPProductsVariants::class;
  private $utilsClass=ERPProductsVariantsUtils::class;
	private $module='ERP';
  /**
   * @Route("/{_locale}/ERP/productsvariants", name="productsvariants")
   */
  public function index(RouterInterface $router, Request $request)
  {
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
    //$this->denyAccessUnlessGranted('ROLE_ADMIN');
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $utils = new ERPProductsVariantsUtils();
    $templateLists[]=$utils->formatList($this->getUser());
    //$formUtils=new GlobaleFormUtils();
    //$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Products.json", $request, $this, $this->getDoctrine());
    //$templateForms[]=$formUtils->formatForm('products', true, null, $this->class);
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@Globale/genericlist.html.twig', [
        'controllerName' => 'ProductsVariantsController',
        'interfaceName' => 'Variantes',
        'optionSelected' => $request->attributes->get('_route'),
        'menuOptions' =>  $menurepository->formatOptions($userdata),
        'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
        'userData' => $userdata,
        'lists' => $templateLists
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));
  }

  /**
   * @Route("/{_locale}/productsvariants/data/{id}/{action}", name="dataProductsVariants", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request){
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   $template=dirname(__FILE__)."/../Forms/ProductsVariants.json";
   $utils = new GlobaleFormUtils();
   $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
   return $utils->make($id, $this->class, $action, "formProductsVariants", "modal");
  }

  /**
   * @Route("/{_locale}/admin/global/productsvariants/list", name="productsvariantslist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ProductsVariants.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, ProductsVariants::class,[],[],null,"id",$this->getDoctrine());
    return new JsonResponse($return);
  }

}
