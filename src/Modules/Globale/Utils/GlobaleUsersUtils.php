<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Config\GlobaleConfigVars;

class GlobaleUsersUtils
{

  public function proccess($form,$user,$obj,$request,$entityManager,$encoder){
    //if changed Password
    $userRoles = $obj->getRoles();
    $form->handleRequest($request);
    if(!$form->isSubmitted()) return false;
    if ($form->isSubmitted() && $form->isValid() ) {

      $obj = $form->getData();
      if($form["password"]->getData()!="")
        $obj->setPassword($encoder->encodePassword($obj, $form["password"]->getData()));

      if($form["roles"]->getData()==null || $form["roles"]->getData()==""){
        $obj->setRoles($obj->getRoles());
      }else{
        //if the user has less rol than profile avoid the modification
        if($this->compareRoles($user->getRoles(),$userRoles)<2){
          //Check if user has permissions to assign this roles, and unset if neccesary
          $roles=[];
          //Avoid user grant upper privileges
          foreach ($form["roles"]->getData() as $key => $rol) {
                  if(array_search($rol, $user->getRoles())!==FALSE){
                    $roles[]=$rol;
                  }
          }
          $obj->setRoles($roles);
        }else $obj->setRoles($userRoles);
      }

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
  public function getExcludedForm($params){
    return ['password','emailDefaultAccount'];
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

  //Get the the roles with less value (less is better, more privileges)
  public function compareRoles($roles1, $roles2){
    $val1=$this->getMinRolValue($roles1);
    $val2=$this->getMinRolValue($roles2);
    if($val1==$val2) return 0;
      else return $val1<$val2?1:2;
  }

  //Get the min value of the roles (less value is better, more privileges)
  public function getMinRolValue($roles){
      $config=new GlobaleConfigVars();
      $min=count($config->roles)-1;
      foreach ($roles as $key => $rol) {
        $val=array_search($rol, $config->roles);
        if($val<$min){
          $min=$val;
        }
      }
      return $min;
  }


}
