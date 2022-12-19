<?php
namespace App\Modules\ERP\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;

class ERPStocksUtils
{

  public function getExcludedForm($params){
    return ['productvariant'];
  }
  public function formatListByProduct($product){
    $list=[
      'id' => 'listStocks',
      'route' => 'stocklist',
      'routeParams' => ["id" => $product],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Stocks.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksTopButtons.json"),true)
    ];
    return $list;
  }

  public function formatListProductsManagers($product, $manager){
    $list=[
      'id' => 'listStocksManaged',
      'route' => 'stocksmanagedlist',
      'routeParams' => ["product" => $product,
                        "storemanager" => $manager],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 3,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksManaged.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/StocksManagersFieldButtons.json"),true),
      'topButtons' => []
    ];
    return $list;
  }
}
