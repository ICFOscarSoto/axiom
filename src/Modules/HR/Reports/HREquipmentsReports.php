<?php
namespace App\Modules\HR\Reports;
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

use App\Modules\Globale\Reports\GlobaleReports;


class HREquipmentsReports
{
  private $pdf;
  private $user;
  private $worker;
  private $equipment;
  private $monthNames=["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];

  private function docHeader(){
    $company=$this->user->getCompany();
    $this->pdf->SetFillColor(210, 210, 210);
    $this->pdf->SetDrawColor(210, 210, 210);
    $this->pdf->SetTextColor(50,50,50);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Ln(4);
    $this->pdf->Cell(190,6,utf8_decode("ACTA DE ENTREGA DE DOTACIÓN"),'TRL','R','C');
    $this->pdf->Ln(6);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(20,6,utf8_decode("Empresa: "),1,0,'L',true);
    $this->pdf->Cell(70,6,utf8_decode($this->worker->getWorkCenters()!=null?$this->worker->getWorkCenters()->getSocialname():$company->getSocialname()),1,0,'C',false);
    $this->pdf->Cell(20,6,utf8_decode("CIF: "),1,0,'L',true);
    $this->pdf->Cell(30,6,utf8_decode($this->worker->getWorkCenters()!=null?$this->worker->getWorkCenters()->getVAT():$company->getVAT()),1,0,'C',false);
    $this->pdf->Cell(20,6,utf8_decode("CCC: "),1,0,'L',true);
    $this->pdf->Cell(30,6,utf8_decode($company->getSs()),1,0,'C',false);
    $this->pdf->Ln(6);
    $this->pdf->Cell(20,6,utf8_decode("Trabajador: "),1,0,'L',true);
    $this->pdf->Cell(70,6,utf8_decode($this->worker->getName().' '.$this->worker->getLastName()),1,0,'C',false);
    $this->pdf->Cell(20,6,utf8_decode("NIF: "),1,0,'L',true);
    $this->pdf->Cell(30,6,utf8_decode($this->worker->getIdcard()),1,0,'C',false);
    $this->pdf->Cell(20,6,utf8_decode("NAF: "),1,0,'L',true);
    $this->pdf->Cell(30,6,utf8_decode($this->worker->getSs()),1,0,'C',false);
    $this->pdf->Ln(10);
  }

  function create($params){
    $this->pdf  = new GlobaleReports();

    $this->pdf->AliasNbPages();
    //$this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $this->worker=$params["document"]->getWorker();
    $this->equipment=$params["document"]->getEquipment();

    $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
    $this->pdf->user=$params["user"];
    $this->pdf->workcenters=$this->worker->getWorkcenters();

    $this->pdf->AddPage();
    $this->pdf->SetMargins(10, 20);
    $this->docHeader();
    $this->pdf->SetFont('Arial','',10);
    $date = new \DateTime();
    $this->pdf->Ln(5);
    $this->pdf->Cell(190,6,utf8_decode("Fecha: ".$date->format('j')." de ".$this->monthNames[$date->format('n')-1]." de ".$date->format('Y')),'','R','L');
    $this->pdf->Ln(20);
    $this->pdf->MultiCell(190,4,utf8_decode("Con la presente acta la empresa hace entrega de la siguiente dotación al trabajador. Además reconoce haber sido informado de los trabajos y zonas en los que debe utilizar dicho equipo, así como haber recibido las instrucciones para su correcto uso:"),0,'J');
    $this->pdf->Ln(10);
    $this->pdf->Cell(80,6,utf8_decode("Equipo"),1,0,'C',true);
    $this->pdf->Cell(20,6,utf8_decode("Cantidad"),1,0,'C',true);
    $this->pdf->Cell(50,6,utf8_decode("Número Serie"),1,0,'C',true);
    $this->pdf->Cell(40,6,utf8_decode("Fecha Entrega"),1,0,'C',true);
    $this->pdf->Ln(6);
    $this->pdf->Cell(80,6,utf8_decode($this->equipment->getName()),1,0,'C');
    $this->pdf->Cell(20,6,utf8_decode(1),1,0,'C');
    $this->pdf->Cell(50,6,utf8_decode($params["document"]->getSerial()),1,0,'C');
    $this->pdf->Cell(40,6,utf8_decode($params["document"]->getDeliverydate()->format('d/m/Y H:i')),1,0,'C');

    $this->pdf->Ln(20);
    $this->pdf->MultiCell(190,4,utf8_decode("El trabajador acepta los siguientes compromisos que se le solicita:"),0,'J');
    $this->pdf->Ln(5);
    $this->pdf->MultiCell(190,4,utf8_decode("a)	Utilizar este equipo durante la jornada de trabajo en las tareas y/ o áreas cuya obligatoriedad de uso se hay indicado o se encuentre señalizada."),0,'J');
    $this->pdf->Ln(5);
    $this->pdf->MultiCell(190,4,utf8_decode("b)	Consultar cualquier duda sobre su correcta utilización, cuidando de su perfecto estado y conservación."),0,'J');
    $this->pdf->Ln(5);
    $this->pdf->MultiCell(190,4,utf8_decode("c)	Solicitar un nuevo equipo en caso de pérdida o deterioro del mismo."),0,'J');
    $this->pdf->Ln(10);
    $this->pdf->MultiCell(190,4,utf8_decode("Así mismo se informa al trabajador de lo siguiente:"),0,'J');
    $this->pdf->Ln(5);
    $this->pdf->MultiCell(190,4,utf8_decode("a) La dotación que aquí se entrega es y será propiedad de la empresa en todo momento. En caso de terminación del contrato de trabajo o entrega de una nueva dotación el trabajador se compromete a hacer la devolución de forma inmediata."),0,'J');
    $this->pdf->Ln(5);
    $this->pdf->MultiCell(190,4,utf8_decode("b) En caso de daño de la dotación o parte de ella, el trabajador deberá informar y hacer entrega de ella de manera inmediata a la empresa."),0,'J');
    $this->pdf->Ln(50);
    $this->pdf->Cell(95,6,utf8_decode("Firmado la Empresa"),0,0,'C',false);
    $this->pdf->Cell(95,6,utf8_decode("Firmado el Trabajador"),0,0,'C',false);
    $this->pdf->Ln();
    $this->pdf->Cell(10,6,"");
    $this->pdf->Cell(10,6,"");

    return $this->pdf->Output();

}
}
