<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Entity\Companies;
use App\Modules\Globale\Entity\Countries;
use App\Modules\Globale\Entity\Currencies;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\CompaniesUtils;
//use App\Modules\Globale\UtilsEntityUtils;
//use App\Modules\Form\Controller\FormController;

class CompaniesController extends Controller
{

	 private $class=Companies::class;
	 private $utilsClass=CompaniesUtils::class;

    /**
     * @Route("/{_locale}/admin/global/companies", name="companies")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);

		$utils = new CompaniesUtils();
		$templateLists[]=$utils->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'CompaniesController',
				'interfaceName' => 'Empresas',
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
	 * @Route("/api/companies/list", name="companieslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository($this->class);
		$listUtils=new ListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Companies.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, $this->class);
		return new JsonResponse($return);
	}


		/**
		 * @Route("/api/global/companies/{id}/get", name="getCompany")
		 */
		public function getCompany($id){
			$company = $this->getDoctrine()->getRepository($this->class)->findById($id);
			if (!$company) {
        throw $this->createNotFoundException('No company found for id '.$id );
			}
			dump ($company);
			return new JsonResponse();
			return new JsonResponse($company->encodeJson());
		}


		/**
	   * @Route("/{_locale}/company/data/{id}/{action}", name="dataCompany", defaults={"id"=0, "action"="read"})
	   */
	   public function data($id, $action, Request $request){
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    $this->denyAccessUnlessGranted('ROLE_ADMIN');
	    $template=dirname(__FILE__)."/../Forms/Companies.json";
	    $utils = new FormUtils();
	    $utilsObj=new $this->utilsClass();
	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
	    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	    return $utils->make($id, $this->class, $action, "formCompany", "full", "@Globale/form.html.twig", 'formCompany', $this->utilsClass);
	  }

	  /**
	   * @Route("/{_locale}/company/form/{id}", name="formCompany", defaults={"id"=0})
	   */
	   public function form($id, Request $request){
	    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	    $this->denyAccessUnlessGranted('ROLE_ADMIN');
	    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
	    $template=dirname(__FILE__)."/../Forms/Companies.json";
	    $userdata=$this->getUser()->getTemplateData();
	    $menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
	    $breadcrumb=$menurepository->formatBreadcrumb('companies');
	    array_push($breadcrumb, $new_breadcrumb);
	    $utils = new FormUtils();
	    $utilsObj=new $this->utilsClass();
	    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
	    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
	    return $this->render('@Globale/genericform.html.twig', array(
	            'controllerName' => 'UsersController',
	            'interfaceName' => 'Empresas',
	            'optionSelected' => 'companies',
	            'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
	            'breadcrumb' => $breadcrumb,
	            'userData' => $userdata,
	            'id' => $id,
	            'route' => $this->generateUrl("dataCompany",["id"=>$id]),
	            'form' => $utils->formatForm('formcompany', true, $id, $this->class, 'dataCompany')

	    ));
	  }

	/**
	* @Route("/{_locale}/admin/global/companies/{id}/disable", name="disableCompany")
	*/
	public function disable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/admin/global/companies/{id}/enable", name="enableCompany")
	*/
	public function enable($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	
	/**
	* @Route("/{_locale}/admin/global/companies/{id}/delete", name="deleteCompany")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
