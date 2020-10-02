<<<<<<< HEAD
<?php

namespace App\Repository\Modules\AERP\Entity;

use App\Modules\AERP\Entity\AERPExpirations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPCalls|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPCalls|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPCalls[]    findAll()
 * @method ERPCalls[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AERPExpirationsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AERPExpirations::class);
    }


}
=======
            ->getResult()
>>>>>>> 0c5fa59ca96f57398f8bea5da882c3c04878d7c7
