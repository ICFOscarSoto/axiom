<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class ShippingCostsUtils
{
  public function formatEditor($user, $obj, $request, $controller, $doctrine, $name, $icon){
    $userdata=$user->getTemplateData();
    $new_breadcrumb["rute"]=null;
    $new_breadcrumb["name"]=$name;
    $new_breadcrumb["icon"]=$icon;
    $menurepository=$doctrine->getRepository(MenuOptions::class);
    $breadcrumb=$menurepository->formatBreadcrumb('shippingsosts');
    $form=$this->formatForm($user, $obj, $request, $controller, $doctrine);

    array_push($breadcrumb, $new_breadcrumb);
    return ['template'=>'@Globale/genericform.html.twig', 'vars'=>array(
        'controllerName' => 'ShippingCostsController',
        'interfaceName' => 'ShippingCosts',
        'optionSelected' => 'shippingcosts',
        'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
        'breadcrumb' =>  $breadcrumb,
        'userData' => $userdata,
        'form' => ["form" => $form->createView(),"template" => json_decode(file_get_contents (dirname(__FILE__)."/../Forms/ShippingCosts.json"),true)]
    )];

  }

  public function formatForm($user, $obj, $request, $controller, $doctrine){
    $formUtils=new FormUtils();
    $formUtils->init($doctrine,$request);
    $form=$formUtils->createFromEntity($obj,$controller,['company'])->getForm();
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
       $obj = $form->getData();
       if($obj->getId() == null){
         $obj->setDateadd(new \DateTime());
         $obj->setDeleted(false);
       }
       $obj->setDateupd(new \DateTime());
       $obj->setCompany($user->getCompany());
       $doctrine->getManager()->persist($obj);
       $doctrine->getManager()->flush();
    }
    return $form;
  }

  public function formatList($user){
    $list=[
      'id' => 'listShippingCosts',
      'route' => 'shippingcostlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ShippingCosts.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ShippingCostsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/ShippingCostsTopButtons.json"),true)
    ];
    return $list;
  }
}
