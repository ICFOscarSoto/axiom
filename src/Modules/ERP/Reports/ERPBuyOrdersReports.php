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
use App\Modules\ERP\Entity\ERPBuyOrders;
use App\Modules\ERP\Entity\ERPBuyOrdersLines;
use App\Modules\ERP\Entity\ERPReferences;
use App\Modules\Globale\Reports\GlobaleReports;


class ERPBuyOrdersReports
{
  private $pdf;
  private $user;
  private $worker;
  private $monthNames=["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];
  private $month, $year;
  private $pageNo=1;

  private function secToH($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    return sprintf("%02d", $hours).":".sprintf("%02d", $minutes).":".sprintf("%02d", $seconds);
  }

  function create($params, $dest='I', $file=null){
    setlocale( LC_NUMERIC, 'es_ES' );
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $order=$params["order"];
    $decimals=$params["decimals"];
    $contactssupplier=$params["contactssupplier"];
    $contactscustomer=$params["contactscustomer"];
    if ($decimals==null || $decimals=='')
      $decimals = 2;
    //$numOffer=$order->getTheiroffer()!=null?$order->getTheiroffer():$order->getOuroffer()!=null?$order->getOuroffer():' ';
    // TODO
    $numOffer = ' ';
    $type='Compra';
    $infoLeft=[["Fecha",$order->getDateadd()->format('d/m/Y')],
               ["Gestor",($order->getAgent()->getName().' '.$order->getAgent()->getLastname().'  extension ...')],
               ["Mail",$order->getAgent()->getEmail()],
               ["Fecha requerida",$order->getEstimateddelivery()->format('d/m/Y')],
               ["Oferta ",$numOffer]
              ];
    $shipment=$order->getFreeshipping()==1?' Portes pagado':' Portes debidos';
    $direct=$order->getStore()->getId()==6?'Envío directo. Albarán sin valorar.':'';
    $infoRight=[ ["Condiciones",$direct.$shipment],
                ["Direccion",($order->getDestinationname().' - '.$order->getDestinationaddress().' - '.$order->getDestinationcity().' - '.$order->getDestinationpostcode().' - '.$order->getDestinationstate()->getName())]
              ];
    if ($contactscustomer!=null){
      for($i=0; $i<count($contactscustomer);$i++){
        array_push($infoRight, ["Contacto", ($contactscustomer[$i]->getName().' - '.$contactscustomer[$i]->getPhone())]);
      }
    }
    $infoCenter=[["Proveedor",($order->getSuppliercode().' - '.$order->getSuppliername())],
                ["Términos de pago",($order->getPaymentterms()!=null?$order->getPaymentterms()->getName():'')],
                ["Mail",($contactssupplier!=null && count($contactssupplier)>0 && $contactssupplier[0]->getEmail()!=null &&  $contactssupplier[0]->getEmail()!=''?$contactssupplier[0]->getEmail():$order->getEmail())],
                ["Telefono",($contactssupplier!=null && count($contactssupplier)>0 && $contactssupplier[0]->getPhone()!=null &&  $contactssupplier[0]->getPhone()!=''?str_replace(' ','',$contactssupplier[0]->getPhone()):str_replace(' ','',$order->getPhone()))],
                ["Contacto",($contactssupplier!=null && count($contactssupplier)>0 && $contactssupplier[0]->getName()!=null &&  $contactssupplier[0]->getName()!=''?$contactssupplier[0]->getName():'')]
              ];

    $lines=$params["lines"];
    $nameLeft='Pedido de compra '.$order->getCode();
    $nameRight='Información de envío';
    $referencesRepository=$doctrine->getRepository(ERPReferences::class);
    $this->pdf->StartPageGroup($type);
      // ancho 190
    $columnsTable=[["name"=>"Nº","width"=>6, "align"=>"R"],
                ["name"=>"Código","width"=>25, "align"=>"L"],
                ["name"=>"Descripción","width"=>94,"align"=>"L"],
                ["name"=>"Variante","width"=>17,"align"=>"L"],
                ["name"=>"Ud compra","width"=>13,"align"=>"R"],
                ["name"=>"Cantidad","width"=>13,"align"=>"R"],
                ["name"=>"Precio","width"=>17,"align"=>"R"],
                ["name"=>"% Dto. 1","width"=>15,"align"=>"R"],
                ["name"=>"% Dto. 2","width"=>15,"align"=>"R"],
                ["name"=>"% Dto Total","width"=>15,"align"=>"R"],
                ["name"=>"Neto","width"=>17,"align"=>"R"],
                ["name"=>"Importe","width"=>17,"align"=>"R"],
                ["name"=>"Fecha","width"=>18,"align"=>"R"]
    ];

    $dataTable=[];
    $last_shipping=null;
    foreach($lines as $line){
      $referenceSupplier=$referencesRepository->getReferenceByProductSupplier($order->getSupplier()->getId(),$line->getProduct()->getId());
          $dataTable[]=[$line->getLinenum(),
                  $referenceSupplier,
                  $line->getProductname(),
                  ($line->getVariant()!=null && $line->getVariant()->getId()!=null && $line->getVariant()->getId()!=0?$line->getVariantname().' - '.$line->getVariantvalue():''),
                  $line->getPurchaseunit(),
                  $line->getQuantity(),
                  number_format($line->getPvp(),intval($decimals),',','.'),
                  $line->getDiscount1(),
                  $line->getDiscount2(),
                  $line->getDiscountequivalent(),
                  number_format($line->getShoppingprice(),intval($decimals),',','.'),
                  number_format($line->getTotal(),intval($decimals),',','.'),
                  $line->getDateestimated()->format('d/m/Y')
                ];
      }

      $columnsFooter=[["name"=>"Importe","width"=>"35"],
                  ["name"=>"Descuento adicional","width"=>"35"],
                  ["name"=>"Base imponible","width"=>"35"],
                  ["name"=>"% IVA","width"=>"35"],
                  ["name"=>"IVA","width"=>"35"],
                  ["name"=>"Gastos de envío","width"=>"35"],
                  ["name"=>"Total","width"=>"35", "bold"=>"B"]
      ];

      // TODO Líneas para cada impuesto
      // TODO Tener en cuanta descuento y portes
      $tax = 21;
      $dataFooter=[number_format($order->getAmount(),2,',','.').json_decode('"\u0080"'),
                  ($order->getDiscount()!=null && $order->getDiscount()!=''?number_format($order->getDiscount(),2,',','.').'%':'0'),
                  number_format($order->getBase(),2,',','.').json_decode('"\u0080"'),
                  number_format($tax).'%',
                  number_format($order->getTaxes(),2,',','.').json_decode('"\u0080"'),
                  ($order->getShipping()!=null && $order->getShipping()!=''?number_format($order->getShipping(),2,',','.').json_decode('"\u0080"'):'0'),
                  number_format($order->getTotal(),2,',','.').json_decode('"\u0080"')
                  ];

      $this->pdf->image_path=$params["rootdir"].DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$params["user"]->getCompany()->getId().DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
      $this->pdf->user=$params["user"];

      $this->pdf->AddPage('L');
      $noDataFooter=['-',
                  '-',
                  '-',
                  '-',
                  '-',
                  '-',
                  'Suma y sigue'
                  ];
      $result=0;
      while(count($dataTable)){
        $this->pdf->docHeader($nameLeft,$nameRight,$infoLeft, $infoRight, $infoCenter);
        $this->pdf->docFooter($order,$columnsFooter,$noDataFooter);
        $dataTable=$this->pdf->Table($dataTable,$columnsTable,'false');

      }

      $this->pdf->docFooter($order,$columnsFooter,$dataFooter);

      return $this->pdf->Output($dest, $file==null?$order->getCode().".pdf":$file);
  }
}
