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
use App\Modules\Globale\Entity\Users;
use App\Modules\Globale\Utils\ListUtils;
use AApp\Modules\Globale\Utils\EntityUtils;

class UsersController extends Controller
{
	private $listFields=array(array("name" => "id", "caption"=>""),
								 array("name" => "company__name", "caption"=>"Empresa", "width" => "50"),
								 array("name" => "name", "caption"=>"Nombre", "width" => "50"),
								 array("name" =>"firstname","caption"=>"Apellidos"),
								 array("name" =>"email","caption"=>"Email"),
								 array("name" => "active", "caption"=>"Estado", "width"=>"10%" ,"class" => "dt-center", "replace"=>array("1"=>"<div style=\"min-width: 75px;\" class=\"label label-success\">Activo</div>",
																																		 "0" => "<div style=\"min-width: 75px;\" class=\"label label-danger\">Desactivado</div>"))
								);
	private $class=Users::class;

    /**
     * @Route("/{_locale}/admin/global/users", name="users")
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
		$listCompanies['id'] = 'listUsers';
		$listCompanies['fields'] = $this->listFields;
		$listCompanies['route'] = 'userslist';
		$listCompanies['orderColumn'] = 2;
		$listCompanies['orderDirection'] = 'DESC';
		$listCompanies['tagColumn'] = 5;
		$listCompanies['fieldButtons'] = array(
			array("id" => "edit", "type" => "default", "icon" => "fa fa-edit", "name" => "editar", "route"=>"", "confirm" =>false, "actionType" => "foreground"),
			array("id" => "desactivate", "type" => "info", "condition"=> "active", "conditionValue" =>true , "icon" => "fa fa-eye-slash","name" => "desactivar", "route"=>"disableUser", "confirm" =>true, "actionType" => "background" ),
			array("id" => "activate", "type" => "info", "condition"=> "active", "conditionValue" =>false, "icon" => "fa fa-eye","name" => "activar", "route"=>"enableUser", "confirm" =>true, "actionType" => "background" ),
			array("id" => "delete", "type" => "danger", "icon" => "fa fa-trash","name" => "borrar", "route"=>"", "confirm" =>true, "undo" =>false, "tooltip"=>"Borrar empresa", "actionType" => "background")
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
				'controllerName' => 'usersController',
				'interfaceName' => 'Usuarios',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'optionSelected' => 'users',
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));
    }

	/**
	 * @Route("/api/users/list", name="userslist")
	 */
	public function indexlist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(Users::class);
		$listUtils=new ListUtils();
		$return=$listUtils->getRecords($repository,$request,$manager,$this->listFields, Users::class);
		return new JsonResponse($return);
	}




	/**
	* @Route("/{_locale}/admin/global/users/{id}/disable", name="disableUser")
	*/
	public function disableUser($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/users/{id}/enable", name="enableUser")
	*/
	public function enableUser($id)
    {
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}


	/**
	* @Route("/api/users/getShareables", name="getUsersShareables")
	*/
	public function getUsersShareables(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$userRepository = $this->getDoctrine()->getRepository(Users::class);
			$return=array();
			$user=$this->getUser();
			$query=$request->query->get('q','');
			$users=$userRepository->getShareables($user, $query);
			foreach($users as $shareable){
					$item["id"] = $shareable->getId();
					$item["value"] = $shareable->getEmail();
					$item["name"] = $shareable->getName();
					$item["firstname"] = $shareable->getFirstname();
					$item["email"] = $shareable->getEmail();
					$item["image"] = $this->generateUrl('getUserImage', array('id'=>$shareable->getId()));
					$item["tokens"] = Array(null, "");
					$return[]=$item;
			}
			return new JsonResponse($return);
		}return new Response();
	}

}
