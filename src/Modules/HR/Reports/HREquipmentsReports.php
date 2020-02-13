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


  private function docHeader(){
    $company=$this->user->getCompany();
    $this->pdf->SetFillColor(210, 210, 210);
    $this->pdf->SetDrawColor(210, 210, 210);
    $this->pdf->SetTextColor(50,50,50);
    $this->pdf->SetFont('Arial','B',10);
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
    $this->docHeader();

    

    $this->pdf->Cell(95,6,utf8_decode("Firmado la Empresa"),0,0,'C',false);
    $this->pdf->Cell(95,6,utf8_decode("Firmado el Trabajador"),0,0,'C',false);
    $this->pdf->Ln();
    $this->pdf->Cell(10,6,"");
    $this->pdf->Cell(10,6,"");

    return $this->pdf->Output();

}
}
