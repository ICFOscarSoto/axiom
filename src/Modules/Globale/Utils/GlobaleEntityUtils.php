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
		return true;
	}
}
