<?php
namespace App\Modules\HR\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Entity\GlobaleCompanies;
use \App\Helpers\HelperFiles;
use App\Modules\Globale\Helpers\PdfParser\Parser;
use App\Modules\Globale\Helpers\PdfParser\Config;
use App\Service\ContainerParametersHelper;

class PayrollProccess extends ContainerAwareCommand
{

  private $doctrine;
  private $entityManager;
  private $configpaths;

  public function __construct(array $configpaths){
      $this->configpaths=$configpaths;
       parent::__construct();
   }

  protected function configure(){
        $this
            ->setName('payroll:send')
            ->setDescription('Proccess payroll file')
            ->addArgument('month', InputArgument::REQUIRED, '¿Mes de la remesa de nóminas?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $workersRepository=$this->doctrine->getRepository(HRWorkers::class);
    $usersRepository=$this->doctrine->getRepository(GlobaleUsers::class);
    $month = $input->getArgument('month');
    //Buscamos el usuario Olivia
    $user=$usersRepository->findOneBy(['email'=>'olivia.sanchez@ferreteriacampollano.com']);
    if(!$user) return 0;

    //Directorios de trabajo
    $ocrDir=$this->configpaths["payroll_preOCR"];
    $tempDir=$this->configpaths["payroll_temp"];

    $sourceFile=$ocrDir.'nominas.pdf';

    //Comprobamos que existan los directorios de trabajo
    if(!file_exists($ocrDir) || !is_dir($ocrDir))
      die();
    if(!file_exists($tempDir) || !is_dir($tempDir))
      die();
    if(!file_exists($sourceFile))
      die();

    $config = new Config();
    $config->setHorizontalOffset("\t");
    $parser = new Parser([], $config);

    $date=new \DateTime();
    //Obtener el numero de páginas del pdf
    $result=shell_exec("qpdf --show-npages  \"".$sourceFile."\"");
    //Partir el fichero en archivos de una unica hoja
    $result=shell_exec("gs -o \"".$tempDir.uniqid("file")."_%04d.pdf\" -sDEVICE=pdfwrite \"".$sourceFile."\"");
    $dir = new \DirectoryIterator($tempDir);
    foreach ($dir as $fileinfo) {
      if(!$fileinfo->isDot()){
        //Añadir formato corporativo
        $result=shell_exec("pdftk ".$tempDir.$fileinfo->getFilename()." stamp /home/operador/nominas/plantilla_nominas.pdf output ".$tempDir.basename($fileinfo->getFilename(), '.pdf')."_format.pdf");
        unlink($tempDir.$fileinfo->getFilename());
        //Firmar documentos
        @$result=shell_exec("AutoFirma sign -i ".$tempDir.basename($fileinfo->getFilename(), '.pdf')."_format.pdf -o ".$tempDir.$fileinfo->getFilename()." -store pkcs12:/home/operador/nominas/representacion_olivia.p12 -alias 47057442v_olivia_sanchez__r:_b02290443_ -password Edin1Icf 2>&1");
        unlink($tempDir.basename($fileinfo->getFilename(), '.pdf')."_format.pdf");
        //Buscar numero DNI
        $pdf = $parser->parseFile($tempDir.$fileinfo->getFilename());
        preg_match('/([0-9]{8})([A-Z]{1})/', $pdf->getText(), $matches, PREG_OFFSET_CAPTURE);
        if(count($matches)>0) $nif=substr($pdf->getText(), $matches[0][1], 9); else $nif=null;
        $worker=$workersRepository->findOneBy(['idcard'=>$nif, 'deleted'=>0]);
        if(!$worker) continue;
        if($worker->getEmail()==null || $worker->getEmail()=='') continue;
        $output->writeln('DNI: '.$nif.' enviar mail a '.$worker->getEmail());
        //Mover archivo a temporales de Email
        rename ($tempDir.$fileinfo->getFilename(), $tempDir.'../'.$user->getId().'/Email/Nomina_'.$month.'_'.$nif.'.pdf');
        //Enviar correo electronico
        $msg="Prueba de correo";
        $postdata = http_build_query([
                'from' => $user->getId(),
                'to' => $worker->getEmail(),
                'cc' => '',
                'bcc' =>'',
                'subject' =>'Nómina de '.$month.' Ferretería Campollano',
                'files' => '{}',
                'text_content' =>'',
                'html_content' => $msg
              ]);
        $opts = ["http" => [
                    "method" => "POST",
                    "header" => "Content-Type: application/x-www-form-urlencoded\r\nX-AUTH-TOKEN: ".$user->getApiToken()."\r\nX-AUTH-DOMAIN: ferreteriacampollano.com",
                    "content" => $postdata
                  ]
                ];
        $context = stream_context_create($opts);
        $output->writeln("  - Enviando correo...");
        $file = file_get_contents('https://axiom.ferreteriacampollano.com/api/emails/send', false, $context);

      }
    }
    //Borrar archivo original
    //unlink($sourceFile);


  }


}
?>
