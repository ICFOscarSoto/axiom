<?php

namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Cloud\Controller\CloudController;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use App\Modules\Security\Utils\SecurityUtils;

use App\Modules\HR\Entity\HRMeetings;
use App\Modules\HR\Entity\HRMeetingsSummoneds;

use App\Modules\HR\Utils\HRMeetingsUtils;



class HRMeetingsController extends Controller
{

	 private $class=HRMeetings::class;
	 private $module='HR';
	 private $utilsClass=HRMeetingsUtils::class;

    /**
		 * @Route("/{_locale}/HR/meetings/data/{id}/{action}", name="dataMeetings", defaults={"id"=0, "action"="read"})
		 */
		 public function dataMeetings($id, $action, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$template=dirname(__FILE__)."/../Forms/Meetings.json";
			$utils = new GlobaleFormUtils();
	    $utilsObj=new $this->utilsClass();
			$repository=$this->getDoctrine()->getRepository($this->class);
			$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[]);
			}
	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "meeting"=>$obj];
			$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
			return $utils->make($id, $this->class, $action, "formmeeting", "full", "@Globale/form.html.twig", "formMeetings");
		}

		/**
		 * @Route("/{_locale}/HR/meeting/form/{id}", name="formMeetings", defaults={"id"=0})
		 */
		 public function formMeetings($id, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$template=dirname(__FILE__)."/../Forms/Meetings.json";
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','HR','Meetings');

			array_push($breadcrumb, $new_breadcrumb);
			$repository=$this->getDoctrine()->getRepository($this->class);
			$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[
						"status_code"=>404,
						"status_text"=>"Objeto no encontrado"
					]);
			}
			$entity_name=$obj?$obj->getName():'';
			return $this->render('@Globale/generictabform.html.twig', array(
							'entity_name' => $entity_name,
							'controllerName' => 'MeetingsController',
							'interfaceName' => 'Reuniones',
							'optionSelected' => 'genericindex',
							'optionSelectedParams' => ["module"=>"HR", "name"=>"Meetings"],
							'menuOptions' =>  $menurepository->formatOptions($userdata),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "caption"=>"Datos reunión", "icon"=>"fa-address-card-o","active"=>true, "route"=>$this->generateUrl("dataMeetings",["id"=>$id])],
												 ["name" => "summoneds", "caption"=>"Summoneds", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("generictablist",["module"=>"HR","name"=>"MeetingsSummoneds","id"=>$id])],
												],
							'include_tab_pre_templates' => ['@HR/send_meetings_summoneds.html.twig'],

							'include_header' => [["type"=>"css", "path"=>"/js/jvectormap/jquery-jvectormap-1.2.2.css"],
																	 ["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
																	 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
							'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
										 		 					 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
		 															 ["type"=>"css", "path"=>"/css/timeline.css"]]

			));
		}

		/**
		* @Route("/{_locale}/HR/meeting/witness/{id}/confirm", name="confirmWitness")
		*/
		public function confirmWitness($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$repository=$this->getDoctrine()->getRepository(HRMeetingsSummoneds::class);
			$obj=$repository->findOneBy(["id"=>$id]);
			if(!$obj) return new JsonResponse(array('result' => -1));
			$obj->setWitness(1);
			$obj->setDateupd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($obj);
			$this->getDoctrine()->getManager()->flush();
			return new JsonResponse(array('result' => 1));
		}

		/**
		* @Route("/{_locale}/HR/meeting/witness/{id}/unconfirm", name="unconfirmWitness")
		*/
		public function unconfirmWitness($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
			$repository=$this->getDoctrine()->getRepository(HRMeetingsSummoneds::class);
			$obj=$repository->findOneBy(["id"=>$id]);
			if(!$obj) return new JsonResponse(array('result' => -1));
			$obj->setWitness(0);
			$obj->setDateupd(new \DateTime());
			$this->getDoctrine()->getManager()->persist($obj);
			$this->getDoctrine()->getManager()->flush();
			return new JsonResponse(array('result' => 1));
		}

		/**
		* @Route("/{_locale}/HR/meeting/sendsummoneds/{id}", name="sendSummoneds")
		*/
		public function sendSummoneds($id,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

			return new JsonResponse(array('result' => 1));
		}



}
