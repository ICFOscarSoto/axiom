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
		$templateLists[]=$this->formatList($this->getUser());
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
		public function profile(RouterInterface $router,Request $request)
		{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$userdata=$this->getUser()->getTemplateData();

		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
		$user = $this->getUser();

		$new_breadcrumb["rute"]=null;
		$new_breadcrumb["name"]="Profile";
		$new_breadcrumb["icon"]="fa fa-edit";

		$formUtils=new FormUtils();
		$formUtils->init($this->getDoctrine(),$request);
		$emailAccountsRepository=$this->getDoctrine()->getRepository(EmailAccounts::class);

		$form=$formUtils->createFromEntity($user,$this, array('password','roles','emailDefaultAccount','company','active'), array(
				['password', RepeatedType::class, [
					'type' => PasswordType::class,
					'required' => false,
					'mapped' => false,
					'first_options'  => ['label' => 'Password'],
					'second_options' => ['label' => 'Repeat Password']
				]],
        ['emailDefaultAccount', ChoiceType::class, [
          'required' => false,
          'attr' => ['class' => 'select2'],
          'choices' => $emailAccountsRepository->findBy(["user"=>$user]),
          'placeholder' => 'Select an email account',
          'choice_label' => 'name',
          'choice_value' => 'id'
        ]]
			))->getForm();

		$emailAccountsLists[]=EmailController::formatList($this->getUser());
		return $this->render('@Globale/formprofile.html.twig', array(
						'controllerName' => 'UsersController',
						'interfaceName' => 'Perfil',
						'optionSelected' => null,
						'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
						'breadcrumb' =>  array($new_breadcrumb),
						'userData' => $userdata,
						'userImage' => $this->generateUrl('getUserImage', array('id'=>$user->getId())),
						'formProfile' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Profile.json"),true)],
						'lists' => $emailAccountsLists
		));

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
	public function formatList($user){
		$list=[
			'id' => 'listUsers',
			'route' => 'userslist',
			'routeParams' => ["id" => $user->getId()],
			'orderColumn' => 2,
			'orderDirection' => 'DESC',
			'tagColumn' => 5,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Users.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/UsersFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/UsersTopButtons.json"),true)
		];
		return $list;
	}


	/**
	* @Route("/{_locale}/admin/global/users/new", name="newUser")
	*/
	public function newUser(Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		//$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$userdata=$this->getUser()->getTemplateData();

		$locale = $request->getLocale();
		$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
		$user = new Users();

		$new_breadcrumb["rute"]=null;
		$new_breadcrumb["name"]="Nuevo";
		$new_breadcrumb["icon"]="fa fa-plus";
		$breadcrumb=$menurepository->formatBreadcrumb('users');

		$formUtils=new FormUtils();
		$formUtils->init($this->getDoctrine(),$request);
		$form=$formUtils->createFromEntity($user, $this, array('password'), array())->getForm();
		$formUtils->proccess($form,$user);

		array_push($breadcrumb, $new_breadcrumb);
				return $this->render('@Globale/genericform.html.twig', array(
						'controllerName' => 'UsersController',
						'interfaceName' => 'Usuarios',
						'optionSelected' => 'users',
						'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
						'breadcrumb' =>  $breadcrumb,
						'userData' => $userdata,
						'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Users.json"),true)]
				));
	}

	/**
	* @Route("/{_locale}/admin/global/users/{id}/edit", name="editUser")
	*/
	public function editUser($id,Request $request, UserPasswordEncoderInterface $encoder)
		{
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			//$this->denyAccessUnlessGranted('ROLE_ADMIN');
			$userdata=$this->getUser()->getTemplateData();

			$locale = $request->getLocale();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);


			$user = new Users();
			$new_breadcrumb["rute"]=null;
			$new_breadcrumb["name"]="Editar";
			$new_breadcrumb["icon"]="fa fa-edit";
			$breadcrumb=$menurepository->formatBreadcrumb('users');

			$userRepository = $this->getDoctrine()->getRepository(Users::class);
			$emailAccountsRepository=$this->getDoctrine()->getRepository(EmailAccounts::class);
			$user=$userRepository->find($id);
			$formUtils=new FormUtils();
			$formUtils->init($this->getDoctrine(),$request);
			$form=$formUtils->createFromEntity($user,$this, array('password','emailDefaultAccount'), array(
					['password', RepeatedType::class, [
			    	'type' => PasswordType::class,
			    	'required' => false,
						'mapped' => false,
			    	'first_options'  => ['label' => 'Password'],
			    	'second_options' => ['label' => 'Repeat Password']
					]],
					['emailDefaultAccount', ChoiceType::class, [
						'required' => false,
						'attr' => ['class' => 'select2'],
            'choices' => $emailAccountsRepository->findBy(["user"=>$user]),
            'placeholder' => 'Select an email account...',
            'choice_label' => 'name',
						'choice_value' => 'id'
					]]
				))->getForm();
			//$formUtils->proccess($form,$user);
			//change Password
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid() ) {
				$obj = $form->getData();
				if($form["password"]->getData()!="")
					$obj->setPassword($encoder->encodePassword($obj, $form["password"]->getData()));
				if($obj->getId() == null) $obj->setDateadd(new \DateTime());
				$obj->setDateupd(new \DateTime());
				$this->getDoctrine()->getManager()->persist($obj);
				$this->getDoctrine()->getManager()->flush();
			}

			array_push($breadcrumb, $new_breadcrumb);
					return $this->render('@Globale/genericform.html.twig', array(
							'controllerName' => 'UsersController',
							'interfaceName' => 'Usuarios',
							'optionSelected' => 'users',
							'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
							'breadcrumb' =>  $breadcrumb,
							'userData' => $userdata,
							'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Users.json"),true)]

					));
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
