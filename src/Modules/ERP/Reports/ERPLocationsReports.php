<?php
namespace App\Modules\ERP\Reports;
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
use App\Modules\HR\Entity\HRClocks;
use App\Modules\HR\Entity\HRVacations;
use App\Modules\HR\Entity\HRSickleaves;
use App\Modules\HR\Entity\HRHollidays;
use \FPDF;
use App\Modules\Globale\Reports\GlobaleReports;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;

class ERPLocationsReports{
  public $params;
  public $pdf;

  function TestCheckDigit($barcode){
  	//Test validity of check digit
  	$sum=0;
  	for($i=1;$i<=11;$i+=2)
  		$sum+=3*$barcode[$i];
  	for($i=0;$i<=10;$i+=2)
  		$sum+=$barcode[$i];
  	return ($sum+$barcode[12])%10==0;
  }

  function create($params){
    setlocale( LC_NUMERIC, 'es_ES' );
    $tempPath=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$params["user"]->getId().DIRECTORY_SEPARATOR;
    if (!file_exists($tempPath) && !is_dir($tempPath)) {
        mkdir($tempPath, 0775, true);
    }
    if(!isset($params["type"]) || $params["type"]==1){
      $this->pdf  = new \FPDF('P','mm','A4');
      $this->pdf->AliasNbPages();
      $this->pdf->SetAutoPageBreak(false);
      $this->pdf->AddPage();
      $this->pdf->SetFont('Arial','',12);
      $options = new QROptions([
        'version'    => 1,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_M,
      ]);

      $i=0;
      $row=0;
      $col=0;
      $colOffset=15;
      $rowOffset=-10;
      foreach ($params["locations"] as $key=> $location){

        if($i%2==0){
          if($row>=9){
            $row=0;
            $col=0;
            $this->pdf->AddPage();
          }
          $row++;
          $col=0;
        }else $col=1;


        $qrcode = new QRCode($options);
        $path=$tempPath.'loc-'.$location['id'].'.png';
        $qrcode->render('LOC.'.$location['name'], $path);

        $this->pdf->Rect($col*90+$colOffset, $row*28+$rowOffset, 90, 28);
        $this->pdf->Image($path, $col*90+$colOffset-4, $row*28+$rowOffset-4, 36, 36);
        $textSize=40;
        $textSize=$textSize-(strlen($location['name']));
        if(strlen($location['name'])>=8) $this->pdf->SetFont('Arial','b',$textSize);
          else $this->pdf->SetFont('Arial','b',40);

        if($location['orientation']==0){
          $this->pdf->SetXY($col*90+$colOffset+28, $row*28+$rowOffset+16);
          $this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'locations'.DIRECTORY_SEPARATOR.'arrow_up.png', $col*90+$colOffset+35, $row*28+$rowOffset+2,46,12);
          $this->pdf->Cell(62, 12, $location['name'], 0, 0, 'C');
        }
        if($location['orientation']==1){
          $this->pdf->SetXY($col*90+$colOffset+28, $row*28+$rowOffset+2);
          $this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'locations'.DIRECTORY_SEPARATOR.'arrow_down.png', $col*90+$colOffset+35, $row*28+$rowOffset+15,46,12);
          $this->pdf->Cell(62, 12, $location['name'], 0, 0, 'C');
        }
        unlink($path);
        $i++;
      }
    }
    if(!isset($params["type"]) || $params["type"]==2){
      $this->pdf  = new \FPDF('P','mm','A4');
      $this->pdf->AliasNbPages();
      $this->pdf->SetAutoPageBreak(false);
      $this->pdf->AddPage();
      $this->pdf->SetFont('Arial','',12);
      $options = new QROptions([
        'version'    => 1,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_M,
        'scale' => 26
      ]);

      $i=1;
      $this->pdf->SetFillColor(255, 248, 53);
      $this->pdf->Rect(0, 0, 210, 148, 'DF');
      $this->pdf->Rect(0, 148, 210, 149, 'DF');
      $this->pdf->SetFont('Arial','b',90);
      foreach ($params["locations"] as $key=> $location){
        if($i%3==0){
          $this->pdf->AddPage();
          $this->pdf->Rect(0, 0, 210, 148, 'DF');
          $this->pdf->Rect(0, 148, 210, 149, 'DF');
            $i=1;
        }
        $qrcode = new QRCode($options);
        $path=$tempPath.'loc-'.$location['id'].'.png';
        $qrcode->render('LOC.'.$location['name'], $path);
        $this->pdf->Image($path, 30, ($i-1)*150-17, 150, 150);
        $this->pdf->SetXY(0,($i-1)*150+105);
        $this->pdf->Cell(210, 50, $location['name'], 0, 0, 'C');
        $i++;

        unlink($path);
      }
    }
    if($params["type"]==3){
      $this->pdf  = new \FPDF('L','mm',array(38,62));
      $this->pdf->AliasNbPages();
      $this->pdf->SetAutoPageBreak(false);
      $this->pdf->SetFont('Arial','',24);
      $options = new QROptions([
        'version'    => 1,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_M,
        'scale' => 26
      ]);

      foreach ($params["locations"] as $key=> $location){
        $this->pdf->AddPage();
        $qrcode = new QRCode($options);
        $path=$tempPath.'loc-'.$location['id'].'.png';
        $qrcode->render('LOC.'.$location['name'], $path);
        $this->pdf->Image($path, 1, 7.5, 40, 40);
        $this->pdf->SetXY(0,5);

        $this->pdf->Cell(65, -1.5, $location['name'], 0, 0, 'C');
        if($location['orientation']==0){
          $this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'locations'.DIRECTORY_SEPARATOR.'arrow_up.png', 32.5,13,28,15);
        }
        if($location['orientation']==1){
        $this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'locations'.DIRECTORY_SEPARATOR.'arrow_down.png', 32.5,15,28,15);
      }


        unlink($path);
      }
    }

    return $this->pdf->Output();

  }
}
