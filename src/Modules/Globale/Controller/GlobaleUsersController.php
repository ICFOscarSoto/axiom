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
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleUsersUtils;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleProfilesUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Email\Entity\EmailAccounts;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Modules\Email\Controller\EmailController;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use Symfony\Component\HttpFoundation\Session\Session;

class GlobaleUsersController extends Controller
{
   	private $class=GlobaleUsers::class;
    private $utilsClass=GlobaleUsersUtils::class;

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
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $usersUtils = new GlobaleUsersUtils();
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
    $new_breadcrumb=["rute"=>null, "name"=>"Editar perfil", "icon"=>"fa fa-edit"];
    $template=dirname(__FILE__)."/../Forms/Profile.json";
    $userdata=$this->getUser()->getTemplateData();
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('profile');
    array_push($breadcrumb, $new_breadcrumb);
    $utils = new GlobaleFormUtils();
    $utilsObj=new GlobaleProfilesUtils();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$this->getUser()->getId(), "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
    return $this->render('@Globale/genericform.html.twig', array(
            'controllerName' => 'GlobaleUsersController',
            'interfaceName' => 'Perfil de usuario',
            'optionSelected' => 'profile',
            'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $this->getUser()->getId(),
            'route' => $this->generateUrl("dataUser",["id"=>$this->getUser()->getId()]),
            'form' => $utils->formatForm("formprofile", true, $this->getUser()->getId(), $this->class, 'dataUser')
    ));



    //$editor=$usersUtils->formatEditor($this->getUser(), $this->getUser(), $request, $this, $this->getDoctrine(), $encoder, "Edit", "fa fa-edit");
    //return $this->render($editor["template"], $editor["vars"]);
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
		$repository = $manager->getRepository(GlobaleUsers::class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Users.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, GlobaleUsers::class);
		return new JsonResponse($return);
	}

  /**
   * @Route("/{_locale}/user/data/{id}/{action}", name="dataUser", defaults={"id"=0, "action"="read"})
   */
   public function data($id, $action, Request $request, UserPasswordEncoderInterface $encoder){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $template=dirname(__FILE__)."/../Forms/Users.json";
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request,
                       $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],
                       method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],$encoder);
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
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('users');
    array_push($breadcrumb, $new_breadcrumb);
    $utils = new GlobaleFormUtils();
    $utilsObj=new $this->utilsClass();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$id, "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[],$encoder);
    $form=$utils->formatForm('formuser', true, $id, $this->class, 'dataUser');
    $form["id_object"]=$id;
    return $this->render('@Globale/genericform.html.twig', array(
            'controllerName' => 'UsersController',
            'interfaceName' => 'Usuarios',
            'optionSelected' => 'users',
            'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $id,
            'id_object' => $id,
            'route' => $this->generateUrl("dataUser",["id"=>$id]),
            'form' => $form

    ));
  }

	/**
	* @Route("/{_locale}/admin/global/users/new", name="newUser")
	*/
	public function newUser(Request $request, UserPasswordEncoderInterface $encoder)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = new GlobaleUsers();
    $usersUtils = new GlobaleUsersUtils();
    $editor=$usersUtils->formatEditor($this->getUser(),$user, $request, $this, $this->getDoctrine(), $encoder, "New", "fa fa-plus");
    return $this->render($editor["template"], $editor["vars"]);
	}

	/**
	* @Route("/{_locale}/admin/global/users/{id}/edit", name="editUser")
	*/
	public function editUser($id,Request $request, UserPasswordEncoderInterface $encoder)
		{
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $userRepository = $this->getDoctrine()->getRepository(GlobaleUsers::class);
      $user=$userRepository->find($id);
      $usersUtils = new GlobaleUsersUtils();
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
				//return new JsonResponse();
				return new JsonResponse($user->encodeJson());
	}

	/**
	* @Route("/{_locale}/admin/global/users/{id}/disable", name="disableUser")
	*/
	public function disable($id)
    {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/admin/global/users/{id}/enable", name="enableUser")
	*/
	public function enable($id)
    {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

  /**
	* @Route("/{_locale}/admin/global/users/{id}/delete", name="deleteUser", defaults={"id"=0})
	*/
	public function delete($id, Request $request){
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $entityUtils=new GlobaleEntityUtils();
    if($id!=0) $result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
     else {
       $ids=$request->request->get('ids');
       $ids=explode(",",$ids);
       foreach($ids as $item){
         $result=$entityUtils->deleteObject($item, $this->class, $this->getDoctrine());
       }
     }
    return new JsonResponse(array('result' => $result));
  }


	/**
	* @Route("/api/users/getShareables", name="getUsersShareables")
	*/
	public function getUsersShareables(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$userRepository = $this->getDoctrine()->getRepository(GlobaleUsers::class);
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

  /**
  * @Route("/api/global/users/collection", name="genericapiUsercollection")
  */
  public function genericapicollection(Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $manager = $this->getDoctrine()->getManager();
    $repository = $manager->getRepository($this->class);
    $parameters=$request->query->all();
    $filter[]=["type"=>"and", "column"=>"company", "value"=>$this->getUser()->getCompany()];
    foreach($parameters as $key => $parameter){
      if(in_array("set".ucfirst($parameter),get_class_methods($this->class)))
        $filter[]=["type"=>"and", "column"=>$key, "value"=>$parameter];
    }
    $listUtils=new GlobaleListApiUtils();
    $return=$listUtils->getRecords($this->getUser(),$repository,$request,$manager, $this->class,$filter,-1,["roles","password","salt","templatedata","usergroups","emailaccounts","emaildefaultaccount","calendars"]);
    return new JsonResponse($return);
  }

  /**
   * @Route("/{_locale}/global/users/export", name="exportUsers")
   */
   public function exportDepartment(Request $request){
     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
     $this->denyAccessUnlessGranted('ROLE_ADMIN');
     $utilsExport = new GlobaleExportUtils();
     $user = $this->getUser();
     $manager = $this->getDoctrine()->getManager();
     $repository = $manager->getRepository($this->class);
     $listUtils=new GlobaleListUtils();
     $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Exports/Users.json"),true);
     $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
     $result = $utilsExport->export($list,$listFields);
     return $result;
   }

   /**
 	 * @Route("/api/global/users/print", name="printUsers")
 	 */
 	 public function printDepartments(Request $request){
 		 $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
 		 $this->denyAccessUnlessGranted('ROLE_ADMIN');
 		 $utilsPrint = new GlobalePrintUtils();
 		 $user = $this->getUser();
 		 $manager = $this->getDoctrine()->getManager();
 		 $repository = $manager->getRepository($this->class);
 		 $listUtils=new GlobaleListUtils();
 		 $listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Prints/Users.json"),true);
 		 $list=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[],[],-1);
		 $utilsPrint->title="LISTADO DE USUARIOS";
 		 $pdf = $utilsPrint->print($list,$listFields,["doctrine"=>$this->getDoctrine(), "rootdir"=> $this->get('kernel')->getRootDir(), "user"=>$this->getUser()]);
		 return new Response($pdf, 200, array('Content-Type' => 'application/pdf'));
 	 }

  /**
  * @Route("/{_locale}/global/users/connectas/{id}", name="conectascompany")
  */
  public function conectascompany($id,Request $request){
      if ($this->get('security.authorization_checker')->isGranted('ROLE_GLOBAL')) {
        $repository=$this->getDoctrine()->getRepository(GlobaleCompanies::class);
    		$obj = $repository->findOneBy(['id'=>$id, 'deleted'=>0]);
    		if($obj!=null){
          $session = new Session();
          $session->set('as_company', $obj);
          return new JsonResponse(["result"=>1]);
        }else{
          return new JsonResponse(["result"=>-2]);
        }
      }else{
        return new JsonResponse(["result"=>-1]);
      }
  }

}
