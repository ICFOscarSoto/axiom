<?php

namespace App\Modules\ERP\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPEntities;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\ERP\Utils\ERPEntitiesUtils;

class ERPEntitiesController extends Controller
{
	private $class=ERPEntities::class;
	private $utilsClass=ERPEntitiesUtils::class;
    /**
     * @Route("/{_locale}/admin/ERP/entities", name="entities")
     */
    public function index(RouterInterface $router,Request $request)
    {
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
  		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$userdata=$this->getUser()->getTemplateData();
  		$locale = $request->getLocale();
  		$this->router = $router;
  		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    	$utils = new $this->utilsClass();
  		$templateLists[]=$utils->formatList($this->getUser());
			$formUtils=new GlobaleFormUtils();
			$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Entities.json", $request, $this, $this->getDoctrine());
			$templateForms[]=$formUtils->formatForm('entities', true, null, $this->class);
  		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
  			return $this->render('@Globale/genericlist.html.twig', [
  				'controllerName' => 'entitiesController',
  				'interfaceName' => 'Departamentos',
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
		 * @Route("/{_locale}/admin/ERP/entities/form/{id}", name="formEntity", defaults={"id"=0})
		 */
		public function formEntity($id,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
			$template=dirname(__FILE__)."/../Forms/Entities.json";
			dump($this->getUser()->getCompany());
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$breadcrumb=$menurepository->formatBreadcrumb('entities');
			array_push($breadcrumb, $new_breadcrumb);
			return $this->render('@Globale/generictabform.html.twig', array(
							'controllerName' => 'EntitiesController',
							'interfaceName' => 'Empresas',
							'optionSelected' => 'entities',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' => $breadcrumb,
							'userData' => $userdata,
							'id' => $id,
							'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
							'tabs' => [["name" => "data", "caption"=>"Datos empresa", "active"=>true, "route"=>$this->generateUrl("dataEntities",["id"=>$id])],
												 ["name" => "paymentroll", "caption"=>"Nóminas"],
												 ["name" => "contracts", "caption"=>"Contratos"]
												 //["name" => "clocks", "caption"=>"Fichajes", "route"=>$this->generateUrl("workerClocks",["id"=>$id])],
												 //["name" => "files", "caption"=>"Archivos", "route"=>$this->generateUrl("cloudfiles",["id"=>$id, "path"=>"workers"])]
												]
			));


		}


		/**
		 * @Route("/{_locale}/entities/data/{id}/{action}", name="dataEntities", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Entities.json";
		 $utils = new GlobaleFormUtils();
     $utilsObj=new $this->utilsClass();
     $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
     $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
     return $utils->make($id, $this->class, $action, "formEntity", "full", "@Globale/form.html.twig", 'formEntity', $this->utilsClass);
   }



    /**
    * @Route("/api/global/entity/{id}/get", name="getEntity")
    */
    public function getEntity($id){
      $entity = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
      if (!$entity) {
            throw $this->createNotFoundException('No currency found for id '.$id );
          }
          return new JsonResponse($entity->encodeJson());
    }

  /**
   * @Route("/api/entity/list", name="entitylist")
   */
  public function indexlist(RouterInterface $router,Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $user = $this->getUser();
    $locale = $request->getLocale();
    $this->router = $router;
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $listUtils=new GlobaleListUtils();
    $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Entities.json"),true);
    $return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"company", "value"=>$user->getCompany()]]);
    return new JsonResponse($return);
  }



	/**
	* @Route("/{_locale}/admin/global/entity/{id}/disable", name="disableEntity")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/entity/{id}/enable", name="enableEntity")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/entity/{id}/delete", name="deleteEntity")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new GlobaleEntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
