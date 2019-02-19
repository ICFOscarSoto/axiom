<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class ProfilesUtils
{
  public function formatEditor($user, $obj, $request, $controller, $doctrine, $encoder, $name, $icon){
    $userdata=$user->getTemplateData();
    $new_breadcrumb["rute"]=null;
    $new_breadcrumb["name"]=$name;
    $new_breadcrumb["icon"]=$icon;
    //$locale = $request->getLocale();
    $menurepository=$doctrine->getRepository(MenuOptions::class);
    $emailAccountsRepository=$doctrine->getRepository(EmailAccounts::class);
    $emailAccountsLists[]=EmailController::formatList($user);
    $form=$this->formatForm($user, $obj, $request, $controller, $doctrine, $encoder);

    array_push($breadcrumb, $new_breadcrumb);
    return ['template'=>'@Globale/formprofile.html.twig', 'vars' => array(
        'controllerName' => 'UsersController',
        'interfaceName' => 'Perfil',
        'optionSelected' => null,
        'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
        'breadcrumb' =>  array($new_breadcrumb),
        'userData' => $userdata,
        'userImage' => $controller->generateUrl('getUserImage', array('id'=>$user->getId())),
        'formProfile' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/Profile.json"),true)],
        'lists' => $emailAccountsLists
      )];
  }

  public function formatForm($user, $obj, $request, $controller, $doctrine, $encoder){
    $formUtils=new FormUtils();
		$formUtils->init($doctrine,$request);
		$emailAccountsRepository=$doctrine->getRepository(EmailAccounts::class);
		$form=$formUtils->createFromEntity($obj,$controller, ['password','roles','emailDefaultAccount','company','active'], [
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
          'placeholder' => 'Select an email account',
          'choice_label' => 'name',
          'choice_value' => 'id'
        ]]
			])->getForm();

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
}


?>
