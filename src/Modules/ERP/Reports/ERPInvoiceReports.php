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

use App\Modules\Globale\Reports\GlobaleReports;


class ERPInvoiceReports
{
  private $pdf;
  private $user;
  private $worker;
  private $monthNames=["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];
  private $month, $year;

  private function secToH($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    return sprintf("%02d", $hours).":".sprintf("%02d", $minutes).":".sprintf("%02d", $seconds);
  }
  private function docFooter($invoice){
    $x=$this->pdf->getX();
    $y=$this->pdf->getY();
    $this->pdf->SetTextColor(255,255,255);
    $this->pdf->SetY(-55);
    $this->pdf->SetX(10);
    $this->pdf->Cell(190,1,'','',0,'C',true);
    $this->pdf->SetY(-43);
    $this->pdf->SetX(10);
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->Cell(34,4,utf8_decode('IMPORTE'),'',0,'C',true);
    $this->pdf->Cell(33,4,utf8_decode('DTO. P.P.'),'',0,'C',true);
    $this->pdf->Cell(35,4,utf8_decode('BASE IMPONIBLE'),'',0,'C',true);
    $this->pdf->Cell(18,4,utf8_decode('% IVA'),'',0,'C',true);
    $this->pdf->Cell(37,4,utf8_decode('CTA. IVA'),'',0,'C',true);
    /*$this->pdf->Cell(31,4,utf8_decode('REC. EQUIVALENCIA'),'',0,'C',true);*/
    $this->pdf->Cell(33,4,utf8_decode('TOTAL'),'',0,'C',true);
    $this->pdf->Ln(4.1);
    $this->pdf->SetX(10);
    //$pdf->SetFillColor(248, 250, 255);
    $this->pdf->SetFillColor(248, 250, 255);
    $this->pdf->SetDrawColor(248, 250, 255);
    $this->pdf->setTextColor(0,0,0);
    $this->pdf->SetFont('Arial','b',9);
    $this->pdf->Cell(34,8,utf8_decode(number_format($invoice['linestotal'],2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $this->pdf->Cell(33,8,utf8_decode(number_format($invoice['dto'],2,',','.').json_decode('"\u0080"')),'TB',0,'C',false);
    $this->pdf->Cell(35,8,utf8_decode(number_format($invoice['base'],2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $vat_string="";
    foreach($invoice['vat'] as $key=>$vat){
      $vat_string.=$vat["caption"].' ';
    }
    $this->pdf->Cell(18,8,utf8_decode($vat_string),'TB',0,'C',false);
    $this->pdf->Cell(37,8,utf8_decode(number_format($invoice['vattotal'],2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    /*$this->pdf->Cell(31,8,utf8_decode(''),'TB',0,'C',false);*/
    $this->pdf->Cell(33,8,utf8_decode(number_format($invoice['total'],2,',','.').json_decode('"\u0080"')),'TB',0,'C',true);
    $this->pdf->Ln(12);
    $this->pdf->SetX(10);
    $this->pdf->SetFont('Arial','',7);
    $this->pdf->SetFillColor(248, 250, 255);

    $this->pdf->Cell(40,3,utf8_decode('Forma de pago'),'B',0,'L',true);
    $this->pdf->Cell(150,3,utf8_decode($invoice['paycode']." ".$invoice['paydesc']),'B',0,'L',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(40,3,utf8_decode('Datos bancarios'),'B',0,'L',true);
    $this->pdf->Cell(150,3,utf8_decode($invoice['customer']." ".$invoice['IBAN']),'B',0,'L',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(40,3,utf8_decode('Vencimientos'),'B',0,'L',true);
    $shadow=40;
    foreach($invoice['expirations'] as $expiration){
      $this->pdf->Cell(20,3,utf8_decode(date("d/m/Y",strtotime($expiration["date"]["date"]))),'B',0,'R',true);
      $shadow+=20;
    }
    $this->pdf->Cell(190-$shadow,3,"",'B',0,'R',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(40,3,utf8_decode('Importe'),'B',0,'L',true);
    foreach($invoice['expirations'] as $expiration){
      $this->pdf->Cell(20,3,utf8_decode($expiration["amount"].json_decode('"\u0080"')),'B',0,'R',true);

    }
    $this->pdf->Cell(190-$shadow,3,"",'B',0,'R',true);
    $this->pdf->Ln(3);
    $this->pdf->SetX(10);
    $this->pdf->Cell(190,3,utf8_decode(''),'B',0,'L',true);
    $this->pdf->SetXY(10,244);
    $this->pdf->SetFont('Arial','',6);
    $this->pdf->SetTextColor(75,75,75);
    $this->pdf->MultiCell(190, 3, utf8_decode('Los datos personales facilitados pasarán a formar parte de un fichero de clientes, para la gestión y facturación de sus pedidos. De acuerdo con la Ley Orgánica 15/1999 del 13 de diciembre, esta sociedad guarda las medidas de confidencialidad establecidas en dicha Ley. Usted podrá ejercer sus derechos de acceso, rectificación y cancelación en la dirección de esta Sociedad.'),0,'C', false);
    $this->pdf->SetXY(0,0);
    $this->pdf->TextWithRotation(8,258,utf8_decode('Industrial Campollano Ferretería S.L. inscrita en el Registro Mercantil de Albacete, Tomo 669, Libro 453, Folio 208, Sección 8ª, Hoja AB-9552, Inscripcción 1ª. CIF B02290443'), 90);
    $this->pdf->setXY($x, $y);
  }

  private function docHeader($invoice){
    $this->pdf->SetDrawColor(44, 132, 194);
    $this->pdf->SetFillColor(44, 132, 194);
    $this->pdf->SetTextColor(44, 132, 194);
    $this->pdf->Rect(105, 41, 1.5, 29,'DF');
    $this->pdf->Rect(198, 41, 1.5, 29,'DF');
    $x=$this->pdf->getX();
    $y=$this->pdf->getY();
    $this->pdf->setXY(5, 39);
    $this->pdf->SetDrawColor(248, 250, 255);
    $this->pdf->Cell(20,9,utf8_decode('Nº Factura'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($invoice["number"]),'',0,'L',false);
    $this->pdf->SetTextColor(44, 132, 194);
    $this->pdf->Ln(4);
    $this->pdf->Cell(20,9,utf8_decode('Fecha'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $date=strtotime($invoice["date"]["date"]);
    $this->pdf->Cell(60,9,utf8_decode(date("d/m/Y",$date)),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->SetTextColor(44, 132, 194);
    $this->pdf->Cell(20,9,utf8_decode('Cliente'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($invoice["customerId"]),'',0,'L',false);
    $this->pdf->Ln(4);
    $this->pdf->SetTextColor(44, 132, 194);
    $this->pdf->Cell(20,9,utf8_decode('Página'),'',0,'L',false);
    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->Cell(60,9,utf8_decode($this->pdf->PageNo().'/{nb}'),'',0,'L',false);

    $this->pdf->setXY(110, 40);
    $this->pdf->Cell(60,9,utf8_decode($invoice["customer"]),'',0,'L',false);
    $this->pdf->setXY(110, 44);
    $this->pdf->Cell(60,9,utf8_decode($invoice["address"]),'',0,'L',false);
    $this->pdf->setXY(110, 48);
    $this->pdf->Cell(60,9,utf8_decode($invoice["city"]." - ".$invoice["postcode"]." - ".$invoice["state"]),'',0,'L',false);
    $this->pdf->setXY(110, 52);
    $this->pdf->Cell(60,9,utf8_decode("NIF/NIE ".$invoice["vat_number"]),'',0,'L',false);
    $this->pdf->SetTextColor(44, 132, 194);
    $this->pdf->setXY(5, 31);
    $this->pdf->SetDrawColor(248, 250, 255);
    $this->pdf->SetFont('Arial','',14);
    $this->pdf->Cell(60,9,utf8_decode('FACTURA'),'',0,'L',false);




    $this->pdf->setXY($x, $y);


  }

  function create($params){
    setlocale( LC_NUMERIC, 'es_ES' );
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $invoices=$params["invoices"];
    foreach($invoices as $invoice){

      $columns=[["name"=>"CÓDIGO","width"=>30, "align"=>"L"], //190
                ["name"=>"DESCRIPCIÓN","width"=>80,"align"=>"L"],
                ["name"=>"UNIDADES","width"=>22,"align"=>"C"],
                ["name"=>"PRECIO","width"=>23,"align"=>"R"],
                ["name"=>"DTO.","width"=>13,"align"=>"C"],
                ["name"=>"IMPORTE","width"=>22,"align"=>"R"]
      ];

      $data=[];
      $last_shipping=null;
      for($i=0;$i<count($invoice["lines"]);$i++){
        if($invoice["lines"][$i]["shipment"]!=$last_shipping){
          if($i>0)$data[]=["","","","","",""];
          $last_shipping=$invoice["lines"][$i]["shipment"];
          $data[]=["","#b#Nº Albarán ".$invoice["lines"][$i]["shipment"],"","","",""];
          $data[]=["","#b#".($invoice["lines"][$i]["contact"]!=""?$invoice["lines"][$i]["contact"]:$invoice["customer"]),"","","",""];
          if($invoice["lines"][$i]["customerreference"]!="") $data[]=["","SU PEDIDO Nº ".$invoice["lines"][$i]["customerreference"],"","","",""];
        }

        $data[]=[$invoice["lines"][$i]["referencecross"]!=""?$invoice["lines"][$i]["referencecross"]:$invoice["lines"][$i]["reference"],$invoice["lines"][$i]["description"],$invoice["lines"][$i]["quantity"],number_format($invoice["lines"][$i]["price"],4,',','.').json_decode('"\u0080"'),$invoice["lines"][$i]["desicount"].'%',number_format($invoice["lines"][$i]["linetotal"],2,',','.').json_decode('"\u0080"')];
      }
      $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
      $this->pdf->user=$params["user"];

      $this->pdf->AddPage();

      $result=0;
      while(count($data)){
        $this->docHeader($invoice);
$this->docFooter($invoice);
        $this->pdf->SetY(80);
        $data=$this->pdf->Table($data,$columns);

      }

    }
    return $this->pdf->Output();

}
}
