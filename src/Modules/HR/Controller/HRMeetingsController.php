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
use App\Modules\HR\Entity\HRWorkers;

use App\Modules\HR\Utils\HRMeetingsUtils;
use App\Modules\HR\Utils\HRMeetingsSummonedsUtils;


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
			$repositorySummoneds=$this->getDoctrine()->getRepository(HRMeetingsSummoneds::class);
			$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			$summoneds=[];
			if($obj){
				$summoneds=$repositorySummoneds->findBy(['meeting'=>$obj, 'deleted'=>0]);
			}
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[]);
			}
			$infos=[];
			$status=0;
			if($id==0){
				$infos[]="Establezca un <b>nombre</b> para la reunión una <b>fecha y hora de celebración</b> y un <b>lugar</b> (físico o virtual por ejemplo un canal de Discord, videollamada, etc.)";
				$infos[]="Es recomendable redactar un <b>'Orden del día'</b> para que los participantes puedan prepararse previamente los temas a tratar y optimizar el tiempo de reunión";
			}else{
				if(count($summoneds)==0){
					$infos[]="Añada participantes a la reunion en la pestaña <b>Participantes</b> o haciendo click <a onclick=\"$('.nav-tabs a[href=\'#tab-body-summoneds\']').tab('show');
				\">aquí</a>";
					$status=1;
				}else{
					if($obj->getStartdate()==null){
						$infos[]="Inicie la reunión cuando este listo haciendo click en <b>Iniciar Reunion</b>";
						$status=2;
					}else{
						if($obj->getEnddate()==null){
							$status=3;
						}else{
							$status=4;
						}
					}

				}
			}

	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "obj"=>$obj, "summoneds"=>$summoneds, "status"=>$status];
			$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
			return $utils->make($id, $this->class, $action, "formmeeting", "full", "@Globale/form.html.twig", "formMeetings",null,[],["infos"=>$infos,"meeting_status"=>$status]);
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
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fas fa-plus"];
			$breadcrumb=$menurepository->formatBreadcrumb('genericindex','HR','Meetings');
			$repositorySummoneds=$this->getDoctrine()->getRepository(HRMeetingsSummoneds::class);
			$repositoryWorkers=$this->getDoctrine()->getRepository(HRWorkers::class);

			array_push($breadcrumb, $new_breadcrumb);
			$repository=$this->getDoctrine()->getRepository($this->class);
			$obj = $repository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
			if($id!=0 && $obj==null){
					return $this->render('@Globale/notfound.html.twig',[
						"status_code"=>404,
						"status_text"=>"Objeto no encontrado"
					]);
			}
			if(!SecurityUtils::isAdmin($this->getUser(), $this->getDoctrine())){
				if($obj->getAuthor()!=$this->getUser()){
					$worker=$repositoryWorkers->findOneBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "user"=>$this->getUser()]);
					if(!$worker) return $this->render('@Globale/notfound.html.twig',[
						"status_code"=>401,
						"status_text"=>"No tiene permisos para acceder a esta reunión"
					]);
					$summoneds=$repositorySummoneds->findBy(['worker'=>$worker,'meeting'=>$obj, 'deleted'=>0]);
					if(!$summoneds){
						return $this->render('@Globale/notfound.html.twig',[
							"status_code"=>401,
							"status_text"=>"No tiene permisos para acceder a esta reunión"
						]);
					}
				}
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
												 ["name" => "summoneds", "caption"=>"Summoneds", "icon"=>"fa-address-card-o","route"=>$this->generateUrl("listSummoneds",["id"=>$id])],
												 ["name" => "files", "icon"=>"fa fa-cloud", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"meetings", "module"=>"HR"])]
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
		 * @Route("/{_locale}/HR/meeting/{id}/summoneds", name="listSummoneds")
		 */
		public function listSummoneds($id,RouterInterface $router,Request $request)
		{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;

		$repository=$this->getDoctrine()->getRepository(HRMeetings::class);
		$repositorySummoneds=$this->getDoctrine()->getRepository(HRMeetingsSummoneds::class);
		$obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$id, "deleted"=>0]);
		$summoneds=[];
		if($obj){
			$summoneds=$repositorySummoneds->findBy(['meeting'=>$obj, 'deleted'=>0]);
		}
		$infos=[];
		foreach($summoneds as $summon){
			if(!$summon->getSended() && $obj->getStartdate()==null){
				$infos[]="Hay participantes a los que no se le ha enviado la convocatoria de la reunión, consiedere enviarla.";
				break;
			}
		}

		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$utils = new HRMeetingsUtils();

		$templateLists=$utils->formatSummonedsList($id);
		$formUtils=new GlobaleFormUtils();

		$utilsObj=new HRMeetingsSummonedsUtils();
		$params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser(), "parent"=>$obj];
		$formUtils->initialize($this->getUser(), new HRMeetingsSummoneds(), dirname(__FILE__)."/../Forms/MeetingsSummoneds.json", $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
		$templateForms[]=$formUtils->formatForm('MeetingsSummoneds', true, $id, HRMeetingsSummoneds::class);
		if($obj && $obj->getStartdate()!=null){
			unset($templateLists["topButtons"][1]);
		}
		if($obj && $obj->getEnddate()!=null){
			$templateLists["fieldButtons"]=[];
			$templateLists["topButtons"]=[];
		}


			return $this->render('@Globale/list.html.twig', [
				'id' => $id,
				'infos' => $infos,
				'listConstructor' => $templateLists,
				'forms' => $templateForms,
				'userData' => $userdata,
				]);

		return new RedirectResponse($this->router->generate('app_login'));
		}

		/**
		 * @Route("/{_locale}/HR/meetingsummoneds/{id}/list", name="meetingsummonedslist")
		 */
		public function meetingsummonedslist($id, RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$repositorySummoneds = $manager->getRepository(HRMeetingsSummoneds::class);
			$listUtils=new GlobaleListUtils();
			$meeting=$repository->findBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "id"=>$id]);
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/MeetingsSummoneds.json"),true);
			$return=$listUtils->getRecords($user,$repositorySummoneds,$request,$manager,$listFields, HRMeetingsSummoneds::class,[["type"=>"and", "column"=>"meeting", "value"=>$meeting]]);
			return new JsonResponse($return);
		}

		/**
		 * @Route("/{_locale}/HR/meeting/list", name="meetinglist")
		 */
		public function meetinglist(RouterInterface $router,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$repositoryWorkers = $manager->getRepository(HRWorkers::class);
			$worker=$repositoryWorkers->findOneBy(["company"=>$this->getUser()->getCompany(), "deleted"=>0, "user"=>$this->getUser()]);
			$andWorker='1=1';
			if(!SecurityUtils::isAdmin($this->getUser(), $this->getDoctrine())){
				if($worker) $andWorker='ms.worker_id = '.$worker->getId().' OR p.author_id = '.$this->getUser()->getId();
					else $andWorker='p.author_id = '.$this->getUser()->getId();
			}
			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Meetings.json"),true);
			$return=$listUtils->getRecordsSQL($user,$repository,$request,$manager,$listFields,HRMeetings::class,['DISTINCT(p.id)'=>'id','p.name'=>'name','p.meetingdate'=>'meetingdate','p.place'=>'place','concat(author.name," ",author.lastname)'=>'author__name_o_author__lastname'],
																																	'hrmeetings p
																																	LEFT JOIN globale_users author ON p.author_id = author.id
																																	LEFT JOIN hrmeetings_summoneds ms ON ms.meeting_id = p.id',
																																	'p.active=1 AND p.deleted=0 AND ms.active=1 AND ms.deleted=0 AND p.company_id='.$this->getUser()->getCompany()->getId()." AND (".$andWorker.")"
																																	);
			return new JsonResponse($return);
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
		* @Route("/{_locale}/HR/meeting/sendsummoneds/{idparent}/{id}", name="sendSummoneds", defaults={"id"=0})
		*/
		public function sendSummoneds($id,$idparent,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

			$repository=$this->getDoctrine()->getRepository(HRMeetings::class);
			$repositorySummoneds=$this->getDoctrine()->getRepository(HRMeetingsSummoneds::class);
			$obj=$repository->findOneBy(["company"=>$this->getUser()->getCompany(), "id"=>$idparent, "deleted"=>0]);
			if(!$obj) return new JsonResponse(array('result' => -1));
			$ids=$request->request->get('ids');
			if($id!=0){
				$ids=$id;
			}
			$ids=$request->request->get('ids');
			$ids=explode(",",$ids);
			foreach($ids as $id){
				$summoned=$repositorySummoneds->findOneBy(['meeting'=>$obj, 'id'=>$id, 'deleted'=>0]);
				if(!$summoned) continue;
				if(!$summoned->getWorker()) continue;
				$now=new \DateTime();
				//if($now->getTimestamp()-$summoned->getDateupd()->getTimestamp()>2) continue;
				$summoned->setSended(1);
				$summoned->setDateupd(new \DateTime());
				$this->getDoctrine()->getManager()->persist($summoned);
				$this->getDoctrine()->getManager()->flush();
				$msg="Has sido convocado a la reunión: **".$obj->getName()."** organizada por **".$obj->getAuthor()->getName()." ".$obj->getAuthor()->getLastname()."** \nTendra lugar el **".$obj->getMeetingdate()->format('d/m/Y H:i')."** en **".$obj->getPlace()."**";
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$summoned->getWorker()->getUser()->getDiscordchannel().'&msg='.urlencode($msg));
				$msg="\n\nMás info en: \n".'https://axiom.ferreteriacampollano.com/es/HR/meeting/form/'.$obj->getId();
				file_get_contents('https://icfbot.ferreteriacampollano.com/message.php?channel='.$summoned->getWorker()->getUser()->getDiscordchannel().'&msg='.urlencode($msg));

			}
			return new JsonResponse(array('result' => 1));
		}




}
