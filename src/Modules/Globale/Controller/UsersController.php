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
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\UsersUtils;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Email\Entity\EmailAccounts;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Modules\Email\Controller\EmailController;
class UsersController extends Controller
{
   	private $class=Users::class;
    private $utilsClass=UsersUtils::class;

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
    $usersUtils = new UsersUtils();
		$templateLists[]=$usersUtils->formatList($this->getUser());
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
	 * @Route("/{_locale}/global/profile", name="profile")
	 */
	public function profile(RouterInterface $router,Request $request, UserPasswordEncoderInterface $encoder)
	{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $usersUtils = new UsersUtils();
    $editor=$usersUtils->formatEditor($this->getUser(), $this->getUser(), $request, $this, $this->getDoctrine(), $encoder, "Edit", "fa fa-edit");
    return $this->render($editor["template"], $editor["vars"]);
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
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Users.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, Users::class);
		return new JsonResponse($return);
	}

  /**
   * @Route("/{_locale}/user/data/{id}/{action}", name="dataUser", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request, UserPasswordEncoderInterface $encoder){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $template=dirname(__FILE__)."/../Forms/Users.json";
    $utils = new FormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],$encoder);
    return $utils->make($id, $this->class, $action, "formuser", "full", "@Globale/form.html.twig", 'formUser', $this->utilsClass);
  }

  /**
   * @Route("/{_locale}/user/form/{id}", name="formUser", defaults={"id"=0})
   */
   public function form($id, Request $request, UserPasswordEncoderInterface $encoder){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
    $template=dirname(__FILE__)."/../Forms/Users.json";
    $userdata=$this->getUser()->getTemplateData();
    $menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('users');
    array_push($breadcrumb, $new_breadcrumb);
    $utils = new FormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],$encoder);
    return $this->render('@Globale/genericform.html.twig', array(
            'controllerName' => 'UsersController',
            'interfaceName' => 'Usuarios',
            'optionSelected' => 'users',
            'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $id,
            'route' => $this->generateUrl("dataUser",["id"=>$id]),
            'form' => $utils->formatForm('formuser', true, $id, $this->class, 'dataUser')

    ));
  }




	/**
	* @Route("/{_locale}/admin/global/users/new", name="newUser")
	*/
	public function newUser(Request $request, UserPasswordEncoderInterface $encoder)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = new Users();
    $usersUtils = new UsersUtils();
    $editor=$usersUtils->formatEditor($this->getUser(),$user, $request, $this, $this->getDoctrine(), $encoder, "New", "fa fa-plus");
    return $this->render($editor["template"], $editor["vars"]);
	}

	/**
	* @Route("/{_locale}/admin/global/users/{id}/edit", name="editUser")
	*/
	public function editUser($id,Request $request, UserPasswordEncoderInterface $encoder)
		{
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $userRepository = $this->getDoctrine()->getRepository(Users::class);
      $user=$userRepository->find($id);
      $usersUtils = new UsersUtils();
      $editor=$usersUtils->formatEditor($this->getUser(),$user, $request, $this, $this->getDoctrine(), $encoder, "Edit", "fa fa-edit");
      return $this->render($editor["template"], $editor["vars"]);

	}




	/**
  * @Route("/api/global/user/{id}/get", name="getUser")
	*/
	public function getCompany($id){
		$user = $this->getDoctrine()->getRepository($this->class)->findOneById($id);
		if (!$user) {
	        throw $this->createNotFoundException('No user found for id '.$id );
				}
				//dump($user);
				//return new JsonResponse();
				return new JsonResponse($user->encodeJson());
	}

	/**
	* @Route("/{_locale}/admin/global/users/{id}/disable", name="disableUser")
	*/
	public function disable($id)
    {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/users/{id}/enable", name="enableUser")
	*/
	public function enable($id)
    {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
  /**
	* @Route("/{_locale}/admin/global/users/{id}/delete", name="deleteUser")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new EntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
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
