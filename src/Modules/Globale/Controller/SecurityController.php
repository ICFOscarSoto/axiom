<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use App\Modules\Globale\Entity\Companies;

class SecurityController extends Controller
{

	function getDomain($url)
	{
	  $pieces = parse_url($url);
	  $domain = isset($pieces['host']) ? $pieces['host'] : '';
	  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
	    return $regs['domain'];
	  }
	  return false;
	}

	/**
     * @Route("/{_locale}/login", name="app_login")
     */
	public function login(Request $request, AuthenticationUtils $authenticationUtils)
	{
		 $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
		$locale = $request->getLocale();
		$domain = $this->getDomain($request->getUri());
		$companyRepository=$this->getDoctrine()->getRepository(Companies::class);
		$company = $companyRepository->findOneBy(["domain" => $domain]);
		if($company!=null)
			return $this->render('@Globale/login.html.twig', ['last_username' => $lastUsername, 'domain'=>$domain, 'type'=> 'hidden', 'error' => $error]);
		else return $this->render('@Globale/login.html.twig', ['last_username' => $lastUsername, 'domain'=>$domain, 'type'=> 'text', 'error' => $error]);
	}


	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();

		// some logic to determine the $locale
		$request->setLocale($locale);
	}
}
