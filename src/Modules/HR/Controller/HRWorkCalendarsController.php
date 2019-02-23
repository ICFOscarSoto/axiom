<?php

namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Entity\Currencies;
use App\Modules\Globale\Entity\Companies;
use App\Modules\Globale\Entity\Users;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRWorkersUtils;
use App\Modules\HR\Utils\HRWorkCalendarsUtils;
use App\Modules\HR\Entity\HRWorkCalendars;


class HRWorkCalendarsController extends Controller
{

	 private $class=HRWorkCalendars::class;
   private $UtilsClass=HRWorkCalendarsUtils::class;

   /**
    * @Route("/{_locale}/HR/workcalendars", name="workcalendars")
    */
   public function workcalendars(RouterInterface $router,Request $request)
   {
   $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
   //$this->denyAccessUnlessGranted('ROLE_ADMIN');
   $userdata=$this->getUser()->getTemplateData();
   $locale = $request->getLocale();
   $this->router = $router;
   $menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
   $utils = new HRWorkCalendarsUtils();
   $formUtils=new FormUtils();
   $formUtils->initialize($this->getUser(), new HRWorkCalendars(), dirname(__FILE__)."/../Lists/WorkCalendars.json", $request, $this, $this->getDoctrine());
   $templateLists[]=$utils->formatList($this->getUser());
   //$templateForms[]=$formUtils->formatForm('workcalendars', true);
   $templateForms[]=$formUtils->formatForm('workcalendars', true);
   if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
     return $this->render('@Globale/genericlist.html.twig', [
       'controllerName' => 'HRController',
       'interfaceName' => 'Calendarios laborales',
       'optionSelected' => $request->attributes->get('_route'),
       'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
       'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
       'userData' => $userdata,
       'lists' => $templateLists,
       'forms' => $templateForms
       ]);
   }
   return new RedirectResponse($this->router->generate('app_login'));
   }

   /**
    * @Route("/api/HR/workcalendars/list", name="workcalendarslist")
    */
   public function workcalendarslist(RouterInterface $router,Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $user = $this->getUser();
     $locale = $request->getLocale();
     $this->router = $router;
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository(HRWorkCalendars::class);
     $listUtils=new ListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkCalendars.json"),true);
     $return=$listUtils->getRecords($repository,$request,$manager,$listFields, HRWorkCalendars::class);
     return new JsonResponse($return);
   }

   /**
   * @Route("/{_locale}/HR/workcalendars/data/{id}/{action}", name="workCalendarsForm", defaults={"id"=0, "action"="read"})
   */
   public function workCalendarsForm($id, $action, Request $request){
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $template=dirname(__FILE__)."/../Forms/WorkCalendars.json";
     if(!$id) $obj=new $this->class();
       else{
         $repository = $this->getDoctrine()->getRepository($this->class);
         $obj=$repository->find($id);
         if($obj===NULL) $obj=new $this->class();
       }
     $utils = new FormUtils();
     $utils->initialize($this->getUser(), $obj, $template, $request, $this, $this->getDoctrine());
		 $form=$utils->createFromEntity2(false)->getForm();
     switch($action){
        case 'save':
          $result=$utils->proccess($form,$obj);
          return new JsonResponse(array('result' => $result));
        break;
        case 'read':
          return $this->render('@Globale/form.html.twig', [
            'formConstructor' =>["form" => $form->createView(), "post"=>$this->generateUrl($request->get('_route'),["id"=>$id, "action"=>"save"]) ,"template" => json_decode(file_get_contents ($template),true)]
            ]);
        break;
     }
   }

 	/**
 	* @Route("/{_locale}/HR/workcalendar/{id}/disable", name="disableWorkCalendar")
 	*/
 	public function disableWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new EntityUtils();
 		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}
 	/**
 	* @Route("/{_locale}/HR/workcalendar/{id}/enable", name="enableWorkCalendar")
 	*/
 	public function enableWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new EntityUtils();
 		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}
 	/**
 	* @Route("/{_locale}/HR/workcalendar/{id}/delete", name="deleteWorkCalendar")
 	*/
 	public function deleteWorkCalendar($id){
 		$this->denyAccessUnlessGranted('ROLE_ADMIN');
 		$entityUtils=new EntityUtils();
 		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
 		return new JsonResponse(array('result' => $result));
 	}

}
