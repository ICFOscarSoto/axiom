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
    $tempPath=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.$params["user"]->getId().DIRECTORY_SEPARATOR;
    if (!file_exists($tempPath) && !is_dir($tempPath)) {
  			mkdir($tempPath, 0775, true);
  	}
    $i=0;
    $row=0;
    $col=0;
    $colOffset=15;
    $rowOffset=-10;
    foreach ($params["locations"] as $key=> $location){
      if($i%2==0){
        $row++;
        $col=0;
      }else $col=1;
      $qrcode = new QRCode($options);
      $path=$tempPath.'loc-'.$location['id'].'.png';
      $qrcode->render('LOC.'.$location['name'], $path);

      $this->pdf->Rect($col*90+$colOffset, $row*28+$rowOffset, 90, 28);
      $this->pdf->Image($path, $col*90+$colOffset-4, $row*28+$rowOffset-4, 36, 36);
      $this->pdf->SetFont('Arial','b',40);

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
      $i++;
    }
    //$this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'logoEAN.png', 2, 6, 13, 13);
    /*$this->pdf->Image($params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR.'logoEAN.png', 47, 6, 14, 14);
    if(!$this->TestCheckDigit($params["barcode"])) $this->pdf->Code39(6,5,$params["barcode"],0.5,16);
     //else $this->pdf->EAN13(20,5,$params["barcode"],16,.40);
     else $this->pdf->EAN13(6,5,$params["barcode"],16,.40);
    //$this->pdf->SetY(-9.5);
    $this->pdf->SetY(-9.5);
    $this->pdf->SetX(0);
    $this->pdf->SetFont('Arial','',9);
    $this->pdf->MultiCell(62,3,utf8_decode($params["name"]),0,'C',0);
    //$this->pdf->SetXY(0,0);
    $this->pdf->SetXY(0,22);
    $this->pdf->Cell(62,5,utf8_decode($params["code"]),0,0,'C');*/

    return $this->pdf->Output();

  }
}
