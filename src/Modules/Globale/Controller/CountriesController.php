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
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Form\Controller\FormController;

class CountriesController extends Controller
{
	private $listFields=array(array("name" => "id", "caption"=>""),array("name" => "name", "caption"=>"Nombre", "width" => "50"), array("name" =>"alfa2","caption"=>"ISO Code 2"), array("name" =>"alfa3","caption"=>"ISO Code 3"));

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
		$listCompanies['orderDirection'] = 'DESC';
		$listCompanies['tagColumn'] = 3;
		$listCompanies['fieldButtons'] = array(
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"editCountry", "confirm" =>false, "actionType" => "foreground"),
			array("id" => "desactivate", "type" => "info", "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"", "confirm" =>true, "actionType" => "background" ),
			array("id" => "delete", "type" => "danger", "icon" => "fa fa-trash","name" => "borrar", "route"=>"", "confirm" =>true, "undo" =>false, "tooltip"=>"Borrar pa�s", "actionType" => "background")
		);
		$listCompanies['topButtons'] = array(
			array("id" => "addTop", "type" => "btn-primary", "icon" => "fa fa-plus", "name" => "", "route"=>"", "confirm" =>false, "tooltip" => "Crear nuevo pa�s"),
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
		* @Route("/{_locale}/admin/global/countries/new", name="formCountries")
		*/

		public function formUser(Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$country = new Countries();
			//Create a Form
			$formjs = new FormController();
			$formDir =dirname(__FILE__)."/../Forms/Countries";
			$formjs->readJSON($formDir);
			$formjs->printForm();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Nueva";
			$new_breadcrumb["icon"]="fa fa-plus";
			$breadcrumb=$menurepository->formatBreadcrumb('users');
			array_push($breadcrumb, $new_breadcrumb);
					return $this->render('@Globale/newcompany.html.twig', array(
							'controllerName' => 'CountriesController',
							'interfaceName' => 'Empresas',
							'optionSelected' => $request->attributes->get('_route'),
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'formDatap' => $formjs->fullForm()
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
		public function editCurrency($id,Request $request)
			{
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				//$this->denyAccessUnlessGranted('ROLE_ADMIN');
				$userdata=$this->getUser()->getTemplateData();

				$locale = $request->getLocale();
				$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
				$countries = new Countries();

				//Create a Form
				$formjs = new FormController();
				$formDir =dirname(__FILE__)."/../Forms/Countries";
				$formjs->readJSON($formDir);
				$formjs->printForm();

				$new_breadcrumb["rute"]=null;
				$new_breadcrumb["name"]="Nueva";
				$new_breadcrumb["icon"]="fa fa-plus";
				$breadcrumb=$menurepository->formatBreadcrumb('companies');

				array_push($breadcrumb, $new_breadcrumb);
						return $this->render('@Globale/newcompany.html.twig', array(
								'controllerName' => 'CountriesController',
								'interfaceName' => 'Empresas',
								'optionSelected' => 'newCountry',
								'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
								'breadcrumb' =>  $breadcrumb,
								'userData' => $userdata,
								'formDatap' => $formjs->fullForm($this->generateUrl('getCurrency', array('id'=>$id)))

						));
		}
	/**
	* @Route("/api/global/countries/new", name="newCountry")
	*/
	public function newCurrency(Request $request){
		$country = new Countries();
		$form = new FormController();
		$formDir =dirname(__FILE__)."/../Forms/Countries";
		$form->readJSON($formDir);
		$country=$form->datareceived($this,$request,$country);
		if($country == null) return new JsonResponse(array("result"=>-1));
	 return new JsonResponse(array("result"=>1));
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
	 * @Route("/api/countries/select", name="countriesSelect")
	 */
	public function countriesSelect(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(Countries::class);
		$result=array();
		$countries= $repository->findBy(["deleted"=>false]);
		foreach($countries as $country){
			$result[]=array("id" => $country->getId(), "name" => $country->getName());
		}
		return new JsonResponse($result);
	}
}
