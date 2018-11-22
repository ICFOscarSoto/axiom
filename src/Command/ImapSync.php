<?php
namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use App\Entity\Email\EmailAccounts;
use App\Entity\Email\EmailFolders;
use App\Entity\Email\EmailSubjects;
use App\Utils\Email\EmailUtils;

class ImapSync extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('imap:sync')
            ->setDescription('Sincronizar cuentas imap')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $doctrine = $this->getContainer()->get('doctrine');
    $entityManager = $doctrine->getManager();
    $emailRepository = $doctrine->getRepository(EmailAccounts::class);
    $emailFolderRepository = $doctrine->getRepository(EmailFolders::class);
    $emailSubjects = $doctrine->getRepository(EmailSubjects::class);
    $emailAccounts=$emailRepository->findAll();

    //Obtenemos la lista de carpetas de cada cuenta del servidor
    foreach($emailAccounts as $emailAccount){
        $connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}';
        $inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
        $folders = imap_list($inbox,$connectionString,'*');
        foreach($folders as $folder){
          $folderName=str_replace($connectionString, '', $folder);
          $emailAccountFolders=$emailFolderRepository->findOneBy([
              'emailAccount' => $emailAccount->getId(),
              'name' => $folderName
          ]);
          if($emailAccountFolders===null){
              $emailFolder=new EmailFolders();
              $emailFolder->setName($folderName);
              $emailFolder->setEmailAccount($emailAccount);
              $entityManager->persist($emailFolder);
              $entityManager->flush();
          }
        }
      }

    //Obtenemos la lista de correos de cada cuenta y cada carpeta del servidor
    $emailAccounts=$emailRepository->findAll();
		foreach($emailAccounts as $emailAccount){
      //Si no esta totalmente configurada la cuenta pasamos a la siguiente
      if($emailAccount->getInboxFolder()===null || $emailAccount->getSentFolder()===null || $emailAccount->getTrashFolder()===null) continue;
			$folders=$emailAccount->getEmailFolders();
		  foreach($folders as $folder){
          $inbox = imap_open('{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$folder->getName(),$emailAccount->getUsername(),$emailAccount->getPassword());
          if($inbox===FALSE) continue;
          $nums=imap_num_msg($inbox);
					for ($i=1;$i<=$nums;$i++){
						$subject = imap_fetch_overview($inbox, $i, 0);
            $emailUtils = new EmailUtils();

            dump($subject);

            //Buscamos el mensaje por Uid o por message_id
            if(isset($subject[0]->message_id))
              $emailSubject_message_id=$emailSubjects->findByAccountAndMessageId($emailAccount->getId(), $subject[0]->message_id);
              else $emailSubject_message_id=null;
            $emailSubject_uid=$emailSubjects->findByAccountAndUid($emailAccount->getId(), $subject[0]->uid);
            $emailSubject=$emailSubject_message_id==null?$emailSubject_uid:$emailSubject_message_id;
            //Si no hemos encontrado el mensaje lo creamos
						if($emailSubject===null){
							mb_internal_encoding('UTF-8');
							$emailSubject=new EmailSubjects();
							$emailSubject->setSubject(str_replace("_"," ", mb_decode_mimeheader(isset($subject[0]->subject) ? $subject[0]->subject:'')));
							$emailSubject->setFromEmail(str_replace("_"," ", mb_decode_mimeheader(isset($subject[0]->from) ? $subject[0]->from:'')));
							$emailSubject->setToEmail(str_replace("_"," ", mb_decode_mimeheader(isset($subject[0]->to) ? $subject[0]->to:'')));
							$emailSubject->setMessageId(isset($subject[0]->message_id)?$subject[0]->message_id:'');
							$emailSubject->setSize($subject[0]->size);
							$emailSubject->setUid($subject[0]->uid);
							$emailSubject->setMsgno($subject[0]->msgno);
							$emailSubject->setRecent($subject[0]->recent == 0 ? false : true);
							$emailSubject->setFlagged($subject[0]->flagged == 0 ? false : true);
							$emailSubject->setAnswered($subject[0]->answered == 0 ? false : true);
							$emailSubject->setDeleted($subject[0]->deleted == 0 ? false : true);
							$emailSubject->setSeen($subject[0]->seen == 0 ? false : true);
							$emailSubject->setDraft($subject[0]->draft == 0 ? false : true);
							$emailSubject->setDate( new \DateTime(date('Y-m-d H:i:s',$subject[0]->udate)));
							$emailSubject->setFolder($folder);
              $emailSubject->setAttachments($emailUtils->countAttachments($inbox, $subject[0]->msgno));
							$entityManager->persist($emailSubject);
		        	$entityManager->flush();
						}
					}
          imap_close($inbox);
			}
		}

  }
}


?>
