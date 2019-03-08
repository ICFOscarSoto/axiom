<?php
namespace App\Modules\Cloud\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Globale\Utils\ListUtils;


class CloudFilesUtils
{


  public function formatList($user,$path,$id){
		$list=[
			'id' => 'listFiles',
			'route' => 'fileslist',
			'routeParams' => ["userid" => $user->getId(), "path"=>$path, "id"=>$id],
			'orderColumn' => 1,
			'orderDirection' => 'ASC',
			'tagColumn' => 1,
      'rowAction' => 'download',
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Files.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/FilesFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/FilesTopButtons.json"),true)
		];
		return $list;
	}
}
