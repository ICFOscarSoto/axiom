<?php
namespace App\Controller\Globale;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Globale\MenuOptions;
use App\Entity\Globale\Countries;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Utils\Globale\ListUtils;

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
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"", "confirm" =>false, "actionType" => "foreground"),
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
			return $this->render('genericlist.html.twig', [
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
}
