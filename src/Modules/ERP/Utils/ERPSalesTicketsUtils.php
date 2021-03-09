<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;

class ERPSalesTicketsUtils
{

  public function formatList($user){
    $list=[
      'id' => 'listSalesTickets',
      'route' => 'salesticketslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTickets.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return [];
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
