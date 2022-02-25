<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleUsersConfig;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleStates;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleStatesUtils;
use App\Modules\Security\Utils\SecurityUtils;

class GlobaleUsersConfigController extends Controller
{
 private $module='Globale';
 private $class=GlobaleUsersConfig::class;

  /**
 * @Route("/api/global/usersconfig/save", name="saveUsersConfig")
 */
 public function save(Request $request)
 {
   // Salva un parámetro de configuración del usuario
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   $usersConfigRepository	 = $this->getDoctrine()->getRepository(GlobaleUsersConfig::class);

   $result 	               = [];
   if ($request->getContent() != null && $request->getContent()!=''){
      $usersConfigData 		 = json_decode($request->getContent(), true);
      if (count($usersConfigData)==4){
        // Usario para el que se define el parámetro
        $user       = $this->getUser();
        // En caso de alta, autor del alta
        $author 	  = $this->getUser();
        // Compañia
        $company    = $this->getUser()->getCompany();
        // Elemento que se quiere parametrizar
        $element    = $usersConfigData['element'];
        // Vista del elemento. Por si el función de la vista el mismo parámetro tiene otro valor
        $view       = ($usersConfigData['view']!=''?$usersConfigData['view']:'Defecto');
        // Atributo/parámetro que se quiere parametrizar dentro del elemento
        $attribute  = $usersConfigData['attribute'];
        // Valor del atributo (si es un array se guarda como json)
        $value      = (is_array($usersConfigData['value'])?$usersConfigData['value']:[$usersConfigData['value']]);

        // Se comrpueba si existe
        $ousersconfig = $usersConfigRepository->findOneBy(["user"=>$user, "company"=>$company, "element"=>$element, "view"=>$view, "attribute"=>$attribute, "active"=>1, "deleted"=>0]);
        if ($ousersconfig!=null){
          // Modifica el parámetro
          $ousersconfig->setValue($value);
          $ousersconfig->setDateupd(new \Datetime());
        }else{
          // Se crea el parámetro
          $ousersconfig = new GlobaleUsersConfig;
          $ousersconfig->setUser($user);
          $ousersconfig->setAuthor($author);
          $ousersconfig->setCompany($company);
          $ousersconfig->setElement($element);
          $ousersconfig->setView($view);
          $ousersconfig->setAttribute($attribute);
          $ousersconfig->setValue($value);
          $ousersconfig->setActive(1);
          $ousersconfig->setDeleted(0);
          $ousersconfig->setDateadd(new \Datetime());
          $ousersconfig->setDateupd(new \Datetime());
        }
        $this->getDoctrine()->getManager()->persist($ousersconfig);
        $this->getDoctrine()->getManager()->flush();
        $this->getDoctrine()->getManager()->clear();
        $result = ["success"=>"1"];
      }
   }
   return new JsonResponse($result);
 }

 /**
  * @Route("/api/global/usersconfig/delete", name="deleteUsersConfig")
  */
  public function delete(Request $request)
  {
    // Borra un parámetro de configuración del usuario
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $usersConfigRepository	 = $this->getDoctrine()->getRepository(GlobaleUsersConfig::class);

    $result 	               = [];
    if ($request->getContent() != null && $request->getContent()!=''){
       $usersConfigData 		 = json_decode($request->getContent(), true);
       if (count($usersConfigData)==4){
         // Usario para el que se define el parámetro
         $user       = $this->getUser();
         // En caso de alta, autor del alta
         $author 	  = $this->getUser();
         // Compañia
         $company    = $this->getUser()->getCompany();
         // Elemento que se quiere parametrizar
         $element    = $usersConfigData['element'];
         // Vista del elemento. Por si el función de la vista el mismo parámetro tiene otro valor
         $view       = ($usersConfigData['view']!=''?$usersConfigData['view']:'Defecto');
         // Atributo/parámetro que se quiere parametrizar dentro del elemento
         $attribute  = $usersConfigData['attribute'];
         // Valor del atributo (si es un array se guarda como json)
         $value      = (is_array($usersConfigData['value'])?$usersConfigData['value']:[$usersConfigData['value']]);

         // Se comrpueba si existe
         $ousersconfig = $usersConfigRepository->findOneBy(["user"=>$user, "company"=>$company, "element"=>$element, "view"=>$view, "attribute"=>$attribute, "active"=>1, "deleted"=>0]);
         if ($ousersconfig!=null){
           // Se elimina
           $ousersconfig->setValue($value);
           $ousersconfig->setDateupd(new \Datetime());
           $this->getDoctrine()->getManager()->remove($ousersconfig);
           $this->getDoctrine()->getManager()->flush();
           $this->getDoctrine()->getManager()->clear();
           $result = ["success"=>"1"];
        }else{
           $result = ["error"=>"No existe el parámetro indicado para su eliminación"];
        }
       }
    }
    return new JsonResponse($result);
  }

}
