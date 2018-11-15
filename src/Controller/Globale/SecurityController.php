<?php

namespace App\Controller\Globale;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class SecurityController extends Controller
{
	/**
     * @Route("/{_locale}/login", name="app_login")
     */
	public function login(Request $request, AuthenticationUtils $authenticationUtils)
	{
		 $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
		$locale = $request->getLocale();
		return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
	}
	
	
	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();

		// some logic to determine the $locale
		$request->setLocale($locale);
	}
}
