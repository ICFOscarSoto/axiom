<?php

namespace App\Controller\Email;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Globale\MenuOptions;
use App\Entity\Globale\Users;
use App\Entity\Email\EmailAccounts;
use App\Entity\Email\EmailFolders;
use App\Entity\Email\EmailSubjects;
use App\Utils\Email\EmailUtils;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
require_once __DIR__.'/../../../vendor/pear/mail/Mail.php';
require_once __DIR__.'/../../../vendor/pear/mail_mime/Mail/mime.php';
use Mail;
use Mail_mime;
class EmailController extends Controller
{
	private $class=EmailsSubjects::class;
	static function cmpTimestamp($a, $b){ return strcmp($a["timestamp"], $b["timestamp"]);}
	/**
	 * @Route("/{_locale}/admin/email", name="email")
	 */
	public function email(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$emailAccounts=$this->getUser()->getEmailAccounts();
			$folder=($request->query->get('folder')!==null)?$request->query->get('folder'):$emailAccounts[0]->getInboxFolder()->getId();
			return $this->render('email\email.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'optionSelected' => 'email',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'folder' => $folder
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/{_locale}/admin/email/{folder}/{id}/view", name="emailView")
	 */
	public function emailView($folder, $id, RouterInterface $router, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			return $this->render('email\email_message.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'optionSelected' => 'email',
				'breadcrumb' =>  $menurepository->formatBreadcrumb('emailView'),
				'userData' => $userdata,
				'id' => $id,
				'folder' => $folder
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/{_locale}/admin/email/new", name="emailNew")
	 */
	public function emailNew(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			$emailAccount=$this->getUser()->getEmailDefaultAccount();
			$folder=$emailAccount->getInboxFolder();
			return $this->render('email\email_compose.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'optionSelected' => 'emailNew',
				'breadcrumb' =>  $menurepository->formatBreadcrumb('emailNew'),
				'userData' => $userdata,
				'id' => 0,
				'mode' => 0,
				'folder' => $folder->getId()
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/emails/{folder}/{id}/reply", name="emailReply")
	 */
	public function emailReply($folder, $id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			return $this->render('email\email_compose.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'optionSelected' => 'emailNew',
				'breadcrumb' =>  $menurepository->formatBreadcrumb('emailNew'),
				'userData' => $userdata,
				'id' => $id,
				'folder' => $folder,
				'mode' => 1
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/emails/{folder}/{id}/forward", name="emailForward")
	 */
	public function emailForward($folder, $id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData();
			$menurepository=$this->getDoctrine()->getRepository(MenuOptions::class);
			return $this->render('email\email_compose.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata["roles"]),
				'optionSelected' => 'emailNew',
				'breadcrumb' =>  $menurepository->formatBreadcrumb('emailNew'),
				'userData' => $userdata,
				'id' => $id,
				'folder' => $folder,
				'mode' => 2
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/emails/send", name="emailSend")
	 */
	public function emailSend(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$fromId=$request->query->get('from');
			$toString=$request->query->get('to');
			$ccString=$request->query->get('cc');
			$bccString=$request->query->get('bcc');
			$entityManager = $this->getDoctrine()->getManager();
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailUtils = new EmailUtils();
			$emailUtils->container=$this->container;
			//Buscamos la cuenta seleccionada
			$emailAccount=$emailRepository->findOneBy([
				"id"=> $fromId,
				"user" => $this->getUser()->getId()
			]);
			$attachments = json_decode($request->query->get('files'));
			$text = $request->query->get('content');
			$html = $request->query->get('content');

			//Generamos el mail para el envio SMTP
			$headers = array(
			              'From'    => $emailAccount->getUsername(),
			              'Subject' => $request->query->get('subject'),
										'To' => implode(',',$emailUtils->extractEmailsFromString($toString)),
										"Content-Type" => "text/html",
										'charset' => "UTF-8",
			              );
			if($ccString!=null)	$headers['Cc'] = implode(',',$emailUtils->extractEmailsFromString($ccString));
			if($bccString!=null)	$headers['Bcc'] = implode(',',$emailUtils->extractEmailsFromString($bccString));

			$mime = new Mail_mime(array('eol' => "\n"));
			$mime->setTXTBody(utf8_decode($text));
			$mime->setHTMLBody(utf8_decode($html));
			$tempPath=$this->get('kernel')->getRootDir() . '/../public/temp/'.$this->getUser()->getId().'/';
		  foreach ($attachments as $attach) {
				$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
				if($mimeTypeGuesser->isSupported()) $mimetype=$mimeTypeGuesser->guess($tempPath.$attach); else $mimetype='text/plain';
				$mime->addAttachment($tempPath.$attach, $mimetype);
			}
			$body = $mime->get();
			dump($mime->headers());
			$headers = $mime->headers($headers);
			$smtp = Mail::factory('smtp',
   		array ('host' => $emailAccount->getSmtpServer(),
			     'auth' => true,
			     'username' => $emailAccount->getSmtpUsername(),
					 'port'=>$emailAccount->getSmtpPort(),
			     'password' => $emailAccount->getSmtpPassword()));

			$result = $smtp->send(implode(',',$emailUtils->extractEmailsFromString($toString)), $headers, $body);
			if($result){
				//Si se ha enviado correctamente el mail SMTP procedemos a crear el mail IMAP para almacenarlo en la carpeta
				//de enviados
				$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailAccount->getSentFolder()->getName();
				$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
				$mailBox = "{".$emailAccount->getServer().":".$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol()."}".$emailAccount->getSentFolder()->getName();
        $dmy = date("d-M-Y H:i:s");
        $boundary = "------=".md5(uniqid(rand()));
        $msgid = '{axiom_'.time().'_'.$emailAccount->getId().'}';
        $msg = "From: ".$emailAccount->getSmtpUsername()."\r\n";
        $msg .= "To: ".implode(',',$emailUtils->extractEmailsFromString($request->query->get('to')))."\r\n";
        $msg .= "Date: $dmy\r\n";
        $msg .= "message_id: <".uniqid ()."@aplicode.com>\r\n";
        $msg .= ($request->query->get('message_id'))?"References: ".$request->query->get('message_id')."\r\nIn-Reply-To: ".$request->query->get('message_id')."\r\n":"";
        $msg .= "Subject: ".$request->query->get('subject')."\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        $msg .= "\r\n\r\n";
        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/html;\r\n\tcharset=\"UTF-8\"\r\n";
        $msg .= "Content-Transfer-Encoding: 8bit \r\n";
        $msg .= "\r\n\r\n";
        $msg .= $html."\r\n";
        if(!empty($attachments)) {
            $msg .= "\r\n\r\n";
            $msg .= "--$boundary\r\n";
            foreach ($attachments as $filename) {
                $attachment = chunk_split(base64_encode(file_get_contents($tempPath.$filename)));
                $msg .= "Content-Transfer-Encoding: base64\r\n";
                $msg .= "Content-Disposition: attachment; filename=\"$filename\"\r\n";
                $msg .= "\r\n" . $attachment . "\r\n\r\n";
            }
        }
        $msg .= "\r\n\r\n\r\n";
        $msg .= "--$boundary--\r\n\r\n";
        $result2=imap_append($inbox,$mailBox,$msg,"\\Seen");
			}
			if($result && $result2) return new JsonResponse(array("result" => 1));
				else return new JsonResponse(array("result" => -1));
		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	* @Route("/api/emails/list/{folder}", name="emailsFolderList")
	*/
	public function emailsFoldersList($folder, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$limit=$request->query->getInt('length', 15);
			$start=$request->query->getInt('start', 1);
			$return=array();
			$user=$this->getUser();
			$emailFolder=$emailFolderRepository->find([
					'id' => $folder
			]);
			if(!$emailFolder) return new JsonResponse(array("result"=> -1));
			$emailAccount=$emailFolder->getEmailAccount();
			if(!$emailAccount) return new JsonResponse(array("result"=> -1));
			$inbox = imap_open('{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailFolder->getName(),$emailAccount->getUsername(),$emailAccount->getPassword());
			if($inbox===FALSE) return new JsonResponse(array("result"=> -1));
			$status = imap_status ( $inbox , '{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailFolder->getName(), SA_ALL);
			$return['folderName']=$emailFolder->getName();
			$return['limit']=$limit;
			$return['start']=$start;
			$return['unseen']=$status->unseen;
			$return['recordsTotal']=$status->messages;
			$return['recordsFiltered']=$status->messages;
			$return['empty']=($emailFolder->getId()==$emailAccount->getTrashFolder()->getId())?true:false;
			$return['url']=$this->generateUrl('emailsFolderList', array('folder'=>$folder));
			$return["urlEmpty"]	=$this->generateUrl('emptyFolder', array('folder'=>$folder));
			$pages=ceil($status->messages/$limit);
			$page=ceil($start/$limit);
			$page_inverse=abs($page-$pages-1);
			$min=($status->messages-($page*$limit))+1; ($min<1)?$min=1:$min=$min;
			$max=(($status->messages-($page*$limit))+$limit); ($max>$status->messages)?$max=$status->messages:$max=$max;
			$range=$min.":".$max;
			$emailSubjects=imap_fetch_overview ($inbox, "$range",0);
						foreach($emailSubjects as $emailSubject){
							$subject=array();
							$subject["id"]						=$emailSubject->uid;
							$subject["subject"]				=isset($emailSubject->subject)?imap_utf8($emailSubject->subject):'';
						  $subject["from"]					=isset($emailSubject->from)?imap_utf8($emailSubject->from):'';
							$subject["to"]						=isset($emailSubject->to)?imap_utf8($emailSubject->to):'';
							$subject["message_id"]		=isset($emailSubject->message_id)?$emailSubject->message_id:'';
							$subject["size"]					=$emailSubject->size;
							$subject["uid"]						=$emailSubject->uid;
							$subject["msgno"]					=$emailSubject->msgno;
							$subject["recent"]				=$emailSubject->recent;
							$subject["flagged"]				=$emailSubject->flagged;
							$subject["answered"]			=$emailSubject->answered;
							$subject["deleted"]				=$emailSubject->deleted;
							$subject["seen"]					=$emailSubject->seen;
							$subject["draft"]					=$emailSubject->draft;
							$subject["date"]					=new \DateTime(date('Y-m-d H:i:s',$emailSubject->udate));
							$subject["url"]						=$this->generateUrl('emailView', array('folder'=>$emailFolder->getId(), 'id' => $emailSubject->msgno));
							$subject["urlDelete"]			=$this->generateUrl('emailMove', array('id' => $emailSubject->msgno, "origin"=> $emailFolder->getId(), "destination"=>$emailAccount->getTrashFolder()->getId()));
							$subject["urlRead"]				=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Seen', 'value' => 1));
							$subject["urlFlagged"]		=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Flagged', 'value' => 1));
							$subject["urlUnRead"]			=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Seen', 'value' => 0));
							$subject["urlUnFlagged"]	=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Flagged', 'value' => 0));
							$return["messages"][] 		=$subject;
						}
						$return["messages"]=isset($return["messages"])?array_reverse($return["messages"]):array();
			return new JsonResponse($return);
		}
		return new Response();
	}

	/**
	* @Route("/api/emails/folders/list", name="foldersList")
	*/
	public function foldersList(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$return=array();
			$user=$this->getUser();
			foreach($user->getEmailAccounts() as $emailAccount){
				$itemAccount=array();
				foreach($emailAccount->getEmailFolders() as $emailFolder){
					$subject=array();
					$queryUnseen = $emailSubjectRepository->createQueryBuilder('t')
							->select('count(t.id)')
							->andWhere('t.folder = :val_folder')
							->andWhere('t.seen = :val_seen')
							->setParameter('val_folder', $emailFolder->getId())
							->setParameter('val_seen', false);
					$subject["id"]=$emailFolder->getId();
					$subject["name"]=$emailFolder->getName();
					$subject["count"]=count($emailFolder->getEmailSubjects());
					if($request->query->get('folder')!==null)
						$subject["default"]=(strtoupper($emailFolder->getName())==strtoupper($request->query->get('folder'))?true:false);
					else $subject["default"]=(strtoupper($emailFolder->getId()==$emailAccount->getInboxFolder()->getId())?true:false);
					$subject["unseen"]=$queryUnseen->getQuery()->getSingleScalarResult();
					$return[$emailAccount->getId()]["name"]=$emailAccount->getName();
					$return[$emailAccount->getId()]["folders"][]=$subject;
					//$return[$emailAccount->getId()][$emailFolder->getName()]=$emailFolder->getEmailSubjects();
				}
			}
			return new JsonResponse($return);
		}
		return new Response();
	}

	/**
	* @Route("/api/emails/unreadlist", name="emailsUnreadedList")
	*/
	public function emailsUnreadedList(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$limit=$request->query->getInt('length', 10);
			$start=$request->query->getInt('start', 0);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			// Buscamos todas las cuentas del usuario
			$emailAccounts=$emailRepository->findBy([
				"user" => $this->getUser()->getId()
			]);
			$return=array();
			//Comprobamos solo las carpetas Inbox
			foreach($emailAccounts as $emailAccount){
				if($emailAccount->getInboxFolder()){
					//Comprobamos si hay correo sin leer
					$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailAccount->getInboxFolder()->getName();
					$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
					$emailsUnseen=imap_search($inbox, 'UNSEEN');
					if(!$emailsUnseen) continue;
					$emailSubjects=imap_fetch_overview ($inbox, implode(',',$emailsUnseen), 0);
					foreach($emailSubjects as $emailSubject){
						$subject=array();
						$subject["id"]				=$emailSubject->uid;
						$subject["msgno"]			=$emailSubject->msgno;
						$subject["subject"]		=isset($emailSubject->subject)?imap_utf8($emailSubject->subject):'';
						$subject["from"]			=isset($emailSubject->from)?imap_utf8($emailSubject->from):'';
						$date=new \DateTime(date('Y-m-d H:i:s',$emailSubject->udate));
						$subject["timestamp"]	=$date->getTimestamp();
						$subject["url"]				=$this->generateUrl('emailView', array('folder'=>$emailAccount->getInboxFolder()->getId(), 'id' => $emailSubject->msgno));
						$return[] = $subject;
					}
					//Ordenamos el array por getTimestamp
					usort($return, array(__NAMESPACE__."\EmailController", "cmpTimestamp"));
				}
			}
			return new JsonResponse($return);
		}
		return new Response();
	}

	/**
	 * @Route("/api/emails/{folder}/{id}/get", name="emailGet")
	 */
	public function emailGet($folder, $id, RouterInterface $router, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$entityManager = $this->getDoctrine()->getManager();
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$emailFolder=$emailFolderRepository->findOneBy([
				"id" => $folder
				]);
			if(!$emailFolder) return new JsonResponse(array("result"=> -1));
			$emailAccount=$emailRepository->findOneBy([
				"id" => $emailFolder->getEmailAccount()->getId(),
				"user" => $this->getUser()->getId()
			]);
			if(!$emailAccount) return new JsonResponse(array("result"=> -1));
			$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailFolder->getName();
			$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
			if(!$inbox) return new JsonResponse(array("result"=> 0));
			$subject=imap_fetch_overview ($inbox, $id, 0);
			if(!count($subject)) return new JsonResponse(array("result"=> 0));
			$emailSubject=$subject[0];
			$emailUtils = new EmailUtils();
			$emailUtils->container=$this->container;
			$emailUtils->getmsg($inbox,$emailSubject->msgno);

			$message["id"]						=$emailSubject->uid;
			$message["folder"]				=$folder;
			$message["subject"]				=isset($emailSubject->subject)?imap_utf8($emailSubject->subject):'';
			$message["from"]					=isset($emailSubject->from)?imap_utf8($emailSubject->from):'';
			$message["to"]						=isset($emailSubject->to)?imap_utf8($emailSubject->to):'';
			$message["message_id"]		=isset($emailSubject->message_id)?$emailSubject->message_id:'';
			$message["imgFrom"]			  =substr($this->generateUrl('getUserImage', array('id' => 0)),1); //TODO Buscar foto del contacto en la agenda
			$message["content"]		  	=($emailUtils->htmlmsg!=null)?(preg_match('!!u', $emailUtils->htmlmsg)?$emailUtils->htmlmsg:utf8_encode($emailUtils->htmlmsg)):$emailUtils->plainmsg;
			$message["attachments"]		=$emailUtils->attachments;
			$message["size"]					=$emailSubject->size;
			$message["uid"]						=$emailSubject->uid;
			$message["msgno"]					=$emailSubject->msgno;
			$message["recent"]				=$emailSubject->recent;
			$message["flagged"]				=$emailSubject->flagged;
			$message["answered"]			=$emailSubject->answered;
			$message["deleted"]				=$emailSubject->deleted;
			$message["seen"]					=$emailSubject->seen;
			$message["draft"]					=$emailSubject->draft;
			$message["date"]					=new \DateTime(date('Y-m-d H:i:s',$emailSubject->udate));
			$message["timestamp"]			=$message["date"]->getTimestamp();
			$message["url"]						=$this->generateUrl('emailView', array('folder'=>$emailFolder->getId(), 'id' => $emailSubject->msgno));
			$message["urlDelete"]			=$this->generateUrl('emailMove', array('id' => $emailSubject->msgno, "origin"=> $emailFolder->getId(), "destination"=>$emailAccount->getTrashFolder()->getId()));
			$message["urlRead"]				=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Seen', 'value' => 1));
			$message["urlFlagged"]		=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Flagged', 'value' => 1));
			$message["urlUnRead"]			=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Seen', 'value' => 0));
			$message["urlUnFlagged"]	=$this->generateUrl('emailSetFlag', array('id' => $emailSubject->uid, 'flag' => 'Flagged', 'value' => 0));

			return new JsonResponse($message);
		}
		return new Response('');
		//return new JsonResponse();
	}

	/**
	 * @Route("/api/emails/{id}/setflag/{flag}/{value}", name="emailSetFlag")
	 */
	public function emailSetFlag($id, $flag, $value, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$entityManager = $this->getDoctrine()->getManager();
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$subject=$emailSubjectRepository->find($id);
			if($subject){
					$emailAccount=$subject->getFolder()->getEmailAccount();
					if($emailAccount->getUser()->getId()==$this->getUser()->getId()){
						$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$subject->getFolder()->getName();
						$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
						if($value) $status = imap_setflag_full($inbox, $subject->getMsgno(), "\\".$flag);
							else $status = imap_clearflag_full($inbox, $subject->getMsgno(), "\\".$flag);
						if($status){
							$subject->{"set".$flag}($value);
							$entityManager->persist($subject);
							$entityManager->flush();
						return new JsonResponse(array("result" => 1));
						}	else return new JsonResponse(array("result" => 0));
					}
			}
		}
		return new JsonResponse(array("result" => -1));
	}

	/**
	 * @Route("/api/emails/{id}/move/{origin}/{destination}", name="emailMove")
	 */
	public function emailMove($id, $origin, $destination, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$entityManager = $this->getDoctrine()->getManager();
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$emailFolderOrigin=$emailFolderRepository->find($origin);
			$emailFolderDestination=$emailFolderRepository->find($destination);
			//Comprobamos que el usuario actual sea el propietario de la carpeta
			$emailAccount=$emailRepository->findOneBy([
				"id" => $emailFolderOrigin->getEmailAccount()->getId(),
				"user" => $this->getUser()->getId()
			]);
			if(!$emailAccount) return new JsonResponse(array("result" => -1));
			$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailFolderOrigin->getName();
			$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
			$result = imap_mail_move($inbox, $id, $emailFolderDestination->getName());
			imap_expunge($inbox);
			return new JsonResponse(array("result" => $result?1:0));
		}return new JsonResponse(array("result" => -1));
	}

	/**
	 * @Route("/api/emails/empty/{folder}", name="emptyFolder")
	 */
	public function emptyFolder($folder, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$entityManager = $this->getDoctrine()->getManager();
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$emailFolder=$emailFolderRepository->find($folder);
			//Comprobamos que el usuario actual sea el propietario de la carpeta
			$emailAccount=$emailRepository->findOneBy([
				"id" => $emailFolder->getEmailAccount()->getId(),
				"user" => $this->getUser()->getId()
			]);
			if(!$emailAccount) return new JsonResponse(array("result" => -1));
			$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$emailFolder->getName();
			$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
			$emails = imap_search($inbox,'ALL');
			$result=true;
			foreach($emails as $email){
				 $result&=imap_delete($inbox,$email);
			}
			imap_expunge($inbox);
			return new JsonResponse(array("result" => $result));
		}return new JsonResponse(array("result" => -1));
	}

	/**
	 * @Route("/api/emails/attachment/{id}/get", name="emailGetAttachment")
	 */
	public function emailGetAttachment($id,RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$emailSubjectRepository = $this->getDoctrine()->getRepository(EmailSubjects::class);
			$subject=$emailSubjectRepository->find($id);
			$filename = $request->query->get('file');
			if($subject && $filename!=null){
				$emailAccount=$subject->getFolder()->getEmailAccount();
				if($emailAccount->getUser()->getId()==$this->getUser()->getId()){
					$encoding=$request->query->getInt('encoding', 3);
					$part=$request->query->getInt('part', 0);
					$connectionString='{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'.$subject->getFolder()->getName();
					$inbox = imap_open($connectionString,$emailAccount->getUsername() ,$emailAccount->getPassword());
					$emailUtils = new EmailUtils();
					$data=$emailUtils->getAtachment($inbox,$subject->getMsgno(),$encoding,$part);
					$fileinfo=finfo_open(FILEINFO_MIME_TYPE);
					$contentType=finfo_buffer($fileinfo, $data);
					$response = new Response();
					// Set headers
					$response->headers->set('Cache-Control', 'private');
					$response->headers->set('Content-type', $contentType );
					$response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '";');
					$response->headers->set('Content-length',  strlen($data));
					// Send headers before outputting anything
					$response->sendHeaders();
					$response->setContent( $data );
					return $response;
				}
			}
		}
		return new Response('');
	}



}
