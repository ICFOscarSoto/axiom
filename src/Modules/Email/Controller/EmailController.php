<?php
namespace App\Modules\Email\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Email\Entity\EmailFolders;
use App\Modules\Email\Entity\EmailSubjects;
use App\Modules\Email\Utils\EmailUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use App\Modules\Security\Utils\SecurityUtils;
require_once __DIR__.'/../../../../vendor/pear/mail/Mail.php';
require_once __DIR__.'/../../../../vendor/pear/mail_mime/Mail/mime.php';
use Mail;
use Mail_mime;
use App\Helpers\HelperMail;
class EmailController extends Controller
{
	private $module='Email';
	private $class=EmailsSubjects::class;
	static function cmpTimestamp($a, $b){ return strcmp($a["timestamp"], $b["timestamp"]);}

	/**
	 * @Route("/{_locale}/email/accounts", name="accounts")
	 */
	public function accounts(RouterInterface $router,Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
		$locale = $request->getLocale();
		$this->router = $router;
		$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

		$templateLists[]=$this->formatList($this->getUser());
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Globale/genericlist.html.twig', [
				'controllerName' => 'emailController',
				'interfaceName' => 'Cuentas correo',
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'optionSelected' => 'users',
				'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
				'userData' => $userdata,
				'lists' => $templateLists
				]);
		}
		return new RedirectResponse($this->router->generate('app_login'));

	}


	/**
	 * @Route("/api/email/accounts/{id}/list", name="accountslist")
	 */
	public function accountslist($id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$userrepository=$this->getDoctrine()->getRepository(GlobaleUsers::class);
		$user = $this->getUser();

		//Permisions check ROLE_GLOBAL -> Any ID, ROLE_ADMIN -> only company fields, ROLE_USER -> only own ID
		if($id!=$user->getId()){
			$user_request = $userrepository->find($id);
			//Check if user has Admin role and is from his company or has Global roles
			if( ((array_search('ROLE_ADMIN',$user->getTemplateData()["roles"])!==FALSE && ($user->getCompany()==$user_request->getCompany())) || array_search('ROLE_GLOBAL',$user->getTemplateData()["roles"])!==FALSE) ){
				//Grant access to requested user
				$user = $user_request;
			}
		}

		$manager = $this->getDoctrine()->getManager();
		$repository = $manager->getRepository(EmailAccounts::class);
		$listUtils=new GlobaleListUtils();
		//$return=$listUtils->getRecords($user,$repository,$request,$manager,$this->listFields, EmailAccounts::class,[["type"=>"and", "column"=>"user.company", "value"=>$this->getUser()->getCompany()]]);
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Accounts.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, EmailAccounts::class,[["type"=>"and", "column"=>"user", "value"=>$user]]);
		return new JsonResponse($return);
	}

	public function formatList($user){
		$list=[
			'id' => 'listAccounts',
			'route' => 'accountslist',
			'routeParams' => ["id" => $user->getId()],
			'orderColumn' => 2,
			'orderDirection' => 'ASC',
			'tagColumn' => 3,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Accounts.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/AccountsFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/AccountsTopButtons.json"),true)
		];
		return $list;
	}

	/**
	 * @Route("/{_locale}/admin/email", name="email")
	 */
	public function email(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {

			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			$folderRepository=$this->getDoctrine()->getRepository(EmailFolders::class);
			$emailAccounts=$this->getUser()->getEmailAccounts();
			$alternativeFolder=null;
			$error=false;
			if(empty($emailAccounts)) $error=true;
				else{
					$alternativeFolder=$folderRepository->findBy(["emailAccount"=>$emailAccounts[0]]);
					if(empty($alternativeFolder)) $error=true;
						else $alternativeFolder=$alternativeFolder[0];
			}

			if(!$error){
			//if($request->query->get('folder')!=null || $emailAccounts[0]->getInboxFolder()!=null){
				$folder=($request->query->get('folder')!==null)?$request->query->get('folder'):($emailAccounts[0]->getInboxFolder()!=null?$emailAccounts[0]->getInboxFolder()->getId():$alternativeFolder->getId());
				return $this->render('@Email/email_list.html.twig', [
					'controllerName' => 'EmailController',
					'interfaceName' => 'Correo electrónico',
					'optionSelected' => 'email',
					'menuOptions' =>  $menurepository->formatOptions($userdata),
					'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
					'userData' => $userdata,
					'folder' => $folder
					]);
			}else{
				return $this->render('@Globale/genericerror.html.twig', [
					  'interfaceName' => 'Correo electrónico',
						'userData' => $userdata,
						'optionSelected' => 'email',
						'menuOptions' =>  $menurepository->formatOptions($userdata),
						'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
						"error"=>["symbol"=> "entypo-attention",
											"title" => "Correo no configurado",
											"description"=>"Debe configurar al menos una cuenta de correo para poder acceder a esta sección"
										]
					]);
			}

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/{_locale}/admin/email/{folder}/{id}/view", name="emailView")
	 */
	public function emailView($folder, $id, RouterInterface $router, Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			return $this->render('@Email/email_message.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata),
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
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);

			$error=false;
			if($this->getUser()->getEmailDefaultAccount()!=null){
				$emailAccount=$this->getUser()->getEmailDefaultAccount();
				$folder=$emailAccount->getInboxFolder();
			}else $error=true;

			if(!$error){
				return $this->render('@Email/email_compose.html.twig', [
					'controllerName' => 'EmailController',
					'interfaceName' => 'Correo electrónico',
					'menuOptions' =>  $menurepository->formatOptions($userdata),
					'optionSelected' => 'emailNew',
					'breadcrumb' =>  $menurepository->formatBreadcrumb('emailNew'),
					'userData' => $userdata,
					'id' => 0,
					'mode' => 0,
					'folder' => $folder->getId(),
					'signature' => $emailAccount->getSignature(),
					'token' => uniqid('sign_').time()
					]);
				}else{
					return $this->render('@Globale/genericerror.html.twig', [
						  'interfaceName' => 'Correo electrónico',
							'userData' => $userdata,
							'optionSelected' => 'email',
							'menuOptions' =>  $menurepository->formatOptions($userdata),
							'breadcrumb' =>  $menurepository->formatBreadcrumb($request->get('_route')),
							"error"=>["symbol"=> "entypo-attention",
												"title" => "Correo no configurado",
												"description"=>"Debe configurar al menos una cuenta de correo para poder acceder a esta sección"
											]
						]);
				}

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/emails/{folder}/{id}/reply", name="emailReply")
	 */
	public function emailReply($folder, $id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			return $this->render('@Email/email_compose.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'optionSelected' => 'emailNew',
				'breadcrumb' =>  $menurepository->formatBreadcrumb('emailNew'),
				'userData' => $userdata,
				'id' => $id,
				'folder' => $folder,
				'mode' => 1,
				'token' => uniqid('sign_').time()
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/emails/{folder}/{id}/forward", name="emailForward")
	 */
	public function emailForward($folder, $id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if(!SecurityUtils::checkRoutePermissions($this->module,$request->get('_route'),$this->getUser(), $this->getDoctrine())) return $this->redirect($this->generateUrl('unauthorized'));
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$locale = $request->getLocale();
			$this->router = $router;
			$userdata=$this->getUser()->getTemplateData($this, $this->getDoctrine());
			$menurepository=$this->getDoctrine()->getRepository(GlobaleMenuOptions::class);
			return $this->render('@Email/email_compose.html.twig', [
				'controllerName' => 'EmailController',
				'interfaceName' => 'Correo electrónico',
				'menuOptions' =>  $menurepository->formatOptions($userdata),
				'optionSelected' => 'emailNew',
				'breadcrumb' =>  $menurepository->formatBreadcrumb('emailNew'),
				'userData' => $userdata,
				'id' => $id,
				'folder' => $folder,
				'mode' => 2,
				'token' => uniqid('sign_').time()
				]);

		}else return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	 * @Route("/api/emails/send", name="emailSend")
	 */
	public function emailSend(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$fromId=$request->request->get('from');
			$toString=$request->request->get('to');
			$ccString=$request->request->get('cc');
			$bccString=$request->request->get('bcc');
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
			$attachments = json_decode($request->request->get('files'));
			$text = $request->request->get('text_content');
			$html = $request->request->get('html_content');
			//Generamos el mail para el envio SMTP
			$headers = array(
			              'From'    => $emailAccount->getUsername(),
			              'Subject' => $request->request->get('subject'),
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
        $msg .= ($request->request->get('message_id'))?"References: ".$request->request->get('message_id')."\r\nIn-Reply-To: ".$request->request->get('message_id')."\r\n":"";
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
			$query=$request->query->get('query');
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

			//Si hay alguna busqueda
			$search_results=array();
			if($query!=null){
				$search_results=imap_search($inbox, 'TEXT "'.$query.'"');
				if($search_results===FALSE) $search_results=array();
				$return['recordsFiltered']=count($search_results);
			}
			//Calculamos los datos de paginacion
			$pages=ceil($return['recordsFiltered']/$limit);
			$page=ceil($start/$limit);
			$page_inverse=abs($page-$pages-1);
			$min=($return['recordsFiltered']-($page*$limit))+1; ($min<1)?$min=1:$min=$min;
			$max=(($return['recordsFiltered']-($page*$limit))+$limit); ($max>$return['recordsFiltered'])?$max=$return['recordsFiltered']:$max=$max;
			$range=$min.":".$max;
			if($query!=null){
				$emailSubjects=imap_fetch_overview ($inbox, implode(',',$search_results),0);
			}else{
				$emailSubjects=imap_fetch_overview ($inbox, "$range",0);
			}
						foreach($emailSubjects as $emailSubject){
							$subject=array();
							$subject["id"]						=$emailSubject->uid;
							$subject["subject"]				=isset($emailSubject->subject)?HelperMail::decode_header(imap_utf8($emailSubject->subject)):'';
						  $subject["from"]					=isset($emailSubject->from)?HelperMail::decode_header(imap_utf8($emailSubject->from)):'';
							$subject["to"]						=isset($emailSubject->to)?HelperMail::decode_header(imap_utf8($emailSubject->to)):'';
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
							$subject["urlDelete"]			=$this->generateUrl('emailMove', array('id' => $emailSubject->uid, "origin"=> $emailFolder->getId(), "destination"=>$emailAccount->getTrashFolder()->getId()));
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
					$return[$emailAccount->getId()]["signatureUrl"]=$this->generateUrl('emailGetSignature', array('id'=>$emailAccount->getId()));
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
					@$inbox = imap_open($connectionString,$emailAccount->getUsername(),$emailAccount->getPassword());
					if($inbox!==FALSE)$emailsUnseen=imap_search($inbox, 'UNSEEN'); else	$emailsUnseen=FALSE;
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
			$message["subject"]				=isset($emailSubject->subject)?HelperMail::decode_header(imap_utf8($emailSubject->subject)):'';
			$message["from"]					=isset($emailSubject->from)?HelperMail::decode_header(imap_utf8($emailSubject->from)):'';
			$message["to"]						=isset($emailSubject->to)?HelperMail::decode_header(imap_utf8($emailSubject->to)):'';
			$message["message_id"]		=isset($emailSubject->message_id)?$emailSubject->message_id:'';
			$message["imgFrom"]			  =substr($this->generateUrl('getUserImage', array('id' => 0)),1); //TODO Buscar foto del contacto en la agenda
			$message["content"]		  	=($emailUtils->htmlmsg!=null)?(preg_match('!!u', $emailUtils->htmlmsg)?$emailUtils->htmlmsg:utf8_encode($emailUtils->htmlmsg)):$emailUtils->plainmsg;
			$message["signature"]			=$emailAccount->getSignature();
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
			$message["urlDelete"]			=$this->generateUrl('emailMove', array('id' => $emailSubject->uid, "origin"=> $emailFolder->getId(), "destination"=>$emailAccount->getTrashFolder()->getId()));
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
			$result = imap_mail_move($inbox, $id, $emailFolderDestination->getName(), CP_UID);
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

	/**
	 * @Route("/api/emails/{id}/getSignature", name="emailGetSignature")
	 */
	public function emailGetSignature($id,RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$entityManager = $this->getDoctrine()->getManager();
			$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
			$emailAccount=$emailRepository->find($id);
				if($emailAccount->getUser()->getId()==$this->getUser()->getId()){
					return new JsonResponse(array("signature" => $emailAccount->getSignature()!=null?$emailAccount->getSignature():""));
				}
		}
		return new Response('');
	}

	/**
	* @Route("/api/email/accounts/{id}/getFolders", name="getEmailFolders")
	*/
	public function getEmailFolders($id){
		$emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
		$emailFoldersRepository = $this->getDoctrine()->getRepository(EmailFolders::class);
		$emailAccount=$emailRepository->findOneBy(["id"=> $id, "user" => $this->getUser()->getId()]);
		$folders=$emailFolder=$emailFoldersRepository->findBy(["emailAccount"=> $emailAccount]);
		$inbox=false;
		$newFolders=0;
		$newAccount=count($folders)>0?false:true;
		try {
			$inbox = imap_open('{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}',$emailAccount->getUsername() ,$emailAccount->getPassword(),OP_HALFOPEN);
		} catch (\Symfony\Component\Debug\Exception\ContextErrorException $e) {
			return new JsonResponse(["result"=>-1]);
		}

		  if($inbox==false) return new JsonResponse(["result"=>-1]);
			$list = imap_list($inbox, '{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}', "*");
			if (is_array($list)) {
			    foreach ($list as $val) {
							//Search if folder already exists
							$emailFolder=$emailFoldersRepository->findOneBy([
								"emailAccount"=> $emailAccount,
								"name" => ltrim(imap_utf7_decode($val),'{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}')
							]);
							if($emailFolder==null){
								$newFolders++;
								//Create folder
								$em=$this->getDoctrine()->getManager();
								$folder=new EmailFolders();
				        $folder->setName(ltrim(imap_utf7_decode($val),'{'.$emailAccount->getServer().':'.$emailAccount->getPort().'/imap/'.$emailAccount->getProtocol().'}'));
				        $folder->setEmailAccount($emailAccount);
				        $em->persist($folder);
				        $em->flush();
							}
			    }
			} else {
			    return new JsonResponse(["result"=>-1]);
			}
			imap_close($inbox);
			return new JsonResponse(["result"=>1, "newaccount"=>$newAccount, "newfolders"=>$newFolders]);
	}

	/**
	* @Route("/{_locale}/email/accounts/{id}/disable", name="disableEmailAccount")
	*/
	public function disable($id)
		{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/email/accounts/{id}/enable", name="enableEmailAccount")
	*/
	public function enable($id)
		{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}
	/**
	* @Route("/{_locale}/email/accounts/{id}/delete", name="deleteEmailAccount")
	*/
	public function delete($id){
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$entityUtils=new GlobaleEntityUtils();
		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
		return new JsonResponse(array('result' => $result));
	}

}
