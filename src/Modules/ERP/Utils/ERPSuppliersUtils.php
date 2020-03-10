<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Email\Entity\EmailAccounts;

class ERPSuppliersUtils
{
  public function formatList($user){
    $list=[
      'id' => 'listSuppliers',
      'route' => 'supplierlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Suppliers.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SuppliersFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SuppliersTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ['supplier'];
  }

  public function getIncludedForm($params){
  /*
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $id=$params["id"];
    $productRepository=$doctrine->getRepository(ERPProducts::class);
    $products=$productRepository->findOneBy(["id"=>$id]);
    */
    return [];
  }






}
