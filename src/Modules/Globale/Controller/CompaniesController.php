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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\UtilsEntityUtils;
use App\Modules\Form\Controller\FormController;

class CompaniesController extends Controller
{
	private $listFields=array(array("name" => "id", "caption"=>""), array("name" => "vat", "caption"=>"CIF/NIF", "width" => "10%"), array("name" =>"name","caption"=>"Razón Social"),
								 array("name" => "active", "caption"=>"Estado", "width"=>"10%" ,"class" => "dt-center", "replace"=>array("1"=>"<div style=\"min-width: 75px;\" class=\"label label-success\">Activo</div>",
																																																		"0" => "<div style=\"min-width: 75px;\" class=\"label label-danger\">Desactivado</div>"))
								);
	 private $class=Companies::class;

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

		$templateLists=array();
		$listCompanies=array();
		$listCompanies['id'] = 'listCompanies';
		$listCompanies['fields'] = $this->listFields;
		$listCompanies['route'] = 'companieslist';
		$listCompanies['orderColumn'] = 2;
		$listCompanies['orderDirection'] = 'DESC';
		$listCompanies['tagColumn'] = 3;
		$listCompanies['fieldButtons'] = array(
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"editCompany", "confirm" =>false, "actionType" => "foreground"),
			array("id" => "desactivate", "type" => "info", "condition"=> "active", "conditionValue" =>true , "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"disableCompany", "confirm" =>true, "actionType" => "background" ),
			array("id" => "activate", "type" => "info", "condition"=> "active", "conditionValue" =>false, "icon" => "fa fa-eye","name" => "activar", "route"=>"enableCompany", "confirm" =>true, "actionType" => "background" ),
			array("id" => "delete", "type" => "danger", "icon" => "fa fa-trash","name" => "borrar", "route"=>"editCompany", "confirm" =>true, "undo" =>false, "tooltip"=>"Borrar empresa", "actionType" => "background")
					/*array("id" => "active", "condition" => array(
														array("id" => "desactivate", "type" => "info", "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"disableCompany", "confirm" =>true, "actionType" => "background" ),
														array("id" => "activate", "type" => "info", "icon" => "fa fa-eye","name" => "activar", "route"=>"enableCompany", "confirm" =>true, "actionType" => "background" )
														)),*/
		);
		$listCompanies['topButtons'] = array(
			array("id" => "addTop", "type" => "btn-primary", "icon" => "fa fa-plus", "name" => "", "route"=>"newCompany", "confirm" =>false, "tooltip" => "Crear nueva empresa"),
			array("id" => "deleteTop", "type" => "btn-red", "icon" => "fa fa-trash","name" => "", "route"=>"editCompany", "confirm" =>true),
			array("id" => "printTop", "type" => "", "icon" => "fa fa-print","name" => "", "route"=>"editCompany", "confirm" =>false),
			array("id" => "exportTop", "type" => "", "icon" => "fa fa-file-excel-o","name" => "", "route"=>"editCompany", "confirm" =>false)
		);
		$templateLists[]=$listCompanies;
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
		$return=$listUtils->getRecords($repository,$request,$manager,$this->listFields, $this->class);
		return new JsonResponse($return);
	}

	/**
	* @Route("/{_locale}/admin/global/companies/new", name="formCompany")
	*/
	public function form(Request $request)
    {


    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
      $company = new Companies();

			//Create a Form
			$formjs = new FormController();
			$formDir =dirname(__FILE__)."/../Forms/Companies";
			$formjs->readJSON($formDir);
			$formjs->printForm();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Nueva";
			$new_breadcrumb["icon"]="fa fa-plus";
			$breadcrumb=$menurepository->formatBreadcrumb('companies');
			$countries=$this->getDoctrine()->getRepository(Countries::class);
			array_push($breadcrumb, $new_breadcrumb);
	        return $this->render('@Globale/newcompany.html.twig', array(
	            'controllerName' => 'CompaniesController',
	            'interfaceName' => 'Empresas',
							'optionSelected' => $request->attributes->get('_route'),
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'formDatap' => $formjs->fullForm()
	        ));
	    }

		/**
		 * @Route("/api/global/companies/new", name="newCompany")
		 */
		public function newCompany(Request $request){
				 $company = new Companies();
				 $form = new FormController();
				 $formDir =dirname(__FILE__)."/../Forms/Companies";
				 dump($formDir);
				 $form->readJSON($formDir);
				 $company=$form->datareceived($this,$request,$company);
				 if($company == null) return new JsonResponse(array("result"=>-1));
				return new JsonResponse(array("result"=>1));
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
	* @Route("/{_locale}/admin/global/companies/{id}/edit", name="editCompany")
	*/
	public function editCompany($id,Request $request)
    {
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$company = new Companies();

			//Create a Form
			$formjs = new FormController();
			$formDir =dirname(__FILE__)."/../Forms/Companies";
			$formjs->readJSON($formDir);
			$formjs->printForm();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Nueva";
			$new_breadcrumb["icon"]="fa fa-plus";
			$breadcrumb=$menurepository->formatBreadcrumb('companies');
			$countries=$this->getDoctrine()->getRepository(Countries::class);
			array_push($breadcrumb, $new_breadcrumb);
					return $this->render('@Globale/newcompany.html.twig', array(
							'controllerName' => 'CompaniesController',
							'interfaceName' => 'Empresas',
							'optionSelected' => 'newCompany',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'formDatap' => $formjs->fullForm($this->generateUrl('getCompany', array('id'=>$id)))

					));
	}


	/**
	* @Route("/{_locale}/admin/global/companies/{id}/disable", name="disableCompany")
	*/
	public function disableCompany($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/companies/{id}/enable", name="enableCompany")
	*/
	public function enableCompany($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
