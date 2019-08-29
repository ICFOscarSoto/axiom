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
use App\Modules\HR\Entity\HRWorkers;


class HRVacationsUtils
{
  public function formatListbyWorker($worker){
    $list=[
      'id' => 'listVacations',
      'route' => 'vacationslistworker',
      'routeParams' => ["id" => $worker],
      'orderColumn' => 3,
      'orderDirection' => 'DESC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Vacations.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/VacationsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/VacationsTopButtons.json"),true)
    ];
    return $list;
  }

  public function getExcludedForm($params){
    return ['worker','type'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $worker=$params["worker"];
    $workersRepository=$doctrine->getRepository(HRWorkers::class);
    return [
    ['worker', ChoiceType::class, [
      'required' => true,
      'disabled' => false,
      'attr' => ['class' => 'select2', 'readonly' => true],
      'choices' => $workersRepository->findBy(["id"=>$worker->getId()]),
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname"))
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'choice_value' => 'id',
      'data' => $worker

    ]],
    ['type', ChoiceType::class, [
      'required' => true,
      'attr' => ['class' => 'select2'],
      'choices' => ['Vacaciones'=>1, 'Permiso'=>2, 'Asuntos propios'=>3, 'Excedencia'=>3],
      'placeholder' => 'Select a type...',
    ]]

  ];
  }
}
