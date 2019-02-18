<?php

namespace App\Modules\Cloud\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Modules\Globale\Entity\MenuOptions;
use App\Modules\Globale\Entity\Users;
use App\Modules\Globale\Utils\EntityUtils;
use App\Modules\Globale\Utils\ListUtils;
use App\Modules\Globale\Utils\FormUtils;
use App\Modules\Cloud\Entity\CloudFiles;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;

class CloudController extends Controller
{

	 private $class=CloudFiles::class;

	/**
	 * @Route("/api/cloud/files/list", name="fileslist")
	 */
	public function fileslist(RouterInterface $router,Request $request){
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$user = $this->getUser();
		$locale = $request->getLocale();
		$this->router = $router;
		$manager = $this->getDoctrine()->getManager();
  	$repository = $manager->getRepository($this->class);
		$listUtils=new ListUtils();
		$listFields=json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Files.json"),true);
		$return=$listUtils->getRecords($repository,$request,$manager,$listFields, $this->class);

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
	public function formatList($user){
		$list=[
			'id' => 'listFiles',
			'route' => 'fileslist',
			'routeParams' => ["id" => $user->getId()],
			'orderColumn' => 1,
			'orderDirection' => 'ASC',
			'tagColumn' => 1,
			'fields' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/Files.json"),true),
			'fieldButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/FilesFieldButtons.json"),true),
			'topButtons' => json_decode(file_get_contents (dirname(__FILE__)."/../Lists/FilesTopButtons.json"),true)
		];
		return $list;
	}

    /**
     * @Route("/api/files/{path}/{id}/upload", name="cloudUpload")
     */
    public function cloudUpload($id,$path, RouterInterface $router, Request $request){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        $manager = $this->getDoctrine()->getManager();
        $output = array('uploaded' => false);
        if($path!=NULL){
          $uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$this->getUser()->getCompany()->getId().'/'.$path.'/'.$id.'/';
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
             }
             return new JsonResponse($output);
        }else return new JsonResponse($output);
      }else return new RedirectResponse($this->router->generate('app_login'));
    }

		/**
		 * @Route("/api/cloud/files/{id}/get", name="cloudGetFiles")
		 */
		public function getFiles($id){
      $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
      if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
        $cloudFileRepository = $this->getDoctrine()->getRepository(CloudFiles::class);
        $cloudFile=$this->getDoctrine()->getRepository(CloudFiles::class)->find($id);
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
      }else return new RedirectResponse($this->router->generate('app_login'));
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
          $filename=$uploadDir=$this->get('kernel')->getRootDir() . '/../cloud/'.$cloudFile->getCompany()->getId().'/'.$cloudFile->getPath().'/'.$cloudFile->getIdclass().'/'.$cloudFile->getHashname();
          $response = new BinaryFileResponse($filename);
          $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$cloudFile->getName());
          return $response;
        }return new Response('');
      }else return new RedirectResponse($this->router->generate('app_login'));
		}

  	/**
  	* @Route("/{_locale}/cloud/files/{id}/disable", name="disableFile")
  	*/
  	public function disable($id){
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$entityUtils=new EntityUtils();
  		$result=$entityUtils->disableObject($id, $this->class, $this->getDoctrine());
  		return new JsonResponse(array('result' => $result));
  	}
  	/**
  	* @Route("/{_locale}/cloud/files/{id}/enable", name="enableFile")
  	*/
  	public function enable($id){
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$entityUtils=new EntityUtils();
  		$result=$entityUtils->enableObject($id, $this->class, $this->getDoctrine());
  		return new JsonResponse(array('result' => $result));
  	}
  	/**
  	* @Route("/{_locale}/cloud/files/{id}/delete", name="deleteFile")
  	*/
  	public function delete($id){
  		$this->denyAccessUnlessGranted('ROLE_ADMIN');
  		$entityUtils=new EntityUtils();
  		$result=$entityUtils->deleteObject($id, $this->class, $this->getDoctrine());
  		return new JsonResponse(array('result' => $result));
  	}
}
