<?php
namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleComments;
use App\Modules\Globale\Entity\GlobaleCommentsEmails;
use App\Modules\Globale\Entity\GlobaleCommentsCalls;
use App\Modules\HR\Entity\HRWorkers;
use App\Modules\Email\Entity\EmailAccounts;
use App\Modules\Email\Entity\EmailFolders;
use App\Modules\Email\Utils\EmailUtils;
use App\Modules\Globale\Helpers\Html2Text\Html2Text;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class GlobaleCommentsController extends Controller
{
  private $module='Globale';
  private $class=GlobaleComments::class;

  public function orderHistoryElements($a, $b) {
      if($a['date']>=$b['date'])
        return -1;
      else return 1;
  }

  /**
   * @Route("/api/globale/savecomment/{id}", name="saveComment", defaults={"id"=0})
   */
  public function saveComment($id,RouterInterface $router,Request $request){
	      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $commentsRepository	= $this->getDoctrine()->getRepository(GlobaleComments::class);
        $comment = null;

        $entity=$request->request->get('entity');
        $entity_id=$request->request->get('entity_id');
        $comment_text=$request->request->get('comment');

        //Comprobar que existe la entidad y el objeto
        if(!class_exists('\\'.$entity)) return new JsonResponse(["result"=>-1]);
        $repository	= $this->getDoctrine()->getRepository('\\'.$entity);
        if(!$repository) return new JsonResponse(["result"=>-1]);
        $obj = $repository->find($entity_id);
        if(!$obj) return new JsonResponse(["result"=>-1]);

        if($id!=0){
          $comment=$commentsRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
          //Comprobamos que el usuario sea el autor del comentario
          if(!$comment || $comment->getUser() != $this->getUser())
            return new JsonResponse(["result"=>-1]);
        }
        if(!$comment){
          $comment= new GlobaleComments();
          $comment->setCompany($this->getUser()->getCompany());
          $comment->setUser($this->getUser());
          $comment->setEntity($entity);
          $comment->setEntityId($entity_id);
          $comment->setActive(1);
          $comment->setDeleted(0);
          $comment->setDateadd(new \DateTime());
        }
        $comment->setComment($comment_text);
        $comment->setDateupd(new \DateTime());
        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse(["result"=>1, "id"=>$comment->getId()]);
  }

  /**
   * @Route("/api/globale/removecomment/{id}", name="removeComment", defaults={"id"=0})
   */
  public function removeComment($id,RouterInterface $router,Request $request){
	      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $commentsRepository	= $this->getDoctrine()->getRepository(GlobaleComments::class);
        $comment = null;
        if($id!=0){
          $comment=$commentsRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
          //Comprobamos que el usuario sea el autor del comentario
          if(!$comment || $comment->getUser() != $this->getUser()){
            return new JsonResponse(["result"=>-1]);
          }else{
            $comment->setDeleted(1);
            $comment->setDateupd(new \DateTime());
            $this->getDoctrine()->getManager()->persist($comment);
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(["result"=>1, "id"=>$comment->getId()]);
          }
        }
        return new JsonResponse(["result"=>-2]);
  }

  /**
   * @Route("/api/emailcomment/{folder}/{id}/save", name="saveEmailComment")
   */
  public function saveEmail($folder, $id, RouterInterface $router, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      $emailRepository = $this->getDoctrine()->getRepository(EmailAccounts::class);
      $emailFolderRepository = $this->getDoctrine()->getRepository(EmailFolders::class);

      $entity=$request->request->get('entity');
      $entity_id=$request->request->get('entity_id');

      //Comprobar que existe la entidad y el objeto
      if(!class_exists('\\'.$entity))
        return new JsonResponse(["result"=>-1]);
      $repository	= $this->getDoctrine()->getRepository('\\'.$entity);
      if(!$repository) return new JsonResponse(["result"=>-1]);
      $obj = $repository->find($entity_id);
      if(!$obj) return new JsonResponse(["result"=>-1]);

      $emailFolder=$emailFolderRepository->findOneBy(["id" => $folder]);
      if(!$emailFolder) return new JsonResponse(array("result"=> -1));
      //Comprobamos que la cuenta y la carpeta pertenezcan al usuario logueado
      $emailAccount=$emailRepository->findOneBy(["id" => $emailFolder->getEmailAccount()->getId(), "user" => $this->getUser()->getId()]);
      if(!$emailAccount) return new JsonResponse(array("result"=> -1));
      $emailUtils = new EmailUtils();
      $message=$emailUtils->readEmail($emailAccount, $emailFolder, $this->container, $id, $router);
      if(!$message) return new JsonResponse(array("result"=> -2));
      $message["folder"]=$folder;
      $mail = new GlobaleCommentsEmails();
      $mail->setCompany($this->getUser()->getCompany());
      $mail->setUser($this->getUser());
      $mail->setEntity($entity);
      $mail->setEntityId($entity_id);
      $mail->setFromaddress($message["from"]);
      $mail->setToaddress($message["to"]);
      $mail->setSubject($message["subject"]);
      $mail->setContent($message["content"]);
      $mail->setActive(1);
      $mail->setDeleted(0);
      $mail->setDateadd(new \DateTime());
      $mail->setDateupd(new \DateTime());
      $this->getDoctrine()->getManager()->persist($mail);
      $this->getDoctrine()->getManager()->flush();
      $plainContent = preg_replace('/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '',$mail->getContent());
      @$plainContent=Html2Text::convert($plainContent);

      return new JsonResponse(["result"=>1, "id"=>$mail->getId(), "shortcontent"=>substr($plainContent,0, 250)]);
    }
     return new JsonResponse(array("result"=> -1));
    //return new JsonResponse();
  }

  /**
   * @Route("/api/emailcomment/read/{id}", name="readEmailComment")
   */
  public function readEmailComment($id, RouterInterface $router, Request $request){
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
    if(!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) return new JsonResponse(array("result"=> -1));
    $commentEmailRepository = $this->getDoctrine()->getRepository(GlobaleCommentsEmails::class);
    $entity=$request->request->get('entity');
    $entity_id=$request->request->get('entity_id');
    //Comprobar que existe la entidad y el objeto
    if(!class_exists('\\'.$entity))
      return new JsonResponse(["result"=>-1]);
    $repository	= $this->getDoctrine()->getRepository('\\'.$entity);
    if(!$repository) return new JsonResponse(["result"=>-1]);
    $obj = $repository->find($entity_id);
    if(!$obj) return new JsonResponse(["result"=>-1]);
    $email=$commentEmailRepository->findOneBy(["id" => $id, "entity_id" => $entity_id, "entity"=> $entity, "deleted"=>0, "active"=>1]);
    if(!$email) return new JsonResponse(["result"=> -1]);
    return new JsonResponse(["result"=>1, "content"=>$email->getContent()]);
  }

  /**
   * @Route("/api/globale/removeemail/{id}", name="removeEmailComment", defaults={"id"=0})
   */
  public function removeEmailComment($id,RouterInterface $router,Request $request){
	      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $commentEmailRepository	= $this->getDoctrine()->getRepository(GlobaleCommentsEmails::class);
        $email = null;
        if($id!=0){
          $email=$commentEmailRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
          //Comprobamos que el usuario sea el autor del comentario
          if(!$email || $email->getUser() != $this->getUser()){
            return new JsonResponse(["result"=>-1]);
          }else{
            $email->setDeleted(1);
            $email->setDateupd(new \DateTime());
            $this->getDoctrine()->getManager()->persist($email);
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse(["result"=>1, "id"=>$email->getId()]);
          }
        }
        return new JsonResponse(["result"=>-2]);
  }

  /**
   * @Route("/api/globale/getavailablecallrecords", name="getCallRecordsComment")
   */
  public function getavailablecallrecords(RouterInterface $router,Request $request){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $workersRepository	= $this->getDoctrine()->getRepository(HRWorkers::class);

        $date = new \DateTime();
        //$tempDir='Z:\Grabaciones\\';

        $tempDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'ERPCalls'.DIRECTORY_SEPARATOR;
        $tempDir=$tempDir.$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;

        if(!file_exists($tempDir)) return new JsonResponse(["result"=>-1 , "dir"=>$tempDir]);

        //Obtenemos el trabajador asociado al usuario

        $worker=$workersRepository->findOneBy(["user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
        if(!$worker) return new JsonResponse(["result"=>-2]);
        //Si el usuario no tiene extension configurada abortamos
        if($worker->getExtension()==null) return new JsonResponse(["result"=>-3]);
        $extension=$worker->getExtension();
        //Obtenemos los ficheros del directorio temporal
        $dir = new \DirectoryIterator($tempDir);
        $availableCalls=[];
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
              if($fileinfo->getSize()<=1024) continue;
              $datetime=new \DateTime();
              $datetime->setTimestamp($fileinfo->getMTime());
              $tempCall=explode('-',$fileinfo->getFilename());
              $call=[];
              switch($tempCall[0]){
                case "external":
                    if($tempCall[1]==$extension){
                      $call["type"]="Entrante";
                      $call["extension"]=$extension;
                      $call["remote"]=$tempCall[2];
                      $call["filename"]=$fileinfo->getFilename();
                      $call["date"]=$fileinfo->getMTime();
                      $call["dateadd"]=$datetime->format("d/m/Y H:i:s");
                      $availableCalls[]=$call;
                    }
                break;
                case "out":
                    if($tempCall[2]==$extension){
                      $call["type"]="Saliente";
                      $call["extension"]=$extension;
                      $call["remote"]=$tempCall[1];
                      $call["filename"]=$fileinfo->getFilename();
                      $call["date"]=$fileinfo->getMTime();
                      $call["dateadd"]=$datetime->format("d/m/Y H:i:s");
                      $availableCalls[]=$call;
                    }
                break;
                case "internal":
                    if($tempCall[1]==$extension || $tempCall[2]==$extension){
                      $call["type"]="Interna";
                      $call["extension"]=$extension;
                      $call["remote"]=$tempCall[1]==$worker->getExtension()?$tempCall[2]:$tempCall[1];
                      $call["filename"]=$fileinfo->getFilename();
                      $call["date"]=$fileinfo->getMTime();
                      $call["dateadd"]=$datetime->format("d/m/Y H:i:s");
                      $availableCalls[]=$call;
                    }
                break;
                default:
                  continue 2;
                break;
              }
            }
        }
        usort($availableCalls, array('\App\Modules\Globale\Helpers\HelperHistory','orderHistoryElements'));
        $availableCalls=array_slice($availableCalls, 0, 15);
        return new JsonResponse(["result"=>1, "data"=>$availableCalls]);
  }



  /**
   * @Route("/api/globale/previewcallrecord/{type}/{filename}", name="previewCallRecord")
   */
  public function previewCallRecord($type,$filename,RouterInterface $router,Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      $tempDir='';
      if($type=='temp'){
        //$tempDir='Z:\Grabaciones\\';
        $tempDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'ERPCalls'.DIRECTORY_SEPARATOR;
        $date = new \DateTime();
        $tempDir=$tempDir.$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;
      }else{
        //$tempDir='A:\var\www\axiom.ferreteriacampollano.com\cloud\2\ERPCalls\\';
        $tempDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'ERPCalls'.DIRECTORY_SEPARATOR;
      }
      $response = new BinaryFileResponse($tempDir.$filename);
      $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
      $response->headers->set('Content-Type', $mimeTypeGuesser->guess($tempDir.$filename));
      $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$filename);
      return $response;
    }


    /**
     * @Route("/api/globale/callcomment/save", name="saveCallComment")
     */
    public function saveCallComment(RouterInterface $router, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        $workersRepository	= $this->getDoctrine()->getRepository(HRWorkers::class);
        $commentCallRepository = $this->getDoctrine()->getRepository(GlobaleCommentsCalls::class);

        $date = new \DateTime();
        //$tempDir='Z:\Grabaciones\\';
        //$destDir='A:\var\www\axiom.ferreteriacampollano.com\cloud\2\ERPCalls\\';
        $tempDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR.'ERPCalls'.DIRECTORY_SEPARATOR;
        $destDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'ERPCalls'.DIRECTORY_SEPARATOR;
        $tempDir=$tempDir.$date->format('Y').DIRECTORY_SEPARATOR.$date->format('m').DIRECTORY_SEPARATOR.$date->format('d').DIRECTORY_SEPARATOR;

        if(!file_exists($tempDir)) return new JsonResponse(["result"=>-1]);

        $entity=$request->request->get('entity');
        $entity_id=$request->request->get('entity_id');
        $file=$request->request->get('file');
        //Comprobamos que existe el fichero
        if(!file_exists($tempDir.$file) || !is_file($tempDir.$file)) return new JsonResponse(["result"=>-2]);
        //Comprobamos que sea un fichero valido > 1Kb
        if(filesize($tempDir.$file)<=1024)  return new JsonResponse(["result"=>-3]);
        //Obtenemos el trabajador asociado al usuario
        $worker=$workersRepository->findOneBy(["user"=>$this->getUser(),"active"=>1,"deleted"=>0]);
        if(!$worker) return new JsonResponse(["result"=>-4]);
        //Si el usuario no tiene extension configurada abortamos
        if($worker->getExtension()==null) return new JsonResponse(["result"=>-5]);
        $extension=$worker->getExtension();
        //Obtenemos los datos de la grabacion

        $tempCall=explode('-',$file);
        $call=[];
        switch($tempCall[0]){
          case "external":
              if($tempCall[1]==$extension){
                $call["type"]="Entrante";
                $call["extension"]=$extension;
                $call["remote"]=$tempCall[2];
                $call["filename"]=$file;
                $call["date"]=filemtime($tempDir.$file);
              }
          break;
          case "out":
              if($tempCall[2]==$extension){
                $call["type"]="Saliente";
                $call["extension"]=$extension;
                $call["remote"]=$tempCall[1];
                $call["filename"]=$file;
                $call["date"]=filemtime($tempDir.$file);
              }
          break;
          case "internal":
              if($tempCall[1]==$extension || $tempCall[2]==$extension){
                $call["type"]="Interna";
                $call["extension"]=$extension;
                $call["remote"]=$tempCall[1]==$worker->getExtension()?$tempCall[2]:$tempCall[1];
                $call["filename"]=$file;
                $call["date"]=filemtime($tempDir.$file);
              }
          break;
          default:
             return new JsonResponse(["result"=>-6]);
          break;
        }
        //Algun error adicional, posiblemente la extension no concuerde con la del fichero
        if(!$call)  return new JsonResponse(["result"=>-7]);

        //Comprobar que existe la entidad y el objeto
        if(!class_exists('\\'.$entity))
          return new JsonResponse(["result"=>-8]);
        $repository	= $this->getDoctrine()->getRepository('\\'.$entity);
        if(!$repository) return new JsonResponse(["result"=>-9]);
        $obj = $repository->find($entity_id);
        if(!$obj) return new JsonResponse(["result"=>-10]);

        $date = new \DateTime();
        $date->setTimestamp($call["date"]);
        //Crear estructura de carpetas final
        if(!file_exists($destDir) || !is_dir($destDir))
          mkdir($destDir, 0777, true);
        //Compiamos el fichero al directorio de destino
        $result=copy($tempDir.$file, $destDir.$file);
        if($result===false) return new JsonResponse(["result"=>-11]);

        //Creamos el objeto

        $callObj = new GlobaleCommentsCalls();
        $callObj->setCompany($this->getUser()->getCompany());
        $callObj->setUser($this->getUser());
        $callObj->setEntity($entity);
        $callObj->setEntityId($entity_id);
        $callObj->setType($call["type"]);
        $callObj->setExtension($call["extension"]);
        $callObj->setRemote($call["remote"]);
        $callObj->setFilename($call["filename"]);
        $callObj->setDate($date);
        $callObj->setActive(1);
        $callObj->setDeleted(0);
        $callObj->setDateadd(new \DateTime());
        $callObj->setDateupd(new \DateTime());
        $this->getDoctrine()->getManager()->persist($callObj);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse(["result"=>1, "id"=>$callObj->getId(), "file"=>$callObj->getFilename(), "calltype"=>$callObj->getType(), "extension"=>$callObj->getExtension(), "remote"=>$callObj->getRemote()]);
      }
       return new JsonResponse(array("result"=> -12));
    }


    /**
     * @Route("/api/globale/removecall/{id}", name="removeCallComment", defaults={"id"=0})
     */
    public function removeCallComment($id,RouterInterface $router,Request $request){
  	      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
          $commentCallRepository	= $this->getDoctrine()->getRepository(GlobaleCommentsCalls::class);
          //$destDir='A:\var\www\axiom.ferreteriacampollano.com\cloud\2\ERPCalls\\';
          $destDir=$this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'ERPCalls'.DIRECTORY_SEPARATOR;

          $call = null;
          if($id!=0){
            $call=$commentCallRepository->findOneBy(["id"=>$id, "active"=>1, "deleted"=>0]);
            //Comprobamos que el usuario sea el autor del comentario
            if(!$call || $call->getUser() != $this->getUser()){
              return new JsonResponse(["result"=>-1]);
            }else{
              //Borramos el fichero
              $result=unlink($destDir.$call->getFilename());
              if($result===false) return new JsonResponse(["result"=>-1]);
              //Marcamos como borrado
              $call->setDeleted(1);
              $call->setDateupd(new \DateTime());
              $this->getDoctrine()->getManager()->persist($call);
              $this->getDoctrine()->getManager()->flush();
              return new JsonResponse(["result"=>1, "id"=>$call->getId()]);
            }
          }
          return new JsonResponse(["result"=>-2]);
    }


}
