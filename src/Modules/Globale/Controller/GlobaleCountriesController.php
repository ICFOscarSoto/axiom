<?php

namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCountries;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleCountriesUtils;


class GlobaleCountriesController extends Controller
{
	private $class=GlobaleCountries::class;
	private $utilsClass=GlobaleCountriesUtils::class;

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
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
		$utils = new $this->utilsClass();
		$templateLists[]=$utils->formatList($this->getUser());
		$formUtils=new GlobaleFormUtils();
		$formUtils->initialize($this->getUser(), new $this->class(), dirname(__FILE__)."/../Forms/Countries.json", $request, $this, $this->getDoctrine());
		$templateForms[]=$formUtils->formatForm('countries', true, null, $this->class);
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'CountriesController',
				'interfaceName' => 'Paises',
				'optionSelected' => $request->attributes->get('_route'),
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists,
        'forms' => $templateForms
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/{_locale}/countries/data/{id}/{action}", name="dataCountries", defaults={"id"=0, "action"="read"})
		 */
		 public function data($id, $action, Request $request){
		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
		 $template=dirname(__FILE__)."/../Forms/Countries.json";
		 $utils = new GlobaleFormUtils();
		 $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine());
		 return $utils->make($id, $this->class, $action, "formCountries", "modal");
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
	 * @Route("/api/countries/list", name="countrieslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(GlobaleCountries::class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Countries.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, GlobaleCountries::class);
		return new JsonResponse($return);
	}

	/**
	* @Route("/{_locale}/admin/global/countries/{id}/disable", name="disableCountry")
	*/
	public function disable($id)
    {
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/admin/global/countries/{id}/enable", name="enableCountry")
	*/
	public function enable($id)
    {
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/countries/{id}/delete", name="deleteCountry")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
