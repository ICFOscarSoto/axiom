<?php
namespace App\Modules\Email\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Email\Entity\EmailFolders;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Email\Entity\GlobaleEmailAccounts;

class EmailAccountsUtils
{
    private $module="Email";
    private $name="Accounts";
    public function getExcludedForm($params){
      return ['user','inboxFolder','sentFolder','trashFolder'];
    }

    public function getIncludedForm($params){
      $doctrine=$params["doctrine"];
      $id=$params["id"];
      $user=$params["user"];
      $userRepository=$doctrine->getRepository(GlobaleUsers::class);
      $accountsRepository=$doctrine->getRepository(EmailAccounts::class);
      $folderRepository=$doctrine->getRepository(EmailFolders::class);
      $account=$accountsRepository->findOneBy(["user"=>$user, "id"=>$id]);
      $folders=$folderRepository->findBy(["emailAccount"=>$account]);
      return [['user', ChoiceType::class, [
        'required' => true,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $userRepository->findBy(["id"=>$user->getId()]),
        'choice_label' => function($obj, $key, $index) {
            if(method_exists($obj, "getLastname"))
              return $obj->getLastname().", ".$obj->getName();
            else return $obj->getName();
        },
        'choice_value' => 'id',
        'data' => $user
      ]],
      ['inboxFolder', ChoiceType::class, [
        'required' => true,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $folders,
        'choice_label' => function($obj, $key, $index) {
            if(method_exists($obj, "getLastname"))
              return $obj->getLastname().", ".$obj->getName();
            else return $obj->getName();
        },
        'choice_value' => 'id'
      ]],
      ['sentFolder', ChoiceType::class, [
        'required' => true,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $folders,
        'choice_label' => function($obj, $key, $index) {
            if(method_exists($obj, "getLastname"))
              return $obj->getLastname().", ".$obj->getName();
            else return $obj->getName();
        },
        'choice_value' => 'id'
      ]],
      ['trashFolder', ChoiceType::class, [
        'required' => true,
        'disabled' => false,
        'attr' => ['class' => 'select2', 'readonly' => true],
        'choices' => $folders,
        'choice_label' => function($obj, $key, $index) {
            if(method_exists($obj, "getLastname"))
              return $obj->getLastname().", ".$obj->getName();
            else return $obj->getName();
        },
        'choice_value' => 'id'
      ]]



    ];
    }

    public function formatList($user){
      $list=[
        'id' => 'list'.$this->name,
        'route' => 'genericlist',
        'routeParams' => ["module" => $this->module,
                          "name" => $this->name],
        'orderColumn' => 2,
        'orderDirection' => 'ASC',
        'tagColumn' => 2,
        'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
        'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
        'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
      ];
      return $list;
    }
}
