<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        if(method_exists($obj,'preProccess')) $obj->{'preProccess'}($this->controller->get('kernel'), null, $this->user);
        $entityManager->persist($obj);
        $entityManager->flush();
        if(method_exists($obj,'postProccess')) $obj->{'postProccess'}($this->controller->get('kernel'));
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
    ]],
    ['email', TextType::class, [
      'required' => false,
      'attr' => ['readonly' => true],
      'mapped' => false,
      'data' => $user->getEmail()
    ]]];
  }
}


?>
