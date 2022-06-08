<?php

namespace App\Modules\ERP\Repository;

use App\Modules\ERP\Entity\ERPBuyOrdersContacts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ERPBuyOrdersContacts|null find($id, $lockMode = null, $lockVersion = null)
 * @method ERPBuyOrdersContacts|null findOneBy(array $criteria, array $orderBy = null)
 * @method ERPBuyOrdersContacts[]    findAll()
 * @method ERPBuyOrdersContacts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ERPBuyOrdersContactsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ERPBuyOrdersContacts::class);
    }

    // /**
    //  * @return ERPBuyOrdersContacts[] Returns an array of ERPBuyOrdersContacts objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ERPBuyOrdersContacts
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // Poner todas los contactos con phone a '~|/%^%/|~' indicando que no es válido
    // Si se actualiza se deja el contacto sino se borra
    // Tipo 0 Contactos proveedor 1 Cliente
    public function setDeletecontacts($buyorder_id, $type){
      $query="update erpbuy_orders_contacts set phone='~|/%^%/|~' where buyorder_id=$buyorder_id and type=$type";
      $this->getEntityManager()->getConnection()->executeQuery($query);
      return true;
    }
    // Elimina los contactos con phone='~|/%^%/|~' del pedido ya que ya no son válidos
    // Tipo 0 Contactos proveedor 1 Cliente
    public function deleteContacts($buyorder_id, $type){
      $query="delete from erpbuy_orders_contacts where phone='~|/%^%/|~' and buyorder_id=$buyorder_id and type=$type";
      $this->getEntityManager()->getConnection()->executeQuery($query);
      return true;
    }
}
