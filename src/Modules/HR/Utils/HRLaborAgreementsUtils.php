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

class HRLaborAgreementsUtils extends Controller
{
  public function formatList($user){
    $list=[
      'id' => 'listLaborAgreement',
      'route' => 'laboragreementlist',
      'routeParams' => ["id" => $user->getId()],
      'orderColumn' => 1,
      'orderDirection' => 'ASC',
      'tagColumn' => 1,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/LaborAgreements.json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/LaborAgreementsFieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/LaborAgreementsTopButtons.json"),true)
    ];
    return $list;
  }

}
