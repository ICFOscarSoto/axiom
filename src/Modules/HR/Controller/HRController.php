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

class HRController extends Controller
{

	 private $class=HRWorkers::class;

    /**
     * @Route("/{_locale}/HR/workers", name="workers")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
		$templateLists[]=$this->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'HRController',
				'interfaceName' => 'Trabajadores',
				'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }

	/**
	 * @Route("/api/HR/workers/list", name="workerslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new ListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, $this->class);
		return new JsonResponse($return);
	}
	public function formatList($user){
		$list=[
			'id' => 'listWorkers',
			'route' => 'workerslist',
			'routeParams' => ["id" => $user->getId()],
			'orderColumn' => 1,
			'orderDirection' => 'ASC',
			'tagColumn' => 1,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersTopButtons.json"),true)
		];
		return $list;
	}

		/**
		 * @Route("/api/HR/workers/{id}/get", name="getWorker")
		 */
		public function getWorker($id){
			$obj = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$obj) {
        throw $this->createNotFoundException('No worker found for id '.$id );
			}
			dump ($obj);
			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
		}

    /**
     * @Route("/{_locale}/HR/workers/new", name="newWorker")
     */
	public function newWorker(Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();

		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
		$worker = new HRWorkers();

		$new_breadcrumb["rute"]=null;
		$new_breadcrumb["name"]="Nuevo";
		$new_breadcrumb["icon"]="fa fa-new";
		$breadcrumb=$menurepository->formatBreadcrumb('workers');

		$formUtils=new FormUtils();
		$formUtils->init($this->getDoctrine(),$request);
		$form=$formUtils->createFromEntity($worker, $this)->getForm();
		$formUtils->proccess($form,$worker);

		array_push($breadcrumb, $new_breadcrumb);
				return $this->render('@HR/formworker.html.twig', array(
						'controllerName' => 'WorkersController',
						'interfaceName' => 'Trabajadores',
						'optionSelected' => 'workers',
						'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
						'breadcrumb' =>  $breadcrumb,
						'userData' => $userdata,
						'formworker' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Workers.json"),true)]
				));
	}

	/**
	* @Route("/{_locale}/HR/workers/{id}/edit", name="editWorker")
	*/
	public function editWorker($id,Request $request)
    {
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$obj = new HRWorkers();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Edit";
			$new_breadcrumb["icon"]="fa fa-edit";
			$breadcrumb=$menurepository->formatBreadcrumb('workers');

			$companyRepository = $this->getDoctrine()->getRepository(HRWorkers::class);
			$obj=$companyRepository->find($id);
			$formUtils=new FormUtils();
			$formUtils->init($this->getDoctrine(),$request);
			$form=$formUtils->createFromEntity($obj,$this)->getForm();
			$formUtils->proccess($form,$obj);

			array_push($breadcrumb, $new_breadcrumb);
			return $this->render('@HR/formworker.html.twig', array(
					'controllerName' => 'WorkersController',
					'interfaceName' => 'Trabajadores',
					'optionSelected' => 'workers',
					'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
					'breadcrumb' =>  $breadcrumb,
					'userData' => $userdata,
					'formworker' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Workers.json"),true)]
			));
		}


	/**
	* @Route("/{_locale}/admin/global/workers/{id}/disable", name="disableWorker")
	*/
	public function disable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/workers/{id}/enable", name="enableWorker")
	*/
	public function enable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/workers/{id}/delete", name="deleteWorker")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
