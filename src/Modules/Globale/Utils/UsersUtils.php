<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class UsersUtils
{
  public function formatEditor($user, $obj, $request, $controller, $doctrine, $encoder, $name, $icon){
    $userdata=$user->getTemplateData();
    $new_breadcrumb["rute"]=null;
    $new_breadcrumb["name"]=$name;
    $new_breadcrumb["icon"]=$icon;
    $menurepository=$doctrine->getRepository(MenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('users');
    $form=$this->formatForm($user, $obj, $request, $controller, $doctrine, $encoder);

    array_push($breadcrumb, $new_breadcrumb);
    return ['template'=>'@Globale/genericform.html.twig', 'vars' => array(
        'controllerName' => 'UsersController',
        'interfaceName' => 'Usuarios',
        'optionSelected' => 'users',
        'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Users.json"),true)]
    )];
  }

  public function formatForm($user, $obj, $request, $controller, $doctrine, $encoder){
    $formUtils=new FormUtils();
    $formUtils->init($doctrine,$request);
    $emailAccountsRepository=$doctrine->getRepository(EmailAccounts::class);
    $form=$formUtils->createFromEntity($obj,$controller, array('password','emailDefaultAccount'), array(
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
          'choices' => $emailAccountsRepository->findBy(["user"=>$obj]),
          'placeholder' => 'Select an email account...',
          'choice_label' => 'name',
          'choice_value' => 'id'
        ]]
      ))->getForm();

    //if changed Password
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid() ) {
      $object = $form->getData();
      if($form["password"]->getData()!="")
        $object->setPassword($encoder->encodePassword($object, $form["password"]->getData()));
      if($object->getId() == null) {
        $object->setDateadd(new \DateTime());
        $object->setDeleted(false);
      }
      $object->setDateupd(new \DateTime());
      $doctrine->getManager()->persist($object);
      $doctrine->getManager()->flush();
    }
    return $form;
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
}
