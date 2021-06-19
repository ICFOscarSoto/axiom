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
use App\Modules\Globale\Entity\GlobaleUsersCards;
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
		$session = $request->getSession();

		$type=$request->query->get('login-type', $session->get('login-type', 'onlypass'));


		switch($type){
			case 'card':
				$template='@Globale/login_card.html.twig';
			break;
			default:
				$template='@Globale/login.html.twig';
			break;
		}
		//$session->set('login-type', $type);

		if($company!=null && $domain!="aplicode.com"){
			$logo=$this->generateUrl('getImage', array('type'=>'companydark' ,'size'=>'medium','id'=>$company->getId()));
			return $this->render($template, ['cfgloginhtmlbottom'=>$company->getCfgloginhtmlbottom(), 'last_username' => $lastUsername, 'domain'=>$domain, 'type'=> 'hidden', 'error' => $error, 'logo' => $logo]);
		}else{
			$logo=$this->generateUrl('getImage', array('type'=>'companydark' ,'size'=>'medium','id'=>1));
			return $this->render($template, ['','last_username' => $lastUsername, 'domain'=>($domain!="aplicode.com")?$domain:"", 'type'=> 'text', 'error' => $error,  'logo' => $logo]);
		}
	}

	/**
		 * @Route("/api/token/get", name="getToken")
		 */
	public function getToken(Request $request)
	{
		$companyRepository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
		$userRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$userCardsRepository=$this->getDoctrine()->getRepository(GlobaleUsersCards::class);
		$user=null;
		$company=null;
		if($request->request->get("nfctag")==null){
			//Classic style domain, user and password
			$domain = $request->request->get("domain");
			if($domain==NULL)
				$domain = $this->getDomain($request->getUri());
			$username = $request->request->get("username");
		 	$company = $companyRepository->findOneBy(["domain" => $domain, "active"=>1, "deleted"=>0]);
			if($company)
		 		$user = $userRepository->findOneBy(["email" => $username, "company" => $company, "active"=>1, "deleted"=>0]);
			if($user){
				$password = $request->request->get("password");
				$passwordEncoder = $this->container->get('security.password_encoder');
				if(!$passwordEncoder->isPasswordValid($user, $password)){
					return new JsonResponse(['token'=>'']);
				}
			}

		}else{
			//NFC read by mobile device
			$userCard=$userCardsRepository->findOneBy(["cardid" => $request->request->get("nfctag"), "active"=>1, "deleted"=>0]);
			if($userCard && $userCard->getUserasigned()->getActive() && !$userCard->getUserasigned()->getDeleted()){
				$user = $userCard->getUserasigned();
				$company = $user->getCompany();
			}
		}

		if($user){


				if($user->getApiToken()!=NULL){
					$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
					$worker=$workersrepository->findOneBy(["user"=>$user]);

					return new JsonResponse(['id'=>$user->getId(),
																	 'workerId'=>$worker!=null?$worker->getId():null,
																	 'clockId'=>$worker!=null?$worker->getClockCode():null,
																	 'companyId'=>$company->getId(),
																	 'domain'=>$company->getDomain(),
																	 'name'=>$user->getName(),
																	 'lastname'=>$user->getLastname(),
																	 'allowRemoteClock'=>$worker!=null?$worker->getAllowremoteclock():null,
																	 'token'=>$user->getApiToken(),
																	 'labelPrinter'=>$user->getDefaultlabelprinter()?$user->getDefaultlabelprinter()->getId():-1,
																	 'printownlabel'=>$user->getAlwaysprintownlabel()
																 ]);
				}else{
						$token = openssl_random_pseudo_bytes(200);
						$token = bin2hex($token);
						$token .= md5(uniqid(time(), true));
						$user->setApiToken($token);
						$em = $this->getDoctrine()->getManager();
						$em->persist($user);
						$em->flush();
						return new JsonResponse(['id'=>$user->getId(),
																		 'workerId'=>$worker->getId(),
																		 'clockId'=>$worker->getClockCode(),
																		 'companyId'=>$company->getId(),
																		 'domain'=>$company->getDomain(),
																		 'name'=>$user->getName(),
																		 'lastname'=>$user->getLastname(),
																		 'allowRemoteClock'=>$worker->getAllowremoteclock(),
																		 'token'=>$token,
																	 	 'labelPrinter'=>$user->getDefaultlabelprinter()?$user->getDefaultlabelprinter()->getId():-1,
																	 	 'printownlabel'=>$user->getAlwaysprintownlabel()
																 ]);
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
