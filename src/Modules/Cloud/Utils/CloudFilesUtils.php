<?php
namespace App\Modules\Cloud\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Cloud\Entity\CloudFiles;
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

  public function clearTemp($params){
    //TODO limiar carpeta temporal del usuario
    //TODO limpiar los registros en BDD
  }

  public function convertToFiles($params){
    $rootDir=$params["rootDir"];
    $doctrine=$params["doctrine"];
    $user=$params["user"];
    $path=$params["path"];
    $id=$params["id"]; //New Id of the class
    $cloudRepository=$doctrine->getRepository(CloudFiles::class);
    // Identify directories
    $source = $rootDir.'/../cloud/'.$user->getCompany()->getId().'/temp/'.$user->getId().'/'.$path.'/';
    $destination = $rootDir.'/../cloud/'.$user->getCompany()->getId().'/'.$path.'/'.$id.'/';
    if (!file_exists($destination) && !is_dir($destination)) {
        mkdir($destination, 0775, true);
    }
    // Get array of all source files
    $files = scandir($source);
    // Cycle through all source files
    foreach ($files as $file) {
      if (in_array($file, array(".",".."))) continue;
      // If we copied this successfully, mark it for deletion
      if (copy($source.$file, $destination.$file)) {
        $delete[] = $source.$file;
        $cloudFile=$cloudRepository->findOneBy(["hashname"=>pathinfo($source.$file, PATHINFO_FILENAME)]);
        if($cloudFile!=null){
          $cloudFile->setIdclass($id);
          $doctrine->getManager()->persist($cloudFile);
          $doctrine->getManager()->flush();
        }
      }
    }
    // Delete all successfully-copied files
    foreach ($delete as $file) {
      unlink($file);
    }
    $cloudRepository=$doctrine->getRepository(CloudFiles::class);

  }
}
