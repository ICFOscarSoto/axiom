<?php

namespace App\Modules\Globale\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleWidgets;
use App\Modules\Globale\Entity\GlobaleUsersWidgets;

class GlobaleDashboardController extends Controller
{
    /**
     * @Route("/{_locale}/admin/dashboard", name="dashboard")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());

		$locale = $request->getLocale();
		$this->router = $router;

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      //get user widgets
      $widgetsRepository=$this->getDoctrine()->getRepository(GlobaleWidgets::class);
      $usersWidgetsRepository=$this->getDoctrine()->getRepository(GlobaleUsersWidgets::class);
      $widgetConfigRepository=$this->getDoctrine()->getRepository(GlobaleWidgets::class);
      //Select Widget Catalog for this user
      $widgetsCatalog=$widgetConfigRepository->findAll();

      $userWidgets=$usersWidgetsRepository->findBy(["user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
      foreach($userWidgets as $key=>$widget){
        //Charge config of the widget
        $widgetConfigRepository=$this->getDoctrine()->getRepository("\App\Widgets\Entity\Widgets".$widget->getWidget()->getName());
        $config=$widgetConfigRepository->findOneBy(["userwidget"=>$widget,"active"=>1,"deleted"=>0]);
        $userWidgets[$key]->settings=$config;

      }
			return $this->render('@Globale/dashboard.html.twig', [
				'controllerName' => 'FadashboardController',
				'interfaceName' => 'Panel de control',
        'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
        'widgets' => $userWidgets,
        'widgetsCatalog' => $widgetsCatalog
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }


	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		// some logic to determine the $locale
		$request->setLocale($locale);
	}


}
