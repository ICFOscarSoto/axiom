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
use App\Modules\HR\Entity\HRSickleaves;
use App\Modules\Globale\Reports\GlobaleReports;


class HRClocksReports
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

  private function docHeader(){
    $company=$this->user->getCompany();
    $this->pdf->SetFillColor(210, 210, 210);
    $this->pdf->SetDrawColor(210, 210, 210);
    $this->pdf->SetTextColor(50,50,50);
    $this->pdf->SetFont('Arial','B',10);
    $this->pdf->Cell(190,6,utf8_decode("REGISTRO DIARIO DE JORNADA: TRABAJADOR A JORNADA COMPLETA/PARCIAL"),'TRL','R','L');
    $this->pdf->Ln(4);
    $this->pdf->SetFont('Arial','',8);
    $this->pdf->Cell(190,6,utf8_decode("En cumplimiento a la obligación establecida en los art 12.5h y 35.5 del Estatuto de los Trabajadores"),'BLR','R','L');
    $this->pdf->Ln(6);
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
    $this->pdf->Ln(6);
    $this->pdf->Cell(20,6,utf8_decode("MES: "),1,0,'L',true);
    $this->pdf->Cell(35,6,utf8_decode(strtoupper($this->monthNames[$this->month-1])),1,0,'C',false);
    $this->pdf->Cell(20,6,utf8_decode("AÑO: "),1,0,'L',true);
    $this->pdf->Cell(35,6,utf8_decode($this->year),1,0,'C',false);
    $this->pdf->Ln(10);
  }

  function create($params){
    $this->pdf  = new GlobaleReports();

    $this->pdf->AliasNbPages();
    //$this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $this->month=$params["month"];
    $this->year=$params["year"];
    $workersRepository=$doctrine->getRepository(HRWorkers::class);
    $clocksRepository=$doctrine->getRepository(HRClocks::class);
    $vacationsRepository=$doctrine->getRepository(HRVacations::class);
    $sickleaveRepository=$doctrine->getRepository(HRSickleaves::class);
    foreach($params["ids"] as $id){
      $this->worker=$workersRepository->findOneBy(["id"=>$id, "company"=>$this->user->getCompany()]);
      if(!$this->worker) continue;
      $columns=[["name"=>"FECHA","width"=>20, "align"=>"C"], //190
                ["name"=>"ENTRADA","width"=>30,"align"=>"C"],
                ["name"=>"SALIDA","width"=>30,"align"=>"C"],
                ["name"=>"HORAS","width"=>30,"align"=>"R"],
                ["name"=>"OBSERVACIONES","width"=>80]
      ];

      $daysMonth = cal_days_in_month(CAL_GREGORIAN, $params["month"], $params["year"]); // 31
      $data=[];
      $totalTime=0;
      for($i=1;$i<=$daysMonth;$i++){
        $rows=$clocksRepository->dayClocks($this->worker, $params['year'].'-'.sprintf("%02d", $params["month"]).'-'.sprintf("%02d", $i));
        $vacation=$vacationsRepository->dayVacations($this->worker, $params['year'].'-'.sprintf("%02d", $params["month"]).'-'.sprintf("%02d", $i));
        $vacation_literals=array("", "Vacaciones", "Permiso", "Asuntos propios", "Excedencia");
        $sickleave=$sickleaveRepository->daySickleave($this->worker, $params['year'].'-'.sprintf("%02d", $params["month"]).'-'.sprintf("%02d", $i));
        $sickleave_literals=array("", "BAJA C.COMÚN", "BAJA C.PROFESIONAL");

        foreach($rows as $row){
          $totalTime+=$row["time"];
          $data[]=[sprintf("%02d", $i)."/".sprintf("%02d", $params["month"])."/".$params["year"],$row["start"],$row["end"],gmdate("H:i:s", $row["time"]),$row["invalid"]!=0?"*INCIDENCIA. ".$row["observations"]:$row["observations"]];

        }
        $start=" - ";
        $end=" - ";
        $hours=0;
        $observations="";
        if($vacation){
          $start=" ------------------------ ";
          $observations=strtoupper($vacation_literals[$vacation["type"]]);
          $end=" ------------------------ ";
          $hours=" --------------------------- ";
        }else{
          if($sickleave){
            $start=" ------------------------ ";
            $observations=strtoupper($sickleave_literals[$sickleave["type"]].": ".$sickleave["name"]);
            $end=" ------------------------ ";
            $hours=" --------------------------- ";
          }
        }
        if(count($rows)==0) $data[]=[sprintf("%02d", $i)."/".sprintf("%02d", $params["month"])."/".$params["year"],$start,$end,$hours,$observations];
      }
      $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
      $this->pdf->user=$params["user"];
      $this->pdf->workcenters=$this->worker->getWorkcenters();
      $this->pdf->AddPage();
      $result=0;
      while(count($data)){
        $this->docHeader();
        $data=$this->pdf->Table($data,$columns);
      }
      $this->pdf->Cell(20,6,"",1,0,'L',true);
      $this->pdf->Cell(60,6,utf8_decode("Total horas trabajadas: "),1,0,'R',true);
      $this->pdf->Cell(30,6,utf8_decode($this->secToH($totalTime)),1,0,'R',false);
      $this->pdf->Cell(80,6,"",'T');
      $this->pdf->Ln(26);
      $this->pdf->Cell(95,6,utf8_decode("Firmado la Empresa"),0,0,'C',false);
      $this->pdf->Cell(95,6,utf8_decode("Firmado el Trabajador"),0,0,'C',false);
      $this->pdf->Ln();
      $this->pdf->Cell(10,6,"");
      $this->pdf->MultiCell(170,3,utf8_decode("La firma de este documento por parte de ámbas partes es imprescindible en caso de existir cualquier tipo de incidencia en el perido contemplado en este informe."),0,'C');
      $this->pdf->Cell(10,6,"");
    }
    return $this->pdf->Output();

}
}
