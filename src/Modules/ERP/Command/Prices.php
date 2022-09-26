<?php
namespace App\Modules\ERP\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Modules\Globale\Entity\GlobaleUsers;
use App\Modules\ERP\Entity\ERPProducts;
use App\Modules\ERP\Entity\ERPProductsVariants;
use App\Modules\ERP\Entity\ERPProductsSuppliers;
use App\Modules\ERP\Entity\ERPProductsSuppliersPrices;
use App\Modules\Globale\Entity\GlobaleCompanies;



class Prices extends ContainerAwareCommand
{
  protected function configure(){
        $this
            ->setName('ERP:prices')
            ->setDescription('Gestión de precios')
            ->addArgument('entity', InputArgument::REQUIRED, '¿Entidad que sincronizar?')
        ;
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->doctrine = $this->getContainer()->get('doctrine');
    $this->entityManager = $this->doctrine->getManager();
    $entity = $input->getArgument('entity');
    $repositoryCompanies=$this->doctrine->getRepository(GlobaleCompanies::class);
    $this->company=$repositoryCompanies->find(2);
    $output->writeln('');
    $output->writeln('Comenzando sincronizacion Precios');
    $output->writeln('==================================');
    switch($entity){
      case 'netprices': $this->getNetPrices($input, $output);
      break;
    }
  }

  public function getNetPrices(InputInterface $input, OutputInterface $output){
    $repositoryProducts=$this->doctrine->getRepository(ERPProducts::class);
    $products=$repositoryProducts->findBy(["netprice"=>1, "deleted"=>0]);
    $output->writeln('Añadiendo precios netos a la tabla ProductsSuppliersPrices....');
    foreach ($products as $product) {
      $repositoryProductsVariants=$this->doctrine->getRepository(ERPProductsVariants::class);
      $productvariant = $repositoryProductsVariants->findOneBy(["product"=>$product, "variant"=>null, "deleted"=>0, "active"=>1]);
      if ($productvariant!=null){
        $repositoryProductsSuppliers=$this->doctrine->getRepository(ERPProductsSuppliers::class);
        $productsupplier = $repositoryProductsVariants->findOneBy(["productvariant"=>$productvariant, "supplier"=>$product->getSupplier(), "deleted"=>0, "active"=>1]);
        if ($productsupplier!=null){
          $repositoryProductsSuppliersPrices=$this->doctrine->getRepository(ERPProductsSuppliersPrices::class);
          $productsupplierprice=$repositoryProductsSuppliersPrices->findOneBy(["productsupplier"=>$productsupplier, "deleted"=>0, "active"=>1]);
          if ($productsupplierprice==null and $product->getSupplier()!=null){
            $output->writeln('Añadiendo el precio neto '.$product->getShoppingPrice().' al producto '.$product->getCode());
            $obj = new ERPProductsSuppliersPrices();
            $obj->setProductSupplier($productsupplier);
            $obj->setQuantity(1);
            $obj->setDateadd(new \Datetime());
            $obj->setDateupd(new \Datetime());
            $obj->setActive(1);
            $obj->setDeleted(0);
            $obj->setPrice($product->getShoppingPrice());
            $this->doctrine->getManager()->merge($obj);
            $this->doctrine->getManager()->flush();
          }
          $this->doctrine->getManager()->clear();
        }
      }
    }
  }


}
