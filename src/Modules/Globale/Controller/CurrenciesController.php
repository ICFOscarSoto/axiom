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
use App\Modules\Globale\Entity\Currencies;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Form\Controller\FormController;

class CurrenciesController extends Controller
{
	private $class=Currencies::class;
	private $listFields=array(array("name" => "id", "caption"=>""),array("name" => "name", "caption"=>"Nombre", "width" => "50"), array("name" =>"isocode","caption"=>"ISO Code"), array("name" =>"charcode","caption"=>"S�mbolo"), array("name" =>"decimals","caption"=>"Decimales"));

    /**
     * @Route("/{_locale}/admin/global/currencies", name="currencies")
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
		$listCompanies['id'] = 'listCurrencies';
		$listCompanies['fields'] = $this->listFields;
		$listCompanies['route'] = 'currencieslist';
		$listCompanies['orderColumn'] = 2;
		$listCompanies['orderDirection'] = 'DESC';
		$listCompanies['tagColumn'] = 3;
		$listCompanies['fieldButtons'] = array(
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"editCurrency", "confirm" =>false, "actionType" => "foreground"),
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
				'controllerName' => 'currenciesController',
				'interfaceName' => 'Modendas',
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
		* @Route("/{_locale}/admin/global/currencies/new", name="formCurrency")
		*/

		public function formUser(Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$currency = new Currencies();
			//Create a Form
			$formjs = new FormController();
			$formDir =dirname(__FILE__)."/../Forms/Currencies";
			$formjs->readJSON($formDir);
			$formjs->printForm();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Nueva";
			$new_breadcrumb["icon"]="fa fa-plus";
			$breadcrumb=$menurepository->formatBreadcrumb('users');
			array_push($breadcrumb, $new_breadcrumb);
					return $this->render('@Globale/newcompany.html.twig', array(
							'controllerName' => 'CurrenciesController',
							'interfaceName' => 'Empresas',
							'optionSelected' => $request->attributes->get('_route'),
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'formDatap' => $formjs->fullForm()
					));
		}
		/**
	  * @Route("/api/global/currency/{id}/get", name="getCurrency")
		*/
		public function getCompany($id){
			$currency = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
			if (!$currency) {
		        throw $this->createNotFoundException('No currency found for id '.$id );
					}
					return new JsonResponse($currency->encodeJson());
		}
		/**
		* @Route("/{_locale}/admin/global/currencies/{id}/edit", name="editCurrency")
		*/
		public function editCurrency($id,Request $request)
			{
				$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
				//$this->denyAccessUnlessGranted('ROLE_ADMIN');
				$userdata=$this->getUser()->getTemplateData();

				$locale = $request->getLocale();
				$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
				$currency = new Currencies();

				//Create a Form
				$formjs = new FormController();
				$formDir =dirname(__FILE__)."/../Forms/Currencies";
				$formjs->readJSON($formDir);
				$formjs->printForm();

				$new_breadcrumb["rute"]=null;
				$new_breadcrumb["name"]="Nueva";
				$new_breadcrumb["icon"]="fa fa-plus";
				$breadcrumb=$menurepository->formatBreadcrumb('companies');

				array_push($breadcrumb, $new_breadcrumb);
						return $this->render('@Globale/newcompany.html.twig', array(
								'controllerName' => 'CurrenciesController',
								'interfaceName' => 'Empresas',
								'optionSelected' => 'newCurrency',
								'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
								'breadcrumb' =>  $breadcrumb,
								'userData' => $userdata,
								'formDatap' => $formjs->fullForm($this->generateUrl('getCurrency', array('id'=>$id)))

						));
		}
	/**
	* @Route("/api/global/currencies/new", name="newCurrency")
	*/
	public function newCurrency(Request $request){
		$currency = new Currencies();
		$form = new FormController();
		$formDir =dirname(__FILE__)."/../Forms/Currencies";
		$form->readJSON($formDir);
		$currency=$form->datareceived($this,$request,$currency);
		if($currency == null) return new JsonResponse(array("result"=>-1));
	 return new JsonResponse(array("result"=>1));
	}

	/**
	 * @Route("/api/currencies/list", name="currencieslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(Currencies::class);
		$listUtils=new ListUtils();
		$return=$listUtils->getRecords($repository,$request,$manager,$this->listFields, Currencies::class);
		return new JsonResponse($return);
	}

	/**
	* @Route("/api/currencies/select", name="currenciesSelect")
	*/
 public function currenciesSelect(RouterInterface $router,Request $request){
	 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	 $user = $this->getUser();
	 $locale = $request->getLocale();
	 $this->router = $router;
	 $manager = $this->getDoctrine()->getManager();
	 $repository = $manager->getRepository(Currencies::class);
	 $result=array();
	 $currencies= $repository->findAll();
	 foreach($currencies as $currency){
		 $result[]=array("id" => $currency->getId(), "name" => $currency->getName());
	 }
	 return new JsonResponse($result);
 }
}
