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
use App\Modules\ERP\Entity\ERPInventory;
use App\Modules\ERP\Entity\ERPInventoryLines;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPInventoryUtils;
use App\Modules\Security\Utils\SecurityUtils;

class ERPInventoryController extends Controller
{
	private $class=ERPInventory::class;
	private $module='ERP';
	private $utilsClass=ERPInventoryUtils::class;

	/**
	 * @Route("/{_locale}/ERP/inventory", name="inventory")
	 */
	public function index(RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$utils = new $this->utilsClass();
		$templateLists[]=$utils->formatList($this->getUser());
		$formUtils=new GlobaleFormUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Inventory.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils->formatForm('stores', true, null, $this->class);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'inventoryController',
				'interfaceName' => 'Inventory',
				'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists,
				'forms' => $templateForms
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}



			/**
			 * @Route("/{_locale}/ERP/inventory/form/{id}", name="formERPInventory", defaults={"id"=0})
			 */
			public function formERPInventory($id,Request $request)
			{
			  $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			  if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			  $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			  $locale = $request->getLocale();
			  $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			  $repository=$this->getDoctrine()->getRepository($this->class);
			  $obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
				$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-plus"];
				$breadcrumb=$menurepository->formatBreadcrumb('inventory');
				array_push($breadcrumb,$new_breadcrumb);
				$linesrepository=$this->getDoctrine()->getRepository(ERPInventoryLines::class);
				$lines=$linesrepository->findBy(["inventory"=>$id, "active"=>1, "deleted"=>2]);
				return $this->render('@ERP/inventoryLinesList.html.twig', [
					'controllerName' => 'inventoryController',
					'interfaceName' => 'Inventory',
					'optionSelected' => $request->attributes->get('_route'),
					'menuOptions' =>  $menurepository->formatOptions($userdata),
					'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
					'userData' => $userdata,
					'inventory' => $obj,
					'linesInventory' => $lines
				]);
			  }

}
