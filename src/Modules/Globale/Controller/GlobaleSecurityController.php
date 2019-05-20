<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Controller\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GlobaleSecurityController extends Controller
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
		$companyRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
		$company = $companyRepository->findOneBy(["domain" => $domain]);
		if($company!=null)
			return $this->render('@Globale/login.html.twig', ['last_username' => $lastUsername, 'domain'=>$domain, 'type'=> 'hidden', 'error' => $error, 'logo' => $this->generateUrl('getCompanyImage', array('id'=>$company->getId()))]);
		else return $this->render('@Globale/login.html.twig', ['last_username' => $lastUsername, 'domain'=>$domain, 'type'=> 'text', 'error' => $error,  'logo' => $this->generateUrl('getCompanyImage', array('id'=>1))]);
	}

	/**
		 * @Route("/api/token/get", name="getToken")
		 */
	public function getToken(Request $request)
	{
		$domain = $request->request->get("domain");
		if($domain==NULL)
			$domain = $this->getDomain($request->getUri());
		$username = $request->request->get("username");
		$companyRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
	 	$company = $companyRepository->findOneBy(["domain" => $domain]);
		$userRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
	 	$user = $userRepository->findOneBy(["email" => $username, "company" => $company]);
		if($user){
			$password = $request->request->get("password");
			$passwordEncoder = $this->container->get('security.password_encoder');
			if($passwordEncoder->isPasswordValid($user, $password)){
				if($user->getApiToken()!=NULL){
					$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
					$worker=$workersrepository->findOneBy(["user"=>$user]);
					if($worker!==NULL) $worker=$worker->getId();
					return new JsonResponse(['id'=>$user->getId(),'workerId'=>$worker,'companyId'=>$company->getId(),'name'=>$user->getName(),'lastname'=>$user->getLastname(),'token'=>$user->getApiToken()]);
				}else{
						$token = openssl_random_pseudo_bytes(200);
						$token = bin2hex($token);
						$token .= md5(uniqid(time(), true));
						$user->setApiToken($token);
						$em = $this->getDoctrine()->getManager();
						$em->persist($user);
						$em->flush();
						return new JsonResponse(['token'=>$token]);
					}

			}
		}else{
			return new JsonResponse(['token'=>'']);
		}
	}



	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		// some logic to determine the $locale
		$request->setLocale($locale);
	}
}
