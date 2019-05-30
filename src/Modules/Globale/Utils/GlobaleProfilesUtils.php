<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class GlobaleProfilesUtils
{

  public function proccess($form,$user,$obj,$request,$entityManager,$encoder){
    //if changed Password
    $form->handleRequest($request);
    if(!$form->isSubmitted()) return false;
    if ($form->isSubmitted() && $form->isValid() ) {
      $obj = $form->getData();
      if($form["password"]->getData()!="")
        $obj->setPassword($encoder->encodePassword($obj, $form["password"]->getData()));

      if($obj->getId() == null) {
        $obj->setDateadd(new \DateTime());
        $obj->setDeleted(false);
        //If object has Company save with de user Company
        if(method_exists($obj,'setCompany')) $obj->setCompany($user->getCompany());
      }
      $obj->setDateupd(new \DateTime());
      try{
        if(method_exists($obj,'preProccess')) $obj->{'preProccess'}();
        $entityManager->persist($obj);
        $entityManager->flush();
        return $obj;
      }catch (Exception $e) {
        return false;
      }
    }
  }

  public function getExcludedForm(){
    return ["password","emailDefaultAccount", "email", "roles", "apiToken"];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $emailAccountsRepository=$doctrine->getRepository(EmailAccounts::class);
    return [['password', RepeatedType::class, [
      'type' => PasswordType::class,
      'required' => false,
      'mapped' => false,
      'first_options'  => ['label' => 'Password'],
      'second_options' => ['label' => 'Repeat Password']
    ]],
    ['emailDefaultAccount', ChoiceType::class, [
      'required' => false,
      'attr' => ['class' => 'select2'],
      'choices' => $emailAccountsRepository->findBy(["user"=>$id]),
      'placeholder' => 'Select an email account...',
      'choice_label' => 'name',
      'choice_value' => 'id'
    ]]];
  }

/*

  public function formatEditor($user, $obj, $request, $controller, $doctrine, $encoder, $name, $icon){
    $userdata=$user->getTemplateData();
    $new_breadcrumb["rute"]=null;
    $new_breadcrumb["name"]=$name;
    $new_breadcrumb["icon"]=$icon;
    //$locale = $request->getLocale();
    $menurepository=$doctrine->getRepository(GlobaleMenuOptions::class);
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
    $formUtils=new GlobaleFormUtils();
		$formUtils->init($doctrine,$request);
		$emailAccountsRepository=$doctrine->getRepository(GlobaleEmailAccounts::class);
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
  }*/
}


?>
