<?php

namespace App\Modules\Globale\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/")
 */
class GlobaleDefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(RouterInterface $router,Request $request): Response
    {
		$this->router = $router;
        return new RedirectResponse($this->router->generate('dashboard'));
    }

	 /**
     * @Route("/{_locale}/admin", name="indexAdmin")
     */
    public function indexAdmin(RouterInterface $router,Request $request): Response
    {
		$this->router = $router;
        return new RedirectResponse($this->router->generate('dashboard'));
    }

		 /**
     * @Route("/{_locale}/admin/", name="indexAdmin2")
     */
    public function indexAdmin2(RouterInterface $router,Request $request): Response
    {
		$this->router = $router;
        return new RedirectResponse($this->router->generate('dashboard'));
    }

}
