<?php
namespace App\Modules\Globale\Services;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Doctrine\ORM\EntityManager;
use App\Modules\Globale\Entity\GlobaleUsers;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
/**
 * Listener that updates the last activity of the authenticated user
 */
class ActivityListener
{
    protected $tokenStorage;
    protected $sessionStorage;
    protected $entityManager;

    public function __construct(TokenStorage $tokenStorage,EntityManager $entityManager){
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
    * Update the user "lastActivity" on each request
    * @param FilterControllerEvent $event
    */
    public function onCoreController(FilterControllerEvent $event)
    {
        // Check that the current request is a "MASTER_REQUEST"
        // Ignore any sub-request
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }
        // Check token authentication availability
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
            
        }
    }
}
