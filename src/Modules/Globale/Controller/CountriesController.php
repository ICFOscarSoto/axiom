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
use App\Modules\Globale\Entity\Countries;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;


class CountriesController extends Controller
{
	private $class=Countries::class;
	private $listFields=array(array("name" => "id", "caption"=>""),array("name" => "name", "caption"=>"Nombre", "width" => "50"), array("name" =>"alfa2","caption"=>"ISO Code 2"), array("name" =>"alfa3","caption"=>"ISO Code 3"),
														array("name" => "active", "caption"=>"Estado", "width"=>"10%" ,"class" => "dt-center", "replace"=>array("1"=>"<div style=\"min-width: 75px;\" class=\"label label-success\">Activo</div>",
																																																																		"0" => "<div style=\"min-width: 75px;\" class=\"label label-danger\">Desactivado</div>")));

    /**
     * @Route("/{_locale}/admin/global/countries", name="countries")
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
		$listCompanies['id'] = 'listCountries';
		$listCompanies['fields'] = $this->listFields;
		$listCompanies['route'] = 'countrieslist';
		$listCompanies['orderColumn'] = 2;
		$listCompanies['orderDirection'] = 'ASC';
		$listCompanies['tagColumn'] = 3;
		$listCompanies['fieldButtons'] = array(
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"editCountry", "confirm" =>false, "actionType" => "foreground"),
			array("id" => "desactivate", "type" => "info", "condition"=> "active", "conditionValue" =>true , "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"disableCountry", "confirm" =>true, "actionType" => "background" ),
			array("id" => "activate", "type" => "info", "condition"=> "active", "conditionValue" =>false, "icon" => "fa fa-eye","name" => "activar", "route"=>"enableCountry", "confirm" =>true, "actionType" => "background" ),
			array("id" => "delete", "type" => "danger", "icon" => "fa fa-trash","name" => "borrar", "route"=>"", "confirm" =>true, "undo" =>false, "tooltip"=>"Borrar país", "actionType" => "background")
		);
		$listCompanies['topButtons'] = array(
			array("id" => "addTop", "type" => "btn-primary", "icon" => "fa fa-plus", "name" => "", "route"=>"newCountry", "confirm" =>false, "tooltip" => "Crear nuevo país"),
			array("id" => "deleteTop", "type" => "btn-red", "icon" => "fa fa-trash","name" => "", "route"=>"", "confirm" =>true),
			array("id" => "printTop", "type" => "", "icon" => "fa fa-print","name" => "", "route"=>"", "confirm" =>false),
			array("id" => "exportTop", "type" => "", "icon" => "fa fa-file-excel-o","name" => "", "route"=>"", "confirm" =>false)
		);
		$templateLists[]=$listCompanies;
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'CountriesController',
				'interfaceName' => 'Paises',
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
		* @Route("/{_locale}/admin/global/countries/new", name="newCountry")
		*/

		public function newCountry(Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$country = new Countries();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Nuevo";
			$new_breadcrumb["icon"]="fa fa-plus";
			$breadcrumb=$menurepository->formatBreadcrumb('countries');

			$formUtils=new FormUtils();
			$formUtils->init($this->getDoctrine(),$request);
			$form=$formUtils->createFromEntity($country, $this)->getForm();
			$formUtils->proccess($form,$country);

			array_push($breadcrumb, $new_breadcrumb);
					return $this->render('@Globale/genericform.html.twig', array(
							'controllerName' => 'CountriesController',
							'interfaceName' => 'Paises',
							'optionSelected' => 'countries',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Countries"),true)]
					));
		}
		/**
	  * @Route("/api/global/countries/{id}/get", name="getCountry")
		*/
		public function getCountry($id){
			$country = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
			if (!$country) {
		        throw $this->createNotFoundException('No currency found for id '.$id );
					}
					return new JsonResponse($country->encodeJson());
		}
		/**
		* @Route("/{_locale}/admin/global/countries/{id}/edit", name="editCountry")
		*/
		public function editCountry($id,Request $request)
			{
				//$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				$this->denyAccessUnlessGranted('ROLE_ADMIN');
				$userdata=$this->getUser()->getTemplateData();

				$locale = $request->getLocale();
				$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);

				$new_breadcrumb["rute"]=null;
				$new_breadcrumb["name"]="Editar";
				$new_breadcrumb["icon"]="fa fa-edit";
				$breadcrumb=$menurepository->formatBreadcrumb('countries');
				array_push($breadcrumb, $new_breadcrumb);

				$countryRepository = $this->getDoctrine()->getRepository(Countries::class);
				$country=$countryRepository->find($id);
				$formUtils=new FormUtils();
				$formUtils->init($this->getDoctrine(),$request);
				$form=$formUtils->createFromEntity($country,$this)->getForm();
				$formUtils->proccess($form,$country);

				return $this->render('@Globale/genericform.html.twig', array(
								'controllerName' => 'CountriesController',
								'interfaceName' => 'Paises',
								'optionSelected' => 'countries',
								'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
								'breadcrumb' =>  $breadcrumb,
								'userData' => $userdata,
								'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Countries"),true)]
				));
		}


	/**
	 * @Route("/api/countries/list", name="countrieslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(Countries::class);
		$listUtils=new ListUtils();
		$return=$listUtils->getRecords($repository,$request,$manager,$this->listFields, Countries::class);
		return new JsonResponse($return);
	}

	/**
	* @Route("/{_locale}/admin/global/countries/{id}/disable", name="disableCountry")
	*/
	public function disable($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/admin/global/countries/{id}/enable", name="enableCountry")
	*/
	public function enable($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
