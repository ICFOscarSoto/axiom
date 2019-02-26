<?php

namespace App\Modules\Globale\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class GlobaleFilesController extends Controller
{


  /**
   * @Route("/api/files/uploadTemp", name="uploadTemp")
   */
  public function uploadTemp(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $uploadDir=$this->get('kernel')->getRootDir() . '/../public/temp/'.$this->getUser()->getId().'/';
      $output = array('uploaded' => false);
         $file = $request->files->get('file');
         //$fileName = md5(uniqid()).'.'.$file->guessExtension();
         $fileName = $file->getClientOriginalName();
         if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
             mkdir($uploadDir, 0775, true);
         }
         if ($file->move($uploadDir, $fileName)) {
            $output['uploaded'] = true;
            $output['fileName'] = $fileName;
         }
         return new JsonResponse($output);
    }else return new RedirectResponse($this->router->generate('app_login'));
  }

}
