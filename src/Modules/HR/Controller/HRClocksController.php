<?php

namespace App\Modules\HR\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCurrencies;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Utils\HRClocksUtils;
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRWorkers;


class HRClocksController extends Controller
{

	 private $class=HRClocks::class;
   private $utilsClass=HRClocksUtils::class;

    /**
     * @Route("/{_locale}/HR/clocks", name="clocks")
     */
     public function clocks(RouterInterface $router,Request $request)
     {
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     //$this->denyAccessUnlessGranted('ROLE_ADMIN');
     $userdata=$this->getUser()->getTemplateData();
     $locale = $request->getLocale();
     $this->router = $router;
     $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
     $utils = new $this->utilsClass();
     $formUtils=new GlobaleFormUtils();
     $formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Clocks.json", $request, $this, $this->getDoctrine());
     $templateLists[]=$utils->formatList($this->getUser());
     $templateForms[]=$formUtils->formatForm('clocks', true, null, $this->class);
     if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
       return $this->render('@Globale/genericlist.html.twig', [
         'controllerName' => 'HRClocksController',
         'interfaceName' => 'Fichajes',
         'optionSelected' => "workers",
         'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
         'breadcrumb' =>  "workers",
         'userData' => $userdata,
         'lists' => $templateLists,
         'forms' => $templateForms
         ]);
     } return new RedirectResponse($this->router->generate('app_login'));
     }

		 /**
      * @Route("/{_locale}/HR/clocks/status", name="clocksStatus")
      */
      public function status(RouterInterface $router,Request $request)
      {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      //$this->denyAccessUnlessGranted('ROLE_ADMIN');
      $userdata=$this->getUser()->getTemplateData();
      $locale = $request->getLocale();
      $this->router = $router;
      $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
      $utils = new $this->utilsClass();
			$repository = $this->getDoctrine()->getManager()->getRepository($this->class);
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        return $this->render('@HR/workersclocks.html.twig', [
          'controllerName' => 'HRClocksController',
          'interfaceName' => 'Estado Fichaje',
          'optionSelected' => "workers",
          'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
          'breadcrumb' =>  "workers",
          'userData' => $userdata,
					'clocksList' => $repository->findWorkersClocks($this->getUser()->getCompany())
          ]);
      } return new RedirectResponse($this->router->generate('app_login'));
      }

			/**
			 * @Route("/{_locale}/HR/{id}/clocks", name="workerClocks")
			 */
			public function index($id,RouterInterface $router,Request $request)
			{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();
			$locale = $request->getLocale();
			$this->router = $router;
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$utils = new HRClocksUtils();
			$templateLists=$utils->formatListbyWorker($id);
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Clocks.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('clocks', true, $id, $this->class);

			/*$utils = new GlobaleFormUtils();
 		 $utils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Clocks.json", $request, $this, $this->getDoctrine());
 		 $templateForms[]= $utils->make($id, $this->class, "read", "formClocks", "modal");
*/

			if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				return $this->render('@Globale/list.html.twig', [
					'listConstructor' => $templateLists,
					'forms' => $templateForms
					]);
			}
			return new RedirectResponse($this->router->generate('app_login'));
			}



		 /**
 		 * @Route("/api/HR/doclock/{company}/{id}", name="doClocks")
 		 */
 		 public function doClocks($company,$id, Request $request){
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			//Comprobamos si el empleado pertenece a la empresa
			$worker=$workersrepository->findOneBy(["clockCode"=>$id]);
			if($worker===NULL) return new JsonResponse(["result"=>-1]);
			if($worker->getCompany()->getId()==$company){
				//Comprobamos si hay un fichaje SeekableIterator
				$lastClock=$clocksrepository->findOneBy(["worker"=>$worker,"end"=>NULL], ['id'=>'DESC']);
				$latitude = $request->request->get("latitude");
				$longitude = $request->request->get("longitude");
				if($lastClock===NULL){
					//Abrimos el fichaje
					$lastClock=new HRClocks();
					$lastClock->setWorker($worker);
					$lastClock->setStartLatitude($latitude);
					$lastClock->setStartLongitude($longitude);
					$lastClock->setStart(new \DateTime());
					$lastClock->setDateupd(new \DateTime());
					$lastClock->setDateadd(new \DateTime());
					$lastClock->setActive(1);
					$lastClock->setDeleted(0);
					$this->getDoctrine()->getManager()->persist($lastClock);
          $this->getDoctrine()->getManager()->flush();
					return new JsonResponse(["result"=>1]);
				}else{
					$lastClock->setEndLatitude($latitude);
					$lastClock->setEndLongitude($longitude);
					$lastClock->setEnd(new \DateTime());
					$lastClock->setDateupd(new \DateTime());
					$this->getDoctrine()->getManager()->persist($lastClock);
          $this->getDoctrine()->getManager()->flush();
					return new JsonResponse(["result"=>1]);
				}
			}else return new JsonResponse(["result"=>-2]);
 		}



		/**
		 * @Route("/{_locale}/HR/clocks/data/{id}/{action}", name="dataClocks", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$template=dirname(__FILE__)."/../Forms/Clocks.json";
			$utils = new GlobaleFormUtils();
			$utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
			return $utils->make($id, $this->class, $action, "formworker", "modal");
		}

		/**
		 * @Route("/api/HR/clocks/worker/{id}/list", name="clockslistworker")
		 */
		public function clockslistworker($id,RouterInterface $router,Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$user = $this->getUser();
			$workerRepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$worker = $workerRepository->find($id);
			$locale = $request->getLocale();
			$this->router = $router;
			$manager = $this->getDoctrine()->getManager();
			$repository = $manager->getRepository($this->class);
			$listUtils=new GlobaleListUtils();
			$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true);
			$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"worker", "value"=>$worker]]);
			return new JsonResponse($return);
		}

	/**
	 * @Route("/api/HR/clocks/list", name="clockslist")
	 */
	public function listEntity(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Clocks.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class);
		return new JsonResponse($return);
	}

	/**
	 * @Route("/api/HR/clocks/status/list", name="clocksstatuslist")
	 */
	public function statuslistEntity(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		return new JsonResponse($repository->findWorkersClocks($user->getCompany()));
	}

		/**
		 * @Route("/api/HR/clocks/{id}/get", name="getClock")
		 */
		public function getEntity($id){
			$obj = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$obj) {
        throw $this->createNotFoundException('No worker found for id '.$id );
			}
			dump ($obj);
			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
		}

		/**
		 * @Route("/api/HR/clocks/worker/{company}/{id}/status", name="getClockWorkerStatus")
		 */
		public function getClockWorkerStatus($company,$id){
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
			$clocksrepository=$this->getDoctrine()->getRepository(HRClocks::class);
			//Comprobamos si el empleado pertenece a la empresa

			$worker=$workersrepository->findOneBy(["clockCode"=>$id]);
			if($worker===NULL) return new JsonResponse(["result"=>-1]);
			if($worker->getCompany()->getId()==$company){
				//Comprobamos si hay un fichaje SeekableIterator
				$lastClock=$clocksrepository->findOneBy(["worker"=>$worker,"end"=>NULL], ['id'=>'DESC']);
				if($lastClock===NULL){
					return new JsonResponse(["result"=>0]);
				}else return new JsonResponse(["result"=>1, "started"=>$lastClock->getStart()]);
			} else return new JsonResponse(["result"=>-1]);
		}



	/**
	* @Route("/{_locale}/HR/clocks/{id}/disable", name="disableClock")
	*/
	public function disable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/HR/clocks/{id}/enable", name="enableClock")
	*/
	public function enable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/HR/clocks/{id}/delete", name="deleteClock")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}


}
