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
use App\Modules\Globale\Entity\GlobaleUserGroups;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleCompaniesModules;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Globale\Utils\GlobaleUsersUtils;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleProfilesUtils;
use App\Modules\Globale\Utils\GlobaleExportUtils;
use App\Modules\Globale\Utils\GlobalePrintUtils;
use App\Modules\Globale\Entity\GlobaleUserSessions;
use App\Modules\Email\Entity\EmailAccounts;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Modules\Email\Controller\EmailController;
use App\Modules\Globale\Utils\GlobaleListApiUtils;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Modules\Security\Utils\SecurityUtils;

class GlobaleUsersController extends Controller
{
   	private $class=GlobaleUsers::class;
    private $module="Globale";
    private $utilsClass=GlobaleUsersUtils::class;

    /**
     * @Route("/{_locale}/admin/global/users", name="users")
     */
    public function index(RouterInterface $router,Request $request)
    {
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));

		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $usersUtils = new GlobaleUsersUtils();
		$templateLists[]=$usersUtils->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'usersController',
				'interfaceName' => 'Usuarios',
				'menuOptions' =>  $menurepository->formatOptions($userdata),
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
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('profile');
    array_push($breadcrumb, $new_breadcrumb);
    $utils = new GlobaleFormUtils();
    $utilsObj=new GlobaleProfilesUtils();
    $params=["doctrine"=>$this->getDoctrine(), "id"=>$this->getUser()->getId(), "user"=>$this->getUser()];
    $utils->initialize($this->getUser(), new $this->class(), $template, $request, $this, $this->getDoctrine(),method_exists($utilsObj,'getExcludedForm')?$utilsObj->getExcludedForm($params):[],method_exists($utilsObj,'getIncludedForm')?$utilsObj->getIncludedForm($params):[]);
    /*return $this->render('@Globale/genericform.html.twig', array(
            'controllerName' => 'GlobaleUsersController',
            'interfaceName' => 'Perfil de usuario',
            'optionSelected' => 'profile',
            'menuOptions' =>  $menurepository->formatOptions($userdata),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $this->getUser()->getId(),
            'route' => $this->generateUrl("dataUser",["id"=>$this->getUser()->getId()]),
            'form' => $utils->formatForm("formprofile", true, $this->getUser()->getId(), $this->class, 'dataUser')
    ));*/
    $tabs =  [
      ["name" => "data", "caption"=>"Datos usuario", "icon"=>"entypo-book-open","active"=>true, "route"=>$this->generateUrl("dataUser",["id"=>$this->getUser()->getId()])]
    ];
    //Configuration tabs of modules enabled
		$modulespository=$this->getDoctrine()->getRepository(GlobaleCompaniesModules::class);
    $modules=$modulespository->findBy(["companyown"=>$this->getUser()->getCompany(), "active"=>1, "deleted"=>0]);
    foreach($modules as $module){
      if($module->getModule()->getName()=="Email"){
        $tab = ["name" => $module->getModule()->getName(), "icon"=>"fa fa-mail", "caption"=>"Cuentas Correo", "route"=>$this->generateUrl("generictablist",["module"=>"Email", "name"=>"Accounts", "id"=>$this->getUser()->getId()])];
				array_push($tabs, $tab);
      }
    }

    return $this->render('@Globale/generictabform.html.twig', array(
            'controllerName' => 'GlobaleUsersController',
            'interfaceName' => 'Perfil de usuario',
            'optionSelected' => 'profile',
            'menuOptions' =>  $menurepository->formatOptions($userdata),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $this->getUser()->getId(),
            'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
            'tabs' => $tabs,
            'route' => $this->generateUrl("dataUser",["id"=>$this->getUser()->getId()]),
            'include_header' => [["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
            'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"]],
            'include_tab_post_templates' => ['@Email/email_check.html.twig']
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
    //$this->denyAccessUnlessGranted('ROLE_ADMIN');
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
    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
    $template=dirname(__FILE__)."/../Forms/Users.json";
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('users');
    array_push($breadcrumb, $new_breadcrumb);
    $userRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
    $obj = $userRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
    if($id!=0 && $obj==null){
        return $this->render('@Globale/notfound.html.twig',[
          "status_code"=>404,
          "status_text"=>"Objeto no encontrado"
        ]);
    }
    $entity_name=$obj?$obj->getLastName().', '.$obj->getName():'';

    return $this->render('@Globale/generictabform.html.twig', array(
            'entity_name' => $entity_name,
            'controllerName' => 'WorkersController',
            'interfaceName' => 'Usuarios',
            'optionSelected' => 'users',
            'menuOptions' =>  $menurepository->formatOptions($userdata),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $id,
            'tab' => $request->query->get('tab','data'), //Show initial tab, by default data tab
            'tabs' => [["name" => "data", "caption"=>"Datos usuario", "icon"=>"fa fa-user","active"=>true, "route"=>$this->generateUrl("dataUser",["id"=>$id])],
                       ["name" => "Tarjetas", "icon"=>"fa fa-card", "caption"=>"Tarjetas", "route"=>$this->generateUrl("generictablist",["module"=>"Globale", "name"=>"UsersCards", "id"=>$id])],
                       ["name" => "groups", "caption"=>"Grupos", "icon"=>"fa fa-users", "route"=>$this->generateUrl("generictablist",["module"=>"Globale", "name"=>"UsersUserGroups", "id"=>$id])],
                       ["name" => "permissions", "caption"=>"Permisos", "icon"=>"fa fa-shield", "route"=>$this->generateUrl("userPermissions",["id"=>$id])]
                      ],
            'include_header' => [["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
            'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
                                 ["type"=>"css", "path"=>"/css/timeline.css"]]
    ));
  }

  /**
   * @Route("/{_locale}/usergroup/form/{id}", name="formUserGroup", defaults={"id"=0})
   */
   public function formUserGroup($id, Request $request, UserPasswordEncoderInterface $encoder){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
    $new_breadcrumb=["rute"=>null, "name"=>$id?"Editar":"Nuevo", "icon"=>$id?"fa fa-edit":"fa fa-new"];
    $template=dirname(__FILE__)."/../Forms/UserGroups.json";
    $userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
    $menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('usergroups');
    array_push($breadcrumb, $new_breadcrumb);
    $userGroupRepository=$this->getDoctrine()->getRepository(GlobaleUserGroups::class);
    $obj = $userGroupRepository->findOneBy(['id'=>$id, 'company'=>$this->getUser()->getCompany(), 'deleted'=>0]);
    if($id!=0 && $obj==null){
        return $this->render('@Globale/notfound.html.twig',[
          "status_code"=>404,
          "status_text"=>"Objeto no encontrado"
        ]);
    }
    $entity_name=$obj->getName();

    return $this->render('@Globale/generictabform.html.twig', array(
            'entity_name' => $entity_name,
            'controllerName' => 'UsersController',
            'interfaceName' => 'Grupos de Usuarios',
            'optionSelected' => 'genericindex',
            'optionSelectedParams' => ["module"=>"Globale", "name"=>"UserGroups"],
            'menuOptions' =>  $menurepository->formatOptions($userdata),
            'breadcrumb' => $breadcrumb,
            'userData' => $userdata,
            'id' => $id,
            'tab' => $request->query->get('tab','permissions'), //Show initial tab, by default data tab
            'tabs' => [
                       ["name" => "permissions", "caption"=>"Permisos Rutas", "active"=>true, "icon"=>"fa fa-shield", "route"=>$this->generateUrl("userGroupPermissions",["id"=>$id])]
                      ],
            'include_header' => [["type"=>"css", "path"=>"/js/rickshaw/rickshaw.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker-es.js"]],
            'include_footer' => [["type"=>"css", "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.css"],
                                 ["type"=>"js",  "path"=>"/js/datetimepicker/bootstrap-datetimepicker.min.js"],
                                 ["type"=>"css", "path"=>"/css/timeline.css"]]
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

  /**
  * @Route("/api/global/users/kick/{id}", name="kickuser")
  */
  public function kickuser($id,Request $request){
      if ($this->get('security.authorization_checker')->isGranted('ROLE_GLOBAL')) {
        $userRepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
        $sessionRepository=$this->getDoctrine()->getRepository(GlobaleUserSessions::class);
        $user = $userRepository->findOneBy(["id"=>$id]);
        $em = $this->getDoctrine()->getEntityManager();
        if($user){
          $userSessions=$sessionRepository->findBy(["user"=>$user]);
          foreach($userSessions as $userSession){
            $userSession->setKick(1);
            $em->persist($userSession);
            $em->flush();
          }
          return new JsonResponse(["result"=>1]);
        }else{
          return new JsonResponse(["result"=>-1]);
        }
      }
      return new JsonResponse(["result"=>-1]);
  }

}
