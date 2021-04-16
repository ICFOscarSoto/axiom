<?php

namespace App\Modules\Cloud\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\GlobaleMenuOptions;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\Globale\Utils\GlobaleEntityUtils;
use App\Modules\Globale\Utils\GlobaleListUtils;
use App\Modules\Globale\Utils\GlobaleFormUtils;
use App\Modules\Cloud\Entity\CloudFiles;
use App\Modules\Globale\Entity\GlobaleScanners;
use App\Modules\Cloud\Utils\CloudFilesUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class CloudController extends Controller
{

	 private $class=CloudFiles::class;
	 private $utilsClass=CloudFilesUtils::class;

	/**
	 * @Route("/api/cloud/files/{path}/{id}/list", name="fileslist")
	 */
	public function fileslist($path, $id, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
  	$repository = $manager->getRepository($this->class);
		$listUtils=new GlobaleListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Files.json"),true);
		$return=$listUtils->getRecords($user,$repository,$request,$manager,$listFields, $this->class,[["type"=>"and", "column"=>"idclass", "value"=>$id],["type"=>"and", "column"=>"path", "value"=>$path]]);

    //Add icons to the rows
    foreach($return["data"] as $key=>$file){
      $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
      if(file_exists($this->get('kernel')->getRootDir() . "/../public/images/filetype/".$ext.".png"))
        $icon="<img src=\"/images/filetype/".$ext.".png\" height=\"40\">";
      else $icon="<a src=\"/images/filetype/file.png\" height=\"40\">";
      $return["data"][$key]["icon"]=$icon;
    }
		return new JsonResponse($return);
	}

	/**
	 * @Route("/api/cloud/files/{path}/{id}/form/{types}/{module}", name="cloudfiles", defaults={"types"="[]", "module"="Globale"})
	 */
	public function cloudfiles($path, $id, $types, $module, RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$this->router = $router;
		$user=$this->getUser();
		$scanner=$user->getScanner();
		$utils = new $this->utilsClass();
		$types=json_decode($types);
		$templateLists=["id"=>"cloudZone".$path, "list"=>[$utils->formatList($user,$path,$id)], "types"=>$types, "path"=>$this->generateUrl("cloudUpload",["id"=>$id, "path"=>$path])];
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			return $this->render('@Cloud/genericlistfiles.html.twig', [
				'cloudConstructor' => $templateLists,
				'scanner' => $scanner,
				'path' => $path,
				'id' => $id,
				'module' => $module
				]);
			}
		return new RedirectResponse($this->router->generate('app_login'));
		}


    /**
     * @Route("/api/cloud/files/{path}/{id}/upload/{module}", name="cloudUpload", defaults={"module"=""})
     */
    public function cloudUpload($id,$path,$module, RouterInterface $router, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			$type=$request->request->get('type');
			//Check if filespace in disk quota
			$company=$this->getUser()->getCompany();
			$diskUsage=$company->getDiskUsages();
			if($diskUsage[0]->getDiskspace()-$diskUsage[0]->getDiskusage()<=0)  return new JsonResponse(["result"=>-10]);

      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        $manager = $this->getDoctrine()->getManager();
        $output = array('uploaded' => false);
        if($path!=NULL){
						 if($id==0) $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/temp/'.$this->getUser()->getId().'/'.$path.'/';
						 else $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/'.$path.'/'.$id.'/';
             $file = $request->files->get('file');
             //$fileName = md5(uniqid()).'.'.$file->guessExtension();
             $fileName = date("YmdHis").'_'.md5(uniqid());
             //$fileName = $file->getClientOriginalName();
             if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
                 mkdir($uploadDir, 0775, true);
             }
             if ($file->move($uploadDir, $fileName)) {
                $output['uploaded'] = true;
                $output['fileName'] = $fileName;
                $cloudFile=new CloudFiles();
                $cloudFile->setCompany($this->getUser()->getCompany());
                $cloudFile->setUser($this->getUser());
                $cloudFile->setName($file->getClientOriginalName());
								$cloudFile->setType($type);
                $cloudFile->setHashname($fileName);
                $cloudFile->setSize(filesize($uploadDir.$fileName));
                $cloudFile->setPath($path);
                //$cloudFile->setRoles('');
                $cloudFile->setIdclass($id);
                $cloudFile->setPublic(true);
                $cloudFile->setDateupd(new \DateTime());
                $cloudFile->setDateadd(new \DateTime());
                $cloudFile->setActive(true);
                $cloudFile->setDeleted(false);
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($cloudFile);
                $manager->flush();

							$classModule='\App\Modules\\'.$module.'\Entity\\'.$path;
 							 if(class_exists($classModule)){
 								 $classRepository=$this->getDoctrine()->getRepository($classModule);
 								 $obj=$classRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
 								 if($obj){
 									 if(method_exists($obj, 'postUploadCloudFile')){
 										 $obj->postUploadCloudFile($cloudFile, $this->getDoctrine());
 									 }
 								 }
 							 }
             }
             return new JsonResponse($output);
        }else return new JsonResponse($output);
      }else return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/api/cloud/files/{id}/get", name="cloudGetFiles")
		 */
		public function getFiles($id, RouterInterface $router, Request $request){

        $cloudFileRepository = $this->getDoctrine()->getRepository(CloudFiles::class);
        $cloudFile=$this->getDoctrine()->getRepository(CloudFiles::class)->find($id);
				if(!$cloudFile) return new RedirectResponse($router->generate('app_login'));
				//$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
	      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER') || $cloudFile->getHashname()==$request->query->get('hash')) {
        if($cloudFile!=NULL){
          $filename=$uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$cloudFile->getCompany()->getId().'/'.$cloudFile->getPath().'/'.$cloudFile->getIdclass().'/'.$cloudFile->getHashname();
          $response = new BinaryFileResponse($filename);
          $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
          if($mimeTypeGuesser->isSupported()){
            $response->headers->set('Content-Type', $mimeTypeGuesser->guess($filename));
          }else{
            $response->headers->set('Content-Type', 'text/plain');
          }
          $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$cloudFile->getName());
          return $response;
        }return new Response('');
      }else return new RedirectResponse($router->generate('app_login'));
		}
    /**
		 * @Route("/api/cloud/files/{id}/download", name="cloudDownloadFiles")
		 */
		public function downloadFiles($id){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        $cloudFileRepository = $this->getDoctrine()->getRepository(CloudFiles::class);
        $cloudFile=$this->getDoctrine()->getRepository(CloudFiles::class)->find($id);
        if($cloudFile!=NULL){
					if($cloudFile->getIdclass()==0)
					  $filename=$uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$cloudFile->getCompany()->getId().'/temp/'.$cloudFile->getUser()->getId().'/'.$cloudFile->getPath().'/'.$cloudFile->getHashname();
          	else $filename=$uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$cloudFile->getCompany()->getId().'/'.$cloudFile->getPath().'/'.$cloudFile->getIdclass().'/'.$cloudFile->getHashname();
          $response = new BinaryFileResponse($filename);
          $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$cloudFile->getName());
          return $response;
        }return new Response('');
      }else return new RedirectResponse($this->router->generate('app_login'));
		}

		/**
		 * @Route("/api/cloud/files/waitforscan/{id_scanner}/{path}/{id}/{module}", name="cloudWaitForScan", defaults={"id"=0, "module"=""})
		 */
		public function cloudWaitForScan($id, $id_scanner, $path, $module, RouterInterface $router, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        $scannersRepository = $this->getDoctrine()->getRepository(GlobaleScanners::class);
        $scanner=$scannersRepository->findOneBy(["id"=>$id_scanner, "active"=>1, "deleted"=>0]);
				if(!$scanner) return new JsonResponse(["result"=>-1]);
				if(!(file_exists($scanner->getPath()) && is_dir($scanner->getPath()))) return new JsonResponse(["result"=>-2]);
				$type=$request->request->get('type');
				$time=time();
				//Clear scanner directory
				/*if($scanner->getPath()!='/' && $scanner->getPath()!='/home/'){
					$files = glob($scanner->getPath()."*");
					foreach($files as $file){ // iterate files
					  if(is_file($file)) {
					    unlink($file); // delete file
					  }
					}
				}*/
				$count=0; $scanned=false;
				while(!$scanned && $count<=$scanner->getTimeout()){
					$files = glob($scanner->getPath()."*");
					$files = array_merge ( array_filter($files, function ($file) use ($time){ return filemtime($file) >= $time; }), array_filter($files, function ($file) use ($time){ return filectime($file) >= $time; }));
					if ($files)
						{
					   $scanned=true;
						 //Copy file
						 if($id==0) $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/temp/'.$this->getUser()->getId().'/'.$path.'/';
						 else $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/'.$path.'/'.$id.'/';
						 $fileName = date("YmdHis").'_'.md5(uniqid());
						 if (!file_exists($uploadDir) && !is_dir($uploadDir)) {
						 		mkdir($uploadDir, 0775, true);
						 }
						 if (rename($files[0],$uploadDir.$fileName)) {
							 //chown($uploadDir.$fileName, 'www-data');
							 //chgrp($uploadDir.$fileName, 'www-data');
							 //chmod($uploadDir.$fileName, 0774);
						 	 $cloudFile=new CloudFiles();
						 	 $cloudFile->setCompany($this->getUser()->getCompany());
						 	 $cloudFile->setUser($this->getUser());
						 	 $cloudFile->setName(basename($files[0]));
						 	 $cloudFile->setType($type);
						 	 $cloudFile->setHashname($fileName);
						 	 $cloudFile->setSize(filesize($uploadDir.$fileName));
						 	 $cloudFile->setPath($path);
						 	 //$cloudFile->setRoles('');
						 	 $cloudFile->setIdclass($id);
						 	 $cloudFile->setPublic(true);
						 	 $cloudFile->setDateupd(new \DateTime());
						 	 $cloudFile->setDateadd(new \DateTime());
						 	 $cloudFile->setActive(true);
						 	 $cloudFile->setDeleted(false);
						 	 $manager = $this->getDoctrine()->getManager();
						 	 $manager->persist($cloudFile);
						 	 $manager->flush();

							 $classModule='\App\Modules\\'.$module.'\Entity\\'.$path;
							 if(class_exists($classModule)){
								 $classRepository=$this->getDoctrine()->getRepository($classModule);
								 if(property_exists($classModule, "company"))
								 		$obj=$classRepository->findOneBy(["id"=>$id, "company"=>$this->getUser()->getCompany()]);
								 	else $obj=$classRepository->findOneBy(["id"=>$id]);
								 if($obj){
									 if(method_exists($obj, 'postUploadCloudFile')){
										 $obj->postUploadCloudFile($cloudFile, $this->getDoctrine());
									 }
								 }
							 }

						 }else{
							 return new JsonResponse(["result"=>-4, "files"=>[]]);
						 }

						 return new JsonResponse(["result"=>1, "files"=>$files]);
						}
					sleep(1);
					$count++;
				}
					return new JsonResponse(["result"=>0, "files"=>[]]);
			}
				return new JsonResponse(["result"=>-3, "files"=>[]]);
		}


  	/**
  	* @Route("/{_locale}/cloud/files/{id}/disable", name="disableFile")
  	*/
  	public function disable($id){
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$entityUtils=new GlobaleEntityUtils();
  		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
  		return new JsonResponse(array('result' => $result));
  	}
  	/**
  	* @Route("/{_locale}/cloud/files/{id}/enable", name="enableFile")
  	*/
  	public function enable($id){
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$entityUtils=new GlobaleEntityUtils();
  		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
  		return new JsonResponse(array('result' => $result));
  	}
  	/**
  	* @Route("/{_locale}/cloud/files/{id}/delete", name="deleteFile")
  	*/
  	public function delete($id){
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$entityUtils=new GlobaleEntityUtils();
  		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
  		return new JsonResponse(array('result' => $result));
  	}
}
