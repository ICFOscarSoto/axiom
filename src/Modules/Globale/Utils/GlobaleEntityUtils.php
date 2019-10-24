<?php
namespace App\Modules\Globale\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GlobaleEntityUtils
{
	public function disableObject($id, $class, $doctrine)
    {
		$object= $doctrine
			->getRepository($class)
			->find($id);
		if(method_exists($object, "disable"))
				$object->delete($doctrine);
		$object->setActive(false);
		$object->setDateupd(new \DateTime());
		$manager = $doctrine->getManager();
        $manager->persist($object);
		$manager->flush();
		return true;
	}

	public function enableObject($id, $class, $doctrine)
    {
		$object= $doctrine
			->getRepository($class)
			->find($id);
		if(method_exists($object, "enable"))
			$object->delete($doctrine);
		$object->setActive(true);
		$object->setDateupd(new \DateTime());
		$manager = $doctrine->getManager();
        $manager->persist($object);
		$manager->flush();
		return true;
	}

	public function deleteObject($id, $class, $doctrine){
		$object= $doctrine
			->getRepository($class)
			->find($id);
		$object->setActive(false);
		$object->setDeleted(true);
		$object->setDateupd(new \DateTime());
		$manager = $doctrine->getManager();
    $manager->persist($object);
		$manager->flush();
		if(method_exists($object, "delete"))
			$object->delete($doctrine);
		return true;
	}
}
