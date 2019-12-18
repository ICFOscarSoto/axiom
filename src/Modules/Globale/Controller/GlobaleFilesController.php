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
   * @Route("/api/files/uploadTemp/{type}", name="uploadTemp", defaults={"type"="Other"})
   */
  public function uploadTemp($type, RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    //Check if filespace in disk quota
    $company=$this->getUser()->getCompany();
    $diskUsage=$company->getDiskUsages();
    if($diskUsage[0]->getDiskspace()-$diskUsage[0]->getDiskusage()<=0)  return new JsonResponse(["result"=>-10]);

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $uploadDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$this->getUser()->getId().DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR;
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
