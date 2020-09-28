<?php
namespace App\Modules\Globale\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\HR\Entity\HRWorkers;
use Impulze\Bundle\InterventionImageBundle\ImageManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class GlobaleImagesController extends Controller implements ContainerAwareInterface
{

	use ContainerAwareTrait;

	/**
     * @Route("/api/files/{ext}/getimage", name="getFilesImage")
     */
	public function getFilesImage($ext, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			$image_path = $this->get('kernel')->getRootDir() . '/../public/images/filetype/';
			if(file_exists($image_path.$ext.'.png'))
				$filename = $ext.'.png';
			else $filename = 'file.png';
			$response = new BinaryFileResponse($image_path.$filename);
			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
			if($mimeTypeGuesser->isSupported()){
				$seconds_to_cache = 7200;
				$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
				$response->headers->set("Expires", $ts);
				$response->headers->set("Pragma", "cache");
				$response->headers->set("Cache-Control", "max-age=$seconds_to_cache");
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($image_path.$filename));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
			);
			return $response;
		}
		return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
     * @Route("/api/company/{id}/getimage", name="getCompanyImage", defaults={"id"=0})
     */
	public function getCompanyImage($id, Request $request)
	{
			$type = $request->request->get("type",'');
			$id=$id==0?$this->getUser()->getCompany()->getId():$id;
			$image_path = $this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'company'.DIRECTORY_SEPARATOR;
			//$image_path = $this->get('kernel')->getRootDir() . '/../public/images/companies/';
			if(file_exists($image_path.$id."-".($type!=''?$type:"medium").'.png'))
				$filename = $id."-".($type!=''?$type:"medium").'.png';
			else if(file_exists($image_path.$id."-".($type!=''?$type:"medium").'.jpg'))
				$filename = $id."-".($type!=''?$type:"medium").'.jpg';
			else $filename = '1.png';

			$response = new BinaryFileResponse($image_path.$filename);
			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
			if($mimeTypeGuesser->isSupported()){
				$seconds_to_cache = 7200;
				$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
				$response->headers->set("Expires", $ts);
				$response->headers->set("Pragma", "cache");
				$response->headers->set("Cache-Control", "max-age=$seconds_to_cache");
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($image_path.$filename));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
			);
			return $response;
	}


	/**
	* @Route("/api/{type}/{id}/uploadimage", name="uploadImage")
	*/
	public function uploadImage($id, $type, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		$file = $request->files->get('picture');
		if($file===null){
			 $fileBlob64 = $request->request->get('picture',null);
			 $tempName = md5(uniqid()).'.jpg';
		}else{
			$tempName = md5(uniqid()).'.'.$file->guessExtension();
		}
		$user=$this->getUser();

		//Check if filespace in disk quota
		$company=$this->getUser()->getCompany();
		$diskUsage=$company->getDiskUsages();
		if($diskUsage[0]->getDiskspace()-$diskUsage[0]->getDiskusage()<=0)  return new JsonResponse(["result"=>-10]);

		switch($type){  //For check permissions

			case "company":
			case "companydark":
					//Check if role_globale
					if(array_search('ROLE_GLOBAL',$user->getRoles())!==FALSE){
						$basePath = $this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR;
					}else $basePath = $this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR;
				break;
			default:
				$basePath = $this->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'cloud'.DIRECTORY_SEPARATOR.$this->getUser()->getCompany()->getId().DIRECTORY_SEPARATOR;

		}

			/*if($file!==null){
				$tempName = md5(uniqid()).'.'.$file->guessExtension();*/

			$tempPath = $basePath.'temp'.DIRECTORY_SEPARATOR.$tempName;
			//Create basepath if it not exists
			if (!file_exists($basePath.'temp')) {
			    mkdir($basePath.'temp', 0777, true);
			}
			if($file===null){
				file_put_contents($tempPath, base64_decode($fileBlob64));
			}else{
				$file->move($basePath.'temp'.DIRECTORY_SEPARATOR, $tempName);
			}

			//Create type path if it not exists
			if (!file_exists($basePath.'images'.DIRECTORY_SEPARATOR.$type)) {
			    mkdir($basePath.'images'.DIRECTORY_SEPARATOR.$type, 0777, true);
			}else{
				//If dir exist clear interface
				//No estoy seguro de que esto haga falta
				/*$files = glob($basePath.'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.'*'); // get all file names
				foreach($files as $file){ // iterate files
				  if(is_file($file))
				    unlink($file); // delete file
				}*/
			}

			if($type=="products"){
				//find last image
				$found=true;
				$i=1;
			  while($found==true){
					if(file_exists($basePath.'temp'.DIRECTORY_SEPARATOR.$id."-".$i.'-large.png') || file_exists($basePath.'temp'.DIRECTORY_SEPARATOR.$id."-".$i.'-large.jpg')){
						$i++;
					}else{
						$found=false;
					}
				}
			}else $i=null;

			//50 256 640 1024
			$manager = new ImageManager($this->container);

			$image = $manager->make($tempPath);
			$image->resize(100, null, function ($constraint) {
			    $constraint->aspectRatio();
			    $constraint->upsize();
			});
			$image->save($basePath.'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$id.($i!=null?'-'.$i:'').'-thumb.png');

			$image = $manager->make($tempPath);
			$image->resize(256, null, function ($constraint) {
			    $constraint->aspectRatio();
			    $constraint->upsize();
			});
			$image->save($basePath.'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$id.($i!=null?'-'.$i:'').'-small.png');

			$image = $manager->make($tempPath);
			$image->resize(640, null, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
			});
			$image->save($basePath.'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$id.($i!=null?'-'.$i:'').'-medium.png');

			$image = $manager->make($tempPath);
			$image->resize(1024, null, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
			});
			$image->save($basePath.'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$id.($i!=null?'-'.$i:'').'-large.png');

			if (isset($tempPath)) { unlink($tempPath); }

			return new JsonResponse(["result"=>1]);

	}


	/**
	* @Route("/api/user/{id}/getimage", name="getUserImage", defaults={"id"=0})
	*/
	public function getUserImage($id, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
			if($id==0) $id=$this->getUser()->getId();
			$userRepository = $this->getDoctrine()->getRepository(GlobaleUsers::class);
				$user=$userRepository->find($id);
				//$image_path = $this->get('kernel')->getRootDir() . '/../public/images/users/';
				$image_path = $this->get('kernel')->getRootDir().'/../cloud/'.$user->getCompany()->getId().'/images/users/';
				if(file_exists($image_path.$id.'-thumb.png'))
					$filename = $id.'-thumb.png';
				else if(file_exists($image_path.$id.'-thumb.jpg'))
					$filename = $id.'-thumb.jpg';
				else $filename = 'no-thumb.jpg';
			//}else $filename = 'no-thumb.jpg';
			$response = new BinaryFileResponse($image_path.$filename);
			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
			if($mimeTypeGuesser->isSupported()){
				$seconds_to_cache = 7200;
				$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($image_path.$filename));
				$response->headers->set("Expires", $ts);
				$response->headers->set("Pragma", "cache");
				$response->headers->set("Cache-Control", "max-age=$seconds_to_cache");
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($image_path.$filename));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
			);
			return $response;
		}
	  return new RedirectResponse($this->router->generate('app_login'));
	}

	/**
	* @Route("/api/{type}/{id}/{size}/getimage/{number}", name="getImage", defaults={"type"="users", "size"="medium", "id"=0, "number"=0 })
	*/
	public function getWorkerImage($type, $id, $size, $number, Request $request)
	{
		if($type!="company" && $type!="companydark"){
			$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
			if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
				return new RedirectResponse($this->router->generate('app_login'));
			}
		}

			$user=$this->getUser();
			if($type=="company" || $type=="companydark") $image_path = $this->get('kernel')->getRootDir().'/../cloud/'.$id.'/images/';
			else $image_path = $this->get('kernel')->getRootDir().'/../cloud/'.$user->getCompany()->getId().'/images/';
			//$image_path = $this->get('kernel')->getRootDir() . '/../public/images/workers/';
			if($number==0) $total_path=$image_path.$type.'/'.$id.'-'.$size;
				else $total_path=$image_path.$type.'/'.$id.'/'.$id.'-'.$number.'-'.$size;
			if($number==0) $prev_filename=$id.'-'.$size;
				else $prev_filename=$id.'-'.$number.'-'.$size;

			if(file_exists($total_path.'.png'))
				$filename = $prev_filename.'.png';
				else
				if(file_exists($total_path.'.jpg'))
					$filename = $prev_filename.'.jpg';
					else if($type=="companydark" && file_exists($total_path.'.png')){
						$filename = $prev_filename.'.png'; $type="company";
					} else if($type=="companydark" && file_exists($total_path.'.jpg')){
							$filename = $prev_filename.'.jpg'; $type="company";
					}else {
							$image_path = $this->get('kernel')->getRootDir().'/../cloud/0/images/';
							$filename = 'no-thumb.jpg';
					}
			//}else $filename = 'no-thumb.jpg';
			if($number==0) $path=$image_path.$type.'/'.$filename;
				else $path=$image_path.$type.'/'.$id.'/'.$filename;
			$response = new BinaryFileResponse($path);
			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
			if($mimeTypeGuesser->isSupported()){
				$seconds_to_cache = 7200;
				$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($path));
				$response->headers->set("Expires", $ts);
				$response->headers->set("Pragma", "cache");
				$response->headers->set("Cache-Control", "max-age=$seconds_to_cache");
				$response->headers->set('Content-Type', $mimeTypeGuesser->guess($path));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
			);

			return $response;

	}
}
