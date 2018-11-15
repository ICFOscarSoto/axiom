<?php

namespace App\Controller\Globale;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use App\Entity\Globale\MenuOptions;

class DashboardController extends Controller
{
    /**
     * @Route("/{_locale}/admin/dashboard", name="dashboard")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();

		$locale = $request->getLocale();
		$this->router = $router;

		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);

		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('Globale/dashboard.html.twig', [
				'controllerName' => 'FadashboardController',
				'interfaceName' => 'Panel de control',
        'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
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
