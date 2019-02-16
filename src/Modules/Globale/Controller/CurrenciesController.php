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
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;

class CurrenciesController extends Controller
{
	private $class=Currencies::class;

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

		$templateLists[]=$this->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'currenciesController',
				'interfaceName' => 'Monedas',
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
		* @Route("/{_locale}/admin/global/currencies/new", name="newCurrency")
		*/

		public function newCurrency(Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$currency = new Currencies();

			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Nueva";
			$new_breadcrumb["icon"]="fa fa-plus";
			$breadcrumb=$menurepository->formatBreadcrumb('currencies');

			$formUtils=new FormUtils();
			$formUtils->init($this->getDoctrine(),$request);
			$form=$formUtils->createFromEntity($currency, $this)->getForm();
			$formUtils->proccess($form,$currency);

			array_push($breadcrumb, $new_breadcrumb);
					return $this->render('@Globale/genericform.html.twig', array(
							'controllerName' => 'CurrenciesController',
							'interfaceName' => 'Monedas',
							'optionSelected' => 'currencies',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Currencies.json"),true)]
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

				$new_breadcrumb["rute"]=null;
				$new_breadcrumb["name"]="Editar";
				$new_breadcrumb["icon"]="fa fa-edit";
				$breadcrumb=$menurepository->formatBreadcrumb('currencies');

				$currencyRepository = $this->getDoctrine()->getRepository(Currencies::class);
				$currency=$currencyRepository->find($id);
				$formUtils=new FormUtils();
				$formUtils->init($this->getDoctrine(),$request);
				$form=$formUtils->createFromEntity($currency,$this)->getForm();
				$formUtils->proccess($form,$currency);

				array_push($breadcrumb, $new_breadcrumb);
						return $this->render('@Globale/genericform.html.twig', array(
								'controllerName' => 'CurrenciesController',
								'interfaceName' => 'Monedas',
								'optionSelected' => 'currencies',
								'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
								'breadcrumb' =>  $breadcrumb,
								'userData' => $userdata,
								'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Currencies.json"),true)]
				));
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
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Currencies.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, Currencies::class);
		return new JsonResponse($return);
	}
	public function formatList($user){
		$list=[
			'id' => 'listCurrencies',
			'route' => 'currencieslist',
			'routeParams' => ["id" => $user->getId()],
			'orderColumn' => 2,
			'orderDirection' => 'ASC',
			'tagColumn' => 3,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Currencies.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CurrenciesFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/CurrenciesTopButtons.json"),true)
		];
		return $list;
	}
	/**
	* @Route("/{_locale}/admin/global/currencies/{id}/disable", name="disableCurrency")
	*/
 public function disable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/currencies/{id}/enable", name="enableCurrency")
 */
 public function enable($id)
	 {
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }
 /**
 * @Route("/{_locale}/admin/global/currencies/{id}/delete", name="deleteCurrency")
 */
 public function delete($id){
	 $this->denyAccessUnlessGranted('ROLE_GLOBAL');
	 $entityUtils=new EntityUtils();
	 $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
	 return new JsonResponse(array('result' => $result));
 }

}
