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
			'tagColumn' => 1,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Workers.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/WorkersTopButtons.json"),true)
		];
		return $list;
	}
}
