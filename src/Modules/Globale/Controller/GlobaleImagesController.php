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
     * @Route("/api/company/{id}/getimage", name="getCompanyImage")
     */
	public function getCompanyImage($id, Request $request)
	{
			$type = $request->request->get("type",'');
			$image_path = $this->get('kernel')->getRootDir() . '/../public/images/companies/';
			if(file_exists($image_path.$id.$type.'.png'))
				$filename = $id.$type.'.png';
			else if(file_exists($image_path.$id.$type.'.jpg'))
				$filename = $id.$type.'.jpg';
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
		$file = $request->files->get('picture');
		$user=$this->getUser();
		$basePath = $this->get('kernel')->getRootDir().'/../cloud/'.$user->getCompany()->getId().'/';
		$tempName = md5(uniqid()).'.'.$file->guessExtension();
		$tempPath = $basePath.'temp/'.$tempName;
		//Create basepath if it not exists
		if (!file_exists($basePath.'temp')) {
		    mkdir($basePath.'temp', 0777, true);
		}
		$file->move($basePath.'temp/', $tempName);
		//Create type path if it not exists
		if (!file_exists($basePath.'images/'.$type)) {
		    mkdir($basePath.'images/'.$type, 0777, true);
		}

		//50 256 640 1024
		$manager = new ImageManager($this->container);

		$image = $manager->make($tempPath);
		$image->fit(100, null, function ($constraint) {
		    $constraint->upsize();
		});
		$image->save($basePath.'images/'.$type.'/'.$id.'-thumb.jpg');

		$image = $manager->make($tempPath);
		$image->resize(256, null, function ($constraint) {
		    $constraint->aspectRatio();
		    $constraint->upsize();
		});
		$image->save($basePath.'images/'.$type.'/'.$id.'-small.jpg');

		$image = $manager->make($tempPath);
		$image->resize(640, null, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
		});
		$image->save($basePath.'images/'.$type.'/'.$id.'-medium.jpg');

		$image = $manager->make($tempPath);
		$image->resize(1024, null, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
		});
		$image->save($basePath.'images/'.$type.'/'.$id.'-large.jpg');

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
	* @Route("/api/{type}/{id}/{size}/getimage", name="getImage", defaults={"type"="users", "size"="medium", "id"=0 })
	*/
	public function getWorkerImage($type, $id, $size, Request $request)
	{
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
		if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {

			$user=$this->getUser();
			$image_path = $this->get('kernel')->getRootDir().'/../cloud/'.$user->getCompany()->getId().'/images//'.$type.'/';
			//$image_path = $this->get('kernel')->getRootDir() . '/../public/images/workers/';
			if(file_exists($image_path.$id.'-'.$size.'.jpg'))
				$filename = $id.'-'.$size.'.jpg';
				//else if(file_exists($image_path.$id.'-thumb.jpg'))
				//	$filename = $id.'-thumb.jpg';
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
}
