<?php

namespace App\Modules\Security\Controller;

use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Translation\TranslatorInterface;

class LoginformAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $router;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        if($request->headers->has('X-AUTH-TOKEN') || $request->headers->has('X-AUTH-CARDID')) return false;

        return ('app_login' === $request->attributes->get('_route') && $request->isMethod('POST'));
            //return $request->headers->has('_csrf_token');
    }

    public function getCredentials(Request $request)
    {
        $email=$request->request->get('email');
        $domain=$request->request->get('domain');
        if(strlen($domain)==0 || $domain=='') $domain='aplicode.com';
        //if no a email, concat company email
        if(strpos($email, "@")==strlen($email)-1) $email = $email.$domain;
        if(strpos($email, "@")===false) $email = $email."@".$domain;

        $credentials = [
            'domain' => $domain,
            'email' => $email,
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];


        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        $company = $this->entityManager->getRepository(GlobaleCompanies::class)->findOneBy(['domain' => $credentials['domain'], 'active'=>1, 'deleted'=>0]);
        if($company!=null)
          $user = $this->entityManager->getRepository(GlobaleUsers::class)->findOneBy(['company'=>$company, 'email' => $credentials['email'], 'active'=>1, 'deleted'=>0]);
        else $user=null;

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Usuario o contrase??a no validos');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->router->generate('some_route'));
		      return new RedirectResponse($this->router->generate('dashboard'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }
}
