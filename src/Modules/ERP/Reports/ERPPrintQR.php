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

class ERPPrintQR{
  public $params;
  public $pdf2;



function create($params){
  $this->pdf  = new \FPDF('L','mm',array(38,62));
  $this->pdf->AliasNbPages();
  $this->pdf->SetAutoPageBreak(false);
  $this->pdf->SetFont('Arial','',20);
  $options = new QROptions([
    'version'    => 1,
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'   => QRCode::ECC_M,
    'scale' => 26
  ]);

    $this->pdf->AddPage();
    $qrcode = new QRCode($options);
    $path=$params['name'].'.png';
    $qrcode->render($params['name'], $path);
    $this->pdf->Image($path, -1, 7, 30, 30);
    $this->pdf->SetXY(0,5);
    $this->pdf->Cell(65, 5.5, $params['name'], 0, 0, 'C');
    unlink($path);
    return $this->pdf->Output();
}

}
