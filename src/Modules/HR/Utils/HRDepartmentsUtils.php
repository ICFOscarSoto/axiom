<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;

class HRDepartmentsUtils extends Controller
{
  public function formatList($user){
    $list=[
      'id' => 'listDepartments',
      'route' => 'departmentslist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Departments.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/DepartmentsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/DepartmentsTopButtons.json"),true)
    ];
    return $list;
  }


  public function formatListWorkers($user, $id){
    $list=[
      'id' => 'listWorkers',
      'route' => 'workerslist',
      'routeParams' => ["id" => $id, "type"=>"department"],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 5,
      'rowAction' => 'edit',
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersTopButtons.json"),true)
    ];
    return $list;
  }
}
