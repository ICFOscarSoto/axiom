<?php
namespace App\Modules\Email\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Email\Entity\EmailFolders;
use App\Modules\Email\Entity\EmailSubjects;
use App\Helpers\HelperMail;

class EmailGetSubjects extends ContainerAwareCommand
{
  protected function configure(){
        $this->setName('email:getsubjects')
             ->setDescription('Obtener asuntos de ultimos correos recibidos');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    $emailAccountsRepository = $doctrine->getRepository(EmailAccounts::class);
    $emailFoldersRepository = $doctrine->getRepository(EmailFolders::class);
    $emailSubjectsRepository = $doctrine->getRepository(EmailSubjects::class);
    $output->writeln([
            'EMAIL get subjects',
            '==================',
            '',
    ]);
    $emailAccounts=$emailAccountsRepository->findAll();

    foreach($emailAccounts as $emailAccount){
      //get folders
      $emailFolders=$emailFoldersRepository->findBy(["emailAccount"=>$emailAccount]);
      $countUnseen=0;
      $emailFolders=$emailFoldersRepository->findBy(["emailAccount"=>$emailAccount]);
      foreach($emailFolders as $folder){
        $connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'/novalidate-cert}'.$folder->getName();
        @$inbox = imap_open($connectionString,$emailAccount->getUsername(),$emailAccount->getPassword());
        //if($inbox!==FALSE)$emailsUnseen=imap_search($inbox, 'UNSEEN'); else	$emailsUnseen=FALSE;
        if($inbox!==FALSE)$emailsUnseen=imap_status ( $inbox , $connectionString , SA_UNSEEN ) ; else	$emailsUnseen=FALSE;
          $count=0;
          if(!$emailsUnseen) $count=0; else $count=$emailsUnseen->unseen;//$count=count($emailsUnseen);
          $countUnseen+=$count;
          $folder->setUnseen($count);
          $entityManager->persist($folder);
          $entityManager->flush();
        //Get Ibox subjects





        if($emailAccount->getInboxFolder()->getId()==$folder->getId()){
          $olderMsgs=$emailSubjectsRepository->getUids($emailAccount->getInboxFolder()->getId());
          //$olderMsgs = array_reduce($olderMsgs, 'array_merge', array());
          $emailSubjectsRepository->deleteByFolder($emailAccount->getInboxFolder()->getId());
          $connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'/novalidate-cert}'.$emailAccount->getInboxFolder()->getName();
          @$inbox = imap_open($connectionString,$emailAccount->getUsername(),$emailAccount->getPassword());
            if($inbox!==FALSE)$emailsUnseen=imap_search($inbox, 'UNSEEN'); else	$emailsUnseen=FALSE;
            if(!$emailsUnseen) continue;
            array_reverse($emailsUnseen);
            $emailSubjects=imap_fetch_overview ($inbox, implode(',',$emailsUnseen), 0);
            foreach($emailSubjects as $emailSubject){
              $subject = new EmailSubjects();
              $subject->setFolder($emailAccount->getInboxFolder());
              $subject->setUid($emailSubject->uid);
              $subject->setRecent(!in_array($emailSubject->uid, $olderMsgs));
              $subject->setMessageId(isset($emailSubject->message_id)?$emailSubject->message_id:'');
              $subject->setMsgno($emailSubject->msgno);
              $subject->setSubject(isset($emailSubject->subject)?HelperMail::decode_header(imap_utf8($emailSubject->subject)):'');
              $subject->setFromEmail(isset($emailSubject->from)?HelperMail::decode_header(imap_utf8($emailSubject->from)):'');
              $subject->setDate(new \DateTime(date('Y-m-d H:i:s',$emailSubject->udate)));
              $entityManager->persist($subject);
              $entityManager->flush();
            }
          }
      }
      $emailAccount->setUnseen($countUnseen);
      $entityManager->persist($emailAccount);
      $entityManager->flush();
    }
  }
}
?>
