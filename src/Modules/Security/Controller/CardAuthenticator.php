<?php
namespace App\Modules\Security\Controller;

use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleUsersCards;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleWorkstations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Cookie;

class CardAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $em;
    private $router;
    //private $session;

    public function __construct(EntityManagerInterface $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

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
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return $request->query->has('X-AUTH-CARDID');
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        return [
            'cardid' => $request->query->get('X-AUTH-CARDID'),
            'domain' => $request->query->get('X-AUTH-DOMAIN'),
            'deviceid' => $request->query->get('X-AUTH-DEVICEID'),
            'login-type'=>$request->query->get('login-type','card'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $cardid   = $credentials['cardid'];
        $domain   = $credentials['domain'];
        $deviceid = $credentials['deviceid'];


        if (null == $cardid) {
            return;
        }
        // if a User object, checkCredentials() is called
        $company=$this->em->getRepository(GlobaleCompanies::class)
            ->findOneBy(['domain' => $domain, 'active'=>1, 'deleted'=>0]);

        if($company==null) return null;

        $method = 'aes-256-cbc';
        $psk = $company->getDeviceuser().$company->getDevicepassword();
        $key = substr(hash('sha256', $psk, true), 0, 32);
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $decryptedCardId = openssl_decrypt(base64_decode($cardid), $method, $key, OPENSSL_RAW_DATA, $iv);

        $card=$this->em->getRepository(GlobaleUsersCards::class)
            ->findOneBy(['cardid' => $decryptedCardId, 'active'=>1, 'deleted'=>0]);
        if($card!=null){
          $user=$card->getUserasigned();
          if($user->getCompany()!=$company) $user=null;
        }else $user=null;

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case
        $cardid = $credentials['cardid'];
        $domain = $credentials['domain'];

        if ($cardid=='') return false;
        // return true to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        //Check if workstation is registered
        $workstationRepository=$this->em->getRepository(GlobaleWorkstations::class);
        $deviceId=$request->query->get('X-AUTH-DEVICEID');
        $deviceName=$request->query->get('X-AUTH-DEVICENAME','');
        $workstation=$workstationRepository->findOneBy(['deviceid'=>$deviceId, 'active'=>1, 'deleted'=>0]);
        if($deviceId){
          if(!$workstation){
            $company=$this->em->getRepository(GlobaleCompanies::class)
                ->findOneBy(['domain' => $request->query->get('X-AUTH-DOMAIN'), 'active'=>1, 'deleted'=>0]);
            $workstation=new GlobaleWorkstations();
            $workstation->setCompany($company);
            $workstation->setDeviceId($deviceId);
            $workstation->setDateadd(new \DateTime());
            $workstation->setActive(1);
            $workstation->setDeleted(0);
          }
          $workstation->setName($deviceName);
          $workstation->setDateupd(new \DateTime());
          $this->em->persist($workstation);
          $this->em->flush();
        }

        $request->getSession()->set('login-type', $request->query->get('login-type','card'));
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(["result"=>-1]);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
