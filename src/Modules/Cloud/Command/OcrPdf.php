<?php
namespace App\Modules\Cloud\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Cloud\Entity\CloudFiles;
use App\Modules\Globale\Entity\GlobaleCompanies;
use App\Modules\Globale\Entity\GlobaleHistories;
use App\Modules\Globale\Entity\GlobaleUserSessions;
use App\Modules\Navision\Entity\NavisionSync;

class OcrPdf extends ContainerAwareCommand
{
  private $doctrine;
  private $entityManager;
  protected function configure(){
        $this
            ->setName('cloud:ocrpdf')
            ->setDescription('OCR PDFs scanned files')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();

    //Execute different tasks
    $this->OCRFiles($output);
  }

  function OCRFiles($output){
    //------   Create Lock Mutex    ------
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $fp = fopen('C:\xampp\htdocs\axiom\tmp\axiom-cloud-ocrfiles.lock', 'c');
    } else {
        $fp = fopen('/tmp/axiom-cloud-ocrfiles.lock', 'c');
    }
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
      $output->writeln('* Fallo al iniciar el ocr de archivos: El proceso ya esta en ejecución.');
      exit;
    }
    //------   Critical Section START   ------
    $navisionSyncRepository=$this->doctrine->getRepository(NavisionSync::class);
    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"cloudOcrFiles"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setMaxtimestamp(0);
    }
    $dateupd=new \DateTime();
    sleep(1);
    $datetime=new \DateTime();
    $cloudRepository = $this->doctrine->getRepository(CloudFiles::class);
    $CompaniesRepository = $this->doctrine->getRepository(GlobaleCompanies::class);

    //Disable SQL logger
    $this->doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);
    $files=$cloudRepository->findRecentPdfs($navisionSync->getLastsync());
    $output->writeln([
            'Searching PDF Files for OCR',
            '===========================',
            '',
    ]);
    foreach($files as $file){
      $fileobj=$cloudRepository->find($file['id']);
      if($fileobj){
        $output->writeln([' - Processing: '.$file['name']]);
        exec('ocrmypdf '.$this->getContainer()->get('kernel')->getRootDir() . '/../cloud/'.$file['company_id'].'/'.$file['path'].'/'.$file['idclass'].'/'.$file['hashname'].' '.$this->getContainer()->get('kernel')->getRootDir() . '/../cloud/'.$file['company_id'].'/'.$file['path'].'/'.$file['idclass'].'/'.$file['hashname']);
        $fileobj->setDateupd($dateupd);
        $this->doctrine->getManager()->persist($fileobj);
        $this->doctrine->getManager()->flush();
      }
    }


    $navisionSync=$navisionSyncRepository->findOneBy(["entity"=>"cloudOcrFiles"]);
    if ($navisionSync==null) {
      $navisionSync=new NavisionSync();
      $navisionSync->setEntity("cloudOcrFiles");
    }
    $navisionSync->setLastsync($datetime);
    $navisionSync->setMaxtimestamp(0);
    $this->doctrine->getManager()->persist($navisionSync);
    $this->doctrine->getManager()->flush();
    //------   Critical Section END   ------
    //------   Remove Lock Mutex    ------
    fclose($fp);

  }
}
?>
