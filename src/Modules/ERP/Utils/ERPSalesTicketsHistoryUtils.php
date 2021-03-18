<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;

class ERPSalesTicketsHistoryUtils
{

  private $module="ERP";
  private $name="SalesTicketsHistory";
  public $parentClass="\App\Modules\ERP\Entity\ERPSalesTickets";
  public $parentField="salestickets";

  public function formatList($user){
    $list=[
      'id' => 'listSalesTicketsHistory',
      'route' => 'salesticketshistorylist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsHistory.json"),true)
    /*  'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTraceabilityHistoryFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTraceabilityHistoryTopButtons.json"),true)
      */
    ];
    return $list;
  }

  public function formatListByTicket($parent){;
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'salesticketshistorylist',
      'routeParams' => ["salesticketid" => $parent],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsHistory.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsHistoryFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/SalesTicketsHistoryTopButtons.json"),true)
    ];
    return $list;
  }


}
