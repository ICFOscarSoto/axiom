<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\ERP\Entity\ERPProducts;

class ERPWebProductsUtils
{
/*
    public function proccess($form,$user,$obj,$request,$entityManager){
      $form->handleRequest($request);
      if(!$form->isSubmitted()) return false;
      if ($form->isSubmitted() && $form->isValid() ) {
        $obj = $form->getData();

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


    public function getExcludedForm($params){
      return ['product'];
   }

   public function getIncludedForm($params){
     $doctrine=$params["doctrine"];
     $id=$params["id"];
    // $emailAccountsRepository=$doctrine->getRepository(EmailAccounts::class);
     return [['producto', TextType::class, ['required' => false]]];
   }
*/



  public function formatList($user){
    $list=[
      'id' => 'listWebProducts',
      'route' => 'webproductlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProducts.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProductsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WebProductsTopButtons.json"),true)
    ];
    return $list;
  }
}
