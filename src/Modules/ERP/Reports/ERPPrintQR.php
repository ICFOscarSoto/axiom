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
use \FPDF;
use App\Modules\Globale\Reports\GlobaleReports;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;
use App\Modules\Navision\Entity\NavisionTransfers;

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
    $this->pdf->Image($path, 16, 7, 32, 32);
    $this->pdf->SetXY(0,5);

    if (strpos($params['name'],'TR.') !== null) $txt=substr($params['name'],3);
    else $txt=$params['name'];
    $this->pdf->Cell(65, 5.5, $txt, 0, 0, 'C');
    unlink($path);
    return $this->pdf->Output();
}

  function transferQR($params){
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $this->pdf->SetFont('Arial','',20);
    $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
    $this->pdf->user=$params["user"];
    $this->pdf->StartPageGroup();
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

    //$this->pdf->Image($path, 16, 7, 32, 32);
    $nameLeft="Traspaso numero ".substr($params['name'],3);
    $transfers=$params['transfers'];
    $infoLeft=[["Fecha",$transfers[0]->getDatesend()->format('d/m/Y')],
               ["Origen",$transfers[0]->getOriginstore()->getName()],
               ["Destino",$transfers[0]->getDestinationstore()->getName()]
              ];
    $infoRight=[['',$this->pdf->Image($path, 135, 28, 35, 35)]];
    foreach ($transfers as $line){
      $dataTable[]=[
        $line->getProduct()->getCode(),
        $line->getProduct()->getname(),
        $line->getQuantity()
      ];
    }

    $columnsTable=[["name"=>"Código","width"=>30, "align"=>"L"],
                  ["name"=>"Descripcion","width"=>80, "align"=>"L"],
                  ["name"=>"Cantidad","width"=>20, "align"=>"C"]
    ];
    while(count($dataTable)){
      $this->pdf->docHeader($nameLeft,'',$infoLeft, $infoRight);
      $this->pdf->docFooter('','','');
      $dataTable=$this->pdf->Table($dataTable,$columnsTable,'false');
    }
      unlink($path);
      return $this->pdf->Output();
  }


  function loadMachine($params){
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $this->pdf->SetFont('Arial','',20);
    $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
    $this->pdf->user=$params["user"];
    $this->pdf->StartPageGroup();

    $this->pdf->AddPage();
      //$this->pdf->Image($path, 16, 7, 32, 32);
    $nameLeft="Carga de productos ";
    $infoLeft=[["Fecha", $params['date']],
               ["Origen", "Almacén Expendedoras"],
               ["Destino", $params['machine']]
              ];
    $lines=$params['lines'];
    foreach ($lines as $line){
      if ($line['upload']!='-') $upload=intval($line['upload']);
      else  $upload='-';
      $dataTable[]=[
        $line['productcode'],
        $line['productname'],
        $line['quantity'],
        $upload,
        $line['multiplier']
      ];
    }

    $columnsTable=[["name"=>"Código","width"=>30, "align"=>"L"],
                  ["name"=>"Descripcion","width"=>80, "align"=>"L"],
                  ["name"=>"Cantidad","width"=>20, "align"=>"C"],
                  ["name"=>"Dispensada","width"=>20, "align"=>"C"],
                  ["name"=>"Multiplicador","width"=>20, "align"=>"C"]
    ];
    while(count($dataTable)){
      $this->pdf->docHeader($nameLeft,'',$infoLeft, '');
      $this->pdf->docFooter('','','');
      $dataTable=$this->pdf->Table($dataTable,$columnsTable,'false');
    }
    return $this->pdf->Output();
  }

  function downloadTransfers($params){
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $this->pdf->SetFont('Arial','',20);
    $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
    $this->pdf->user=$params["user"];
    $this->pdf->StartPageGroup();
    $options = new QROptions([
      'version'    => 1,
      'outputType' => QRCode::OUTPUT_IMAGE_PNG,
      'eccLevel'   => QRCode::ECC_M,
      'scale' => 26
    ]);

    foreach ($params["transfers"] as $key=>$transfer) {
      $this->pdf->AddPage();
      $qrcode = new QRCode($options);
      $path=$transfer['name'].'.png';
      $qrcode->render($transfer['name'], $path);

      //$this->pdf->Image($path, 16, 7, 32, 32);
      $nameLeft="Traspaso número ".$transfer['name'];
      $lines=$transfer['lines'];
      $infoLeft=[["Fecha",$transfer["datesend"]],
                 ["Origen",$transfer["origin"]],
                 ["Destino",$transfer["destination"]]
                ];
      $infoRight=[['',$this->pdf->Image($path, 135, 28, 35, 35)]];
      foreach ($lines as $line){
        $dataTable[]=[
          $line["productcode"],
          $line["productname"],
          $line["quantity"],
          $line["upload"],
          $line["multiplier"]
        ];
      }

      $columnsTable=[["name"=>"Código","width"=>30, "align"=>"L"],
                    ["name"=>"Descripcion","width"=>80, "align"=>"L"],
                    ["name"=>"Cantidad","width"=>20, "align"=>"C"],
                    ["name"=>"Paquetes","width"=>20, "align"=>"C"],
                    ["name"=>"Multiplos","width"=>20, "align"=>"C"]
      ];
      while(count($dataTable)){
        $this->pdf->docHeader($nameLeft,'',$infoLeft, $infoRight);
        $this->pdf->docFooter('','','');
        $dataTable=$this->pdf->Table($dataTable,$columnsTable,'false');
      }
        unlink($path);
    }
    return $this->pdf->Output();
  }


}
