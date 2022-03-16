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
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Cloud\Controller\CloudController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\HR\Entity\HRWorkerScores;
use App\Modules\HR\Utils\HRWorkerScoresUtils;
use App\Modules\Globale\Entity\GlobaleNotifications;
use App\Modules\Globale\Config\GlobaleConfigVars;
use App\Modules\Globale\Controller\GlobaleFirebaseDevicesController;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Security\Utils\SecurityUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;

class HRWorkerScoresController extends Controller
{
	 private $module='HR';
	 private $class=HRWorkerScores::class;
	 private $utilsClass=HRWorkerScoresUtils::class;

    /**
     * @Route("/{_locale}/HR/{id}/workerscores", name="workerScores")
     */
    public function index($id,RouterInterface $router,Request $request)
    {
		setlocale(LC_ALL, 'es_ES');
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;
    $scoresrepository=$this->getDoctrine()->getRepository($this->class);
    $obj=$scoresrepository->findOneBy(["id"=>$id]);

    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $utils = new $this->utilsClass;
    $templateLists=$utils->formatList($this->getUser(),$id);

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@Globale/list.html.twig', [
        'listConstructor' => $templateLists,
        'id' => $id,
				'today' => date("d/m/Y"),
        'userData' => $userdata,
        'include_pre_templates' => ['@HR/workerscoresummary.html.twig', '@HR/workerscore_modal.html.twig']
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));
    }


		/**
     * @Route("/{_locale}/HR/{id}/workerscores/framesummary", name="workerScoresFrameSummary")
     */
    public function workerScoresFrameSummary($id,RouterInterface $router,Request $request)
    {
		setlocale(LC_ALL, 'es_ES');
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $locale = $request->getLocale();
    $this->router = $router;
    $scoresrepository=$this->getDoctrine()->getRepository($this->class);

    $workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);
		$workerScorerepository=$this->getDoctrine()->getRepository(HRWorkerScores::class);
    $worker=$workersrepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany(), "deleted"=>0]);

		//Data for evolution graph
		//-------------------------------
		$date=new \DateTime();
		$months=[];
		$workerScores=[];
		$avgScores=[];

		$dateflag=clone $date;
		$dateflag->modify("-11 months");

		while($dateflag<=$date){
			$months[]=$dateflag->format('m/Y');
			$workerScores[]=$workerScorerepository->getScoreMonth($id, $dateflag->format('Y'),$dateflag->format('n'));
			$avgScores[]=$workerScorerepository->getScoreMonth(null, $dateflag->format('Y'),$dateflag->format('n'));
			$dateflag->modify("+1 month");
		}

		//-------------------------------

    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      return $this->render('@HR/workerscore_frame_summary.html.twig', [
        'id' => $id,
				'worker_name' => $worker->getName(),
				'worker_lastname' => $worker->getLastname(),
				'evolution_graph' => ['months'=>$months, 'scores'=>$workerScores, 'avgscores'=>$avgScores],
				'thisyear_score' => ['label'=>'Media '.date("Y",strtotime("-0 year")) , 'score'=>round($workerScorerepository->getScoreMonth($id,date("Y",strtotime("-0 year")),null)) ],
				'lastyear_score' => ['label'=>'Media '.date("Y",strtotime("-1 year")) , 'score'=>round($workerScorerepository->getScoreMonth($id,date("Y",strtotime("-1 year")),null)) ],
				'allyear_score' => ['label'=>'Media desde origen' , 'score'=>round($workerScorerepository->getScoreTotal($id)) ],
				'today' => date("d/m/Y")
        ]);
    }
    return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/HR/workerscores/data/{id}/{action}", name="dataWorkerScores", defaults={"id"=0, "action"="read"})
		 */
		 public function dataWorkerScores($id, $action, Request $request){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$workerScorerepository=$this->getDoctrine()->getRepository(HRWorkerScores::class);
			$workersrepository=$this->getDoctrine()->getRepository(HRWorkers::class);

			if($action=='read'){
				$workerScore=$workerScorerepository->findOneBy(['id'=>$id, 'active'=>1, 'deleted'=>0]);
				if(!$workerScore) return new JsonResponse(['result'=>-1]);
				return new JsonResponse(['result'=>1, 'data'=>['id'=>$workerScore->getId(), 'score'=>$workerScore->getScore(), 'date'=>$workerScore->getDate()->format('d/m/Y'), 'text'=>$workerScore->getText()]]);
			}else {
				//Save get_class_methods
				$data=json_decode($request->getContent(),true);
				if($data!=null){
					  $worker=$workersrepository->findOneBy(["id"=>$data['worker'], "company"=>$this->getUser()->getCompany(), "deleted"=>0]);
						if($worker==null) return new JsonResponse(['result'=>-1]);
						$workerScore=null;
						if($data['id']!=0){
							$workerScore=$workerScorerepository->findOneBy(['id'=>$data['id'], 'active'=>1, 'deleted'=>0]);
							if($workerScore==null) return new JsonResponse(['result'=>-1]);
						}else{
							$workerScore=new HRWorkerScores();
							$workerScore->setWorker($worker);
							$workerScore->setAuthor($this->getUser());
							$workerScore->setDateadd(new \DateTime());
						}
						$workerScore->setScore($data['score']*1);
						$workerScore->setText($data['text']);
						$workerScore->setDate(date_create_from_format('d/m/Y', $data['date']));
						$workerScore->setDateupd(new \DateTime());
						$this->getDoctrine()->getManager()->persist($workerScore);
						$this->getDoctrine()->getManager()->flush();
						return new JsonResponse(['result'=>1, 'data'=>['id'=>$workerScore->getId()]]);


				}else{
					return new JsonResponse(['result'=>-1]);
				}
				return new JsonResponse(['result'=>-1]);
			}
		}

}
