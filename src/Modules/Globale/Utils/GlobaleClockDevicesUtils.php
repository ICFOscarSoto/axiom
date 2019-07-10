<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Config\GlobaleConfigVars;

class GlobaleClockDevicesUtils
{
  private $module="Globale";
  private $name="ClockDevices";
  public function getExcludedForm($params){
    return ['company'];
  }

  public function getIncludedForm($params){
    $doctrine=$params["doctrine"];
    $id=$params["id"];
    $user=$params["user"];
    $companyRepository=$doctrine->getRepository(GlobaleCompanies::class);
    return [];
  }

  public function formatList($user){
    $list=[
      'id' => 'list'.$this->name,
      'route' => 'genericlist',
      'routeParams' => ["module" => $this->module,
                        "name" => $this->name],
      'orderColumn' => 2,
      'orderDirection' => 'ASC',
      'tagColumn' => 2,
      'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name.".json"),true),
      'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."FieldButtons.json"),true),
      'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/".$this->name."TopButtons.json"),true)
    ];
    return $list;
  }

  public function setdatetime($params){
    $config=new GlobaleConfigVars();
    $ch = curl_init();
    $post = [
        'command' => 'setDateTime',
        'id' => $params["id"]
    ];
    curl_setopt($ch, CURLOPT_URL,$config->cdeCommand);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $result= curl_exec ($ch);
    curl_close ($ch);
  }

  public function setuserdata($params){
    $config=new GlobaleConfigVars();
    $ch = curl_init();
    $worker=$params["worker"];
    $name=$worker->getName().' '.$worker->getLastname();
    $name=explode(" ", $name);
    $username=$name[0].' ';
    array_shift($name);
    foreach ($name as $w) {
     $username .= strtoupper($w[0]);
    }
    $post = [
        'command' => 'setUser',
        'id' => $params["id"],
        'idd' =>$params["idd"],
        'username' =>$username
    ];
    curl_setopt($ch, CURLOPT_URL,$config->cdeCommand);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $result= curl_exec ($ch);
    curl_close ($ch);
  }


}
