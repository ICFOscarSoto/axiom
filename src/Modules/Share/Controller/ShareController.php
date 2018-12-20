<?php
namespace App\Modules\Share\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShareController extends Controller{

  /**
  * @Route("/api/shares/save", name="shareSave")
  */
  public function shareSave(RouterInterface $router,Request $request){

    //TODO: Verificar que el usuario es propietario del elemento o lo tiene compartido con permisos de re-comparticion

  }
}
