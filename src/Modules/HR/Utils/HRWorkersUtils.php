<?php
namespace App\Modules\HR\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Cloud\Utils\CloudFilesUtils;


class HRWorkersUtils extends Controller
{
  public function formatList($user){
		$list=[
			'id' => 'listWorkers',
			'route' => 'workerslist',
			'routeParams' => ["id" => $user->getId()],
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

  public function getExcludedForm($params){
    return ['user'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $user=$params["user"];

    $em=$doctrine->getManager();
    $results=$em->createQueryBuilder()->select('u')
      ->from('App\Modules\Globale\Entity\GlobaleUsers', 'u')
      ->leftJoin('App\Modules\HR\Entity\HRWorkers', 'w', 'WITH', 'u.id = w.user')
      ->where('w.id IS NULL')
      ->andWhere('u.company = :val_company')
      ->setParameter('val_company', $user->getCompany())
      ->getQuery()
      ->getResult();

    return [
    ['user', ChoiceType::class, [
      'required' => false,
      'attr' => ['class' => 'select2'],
      'choices' => $results,
      'placeholder' => 'Select a user...',
      'choice_label' => function($obj, $key, $index) {
          if(method_exists($obj, "getLastname"))
            return $obj->getLastname().", ".$obj->getName();
          else return $obj->getName();
      },
      'choice_value' => 'id'
    ]]];
  }

}
