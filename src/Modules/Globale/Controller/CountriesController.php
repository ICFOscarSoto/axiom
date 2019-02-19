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
use App\Modules\Globale\Utils\CountriesUtils;


class CountriesController extends Controller
{
	private $class=Countries::class;

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
		$utils = new CountriesUtils();
		$templateLists[]=$utils->formatList($this->getUser());
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
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$obj=new Countries();
			$utils = new CountriesUtils();
			$editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "New", "fa fa-plus");
			return $this->render($editor["template"], $editor["vars"]);
		}

		/**
		* @Route("/{_locale}/admin/global/countries/{id}/edit", name="editCountry")
		*/
		public function editCountry($id,Request $request)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$repository = $this->getDoctrine()->getRepository($this->class);
			$obj=$repository->find($id);
			$utils = new CountriesUtils();
			$editor=$utils->formatEditor($this->getUser(),$obj, $request, $this, $this->getDoctrine(), "Edit", "fa fa-edit");
			return $this->render($editor["template"], $editor["vars"]);
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
		$repository = $manager->getRepository(Countries::class);
		$listUtils=new ListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Countries.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, Countries::class);
		return new JsonResponse($return);
	}

	/**
	* @Route("/{_locale}/admin/global/countries/{id}/disable", name="disableCountry")
	*/
	public function disable($id)
    {
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

	/**
	* @Route("/{_locale}/admin/global/countries/{id}/enable", name="enableCountry")
	*/
	public function enable($id)
    {
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/countries/{id}/delete", name="deleteCountry")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_GLOBAL');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
}
