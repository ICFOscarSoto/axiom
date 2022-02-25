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

  function create($params,  $file=null){
    setlocale( LC_NUMERIC, 'es_ES' );
    $this->pdf  = new GlobaleReports();
    $this->pdf->AliasNbPages();
    $this->pdf->SetAutoPageBreak(false);
    $doctrine=$params["doctrine"];
    $this->user=$params["user"];
    $order=$params["order"];
    $numOffer=$order->getTheiroffer()!=null?$order->getTheiroffer():$order->getOuroffer()!=null?$order->getOuroffer():' ';
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
                ["Direccion",($order->getDestinationname().' - '.$order->getDestinationaddress().' - '.$order->getDestinationcity().' - '.$order->getDestinationpostcode().' - '.$order->getDestinationstate()->getName())],
                ["Contacto", ($order->getDestinationcontact().' - '.$order->getDestinationphone())]
              ];
    $infoCenter=[["Proveedor",($order->getSuppliercode().' - '.$order->getSuppliername())],
                ["Términos de pago",$order->getSupplierpaymentterms()],
                ["Mail",$order->getEmail()],
                ["Telefono",str_replace(' ','',$order->getPhone())],
                ["Contacto",$order->getSuppliercontact()]
              ];


    $lines=$params["lines"];
    $nameLeft='Pedido de compra '.$order->getCode();
    $nameRight='Información de envío';
    $referencesRepository=$doctrine->getRepository(ERPReferences::class);
    $this->pdf->StartPageGroup($type);
      // ancho 190
    $columnsTable=[["name"=>"Nº","width"=>6, "align"=>"C"],
                ["name"=>"Código","width"=>25, "align"=>"L"],
                ["name"=>"Descripción","width"=>110,"align"=>"L"],
                ["name"=>"Variante","width"=>10,"align"=>"C"],
                ["name"=>"Ud compra","width"=>13,"align"=>"C"],
                ["name"=>"Cantidad","width"=>13,"align"=>"C"],
                ["name"=>"Precio","width"=>15,"align"=>"C"],
                ["name"=>"% Dto. 1","width"=>15,"align"=>"C"],
                ["name"=>"% Dto. 2","width"=>15,"align"=>"C"],
                ["name"=>"% Dto Total","width"=>15,"align"=>"C"],
                ["name"=>"Neto","width"=>15,"align"=>"C"],
                ["name"=>"Importe","width"=>15,"align"=>"C"],
                ["name"=>"Fecha","width"=>15,"align"=>"R"]
    ];

    $dataTable=[];
    $last_shipping=null;
    foreach($lines as $line){
      $referenceSupplier=$referencesRepository->getReferenceByProductSupplier($order->getSupplier()->getId(),$line->getProduct()->getId());
          $dataTable[]=[$line->getLinenum(),
                  $referenceSupplier,
                  $line->getProductname(),
                  $line->getProductVariant(),
                  $line->getPurchaseunit(),
                  $line->getQuantity(),
                  $line->getPvp(),
                  $line->getDiscount1(),
                  $line->getDiscount2(),
                  $line->getTotaldiscount(),
                  $line->getShoppingprice(),
                  $line->getTotal(),
                  $line->getDateestimated()->format('d/m/Y')
                ];
      }

      $columnsFooter=[["name"=>"Importe"],
                  ["name"=>"Descuento adicional"],
                  ["name"=>"Importe total"],
                  ["name"=>"Gastos de envío"],
                  ["name"=>"Base imponible"],
                  ["name"=>"% IVA"],
                  ["name"=>"IVA"],
                  ["name"=>"Total", "bold"=>"B"]
      ];

      $dataFooter=[number_format($order->getTaxbase(),2,',','.').json_decode('"\u0080"'),
                  number_format($order->getAdditionaldiscount()),
                  number_format($order->getTaxbase()*(100-$order->getAdditionaldiscount())/100,2,',','.').json_decode('"\u0080"'),
                  number_format($order->getShippingcosts()+$order->getAdditionalcost(),2,',','.').json_decode('"\u0080"'),
                  number_format($order->getTaxbase()*(100-$order->getAdditionaldiscount())/100+$order->getShippingcosts()+$order->getAdditionalcost(),2,',','.').json_decode('"\u0080"'),
                  number_format(21),
                  number_format($order->getTaxes(),2,',','.').json_decode('"\u0080"'),
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
//dump($a);
    return $this->pdf->Output('I', $file==null?$order->getCode().".pdf":$file);
  }
}
