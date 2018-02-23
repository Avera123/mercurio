<?php

namespace App\Repository;

use App\Entity\Configuracion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Configuracion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Configuracion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Configuracion[]    findAll()
 * @method Configuracion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfiguracionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Configuracion::class);
    }


    public function getUrl(){
        $em = $this->getEntityManager();
        $arConfiguracion = $em->getRepository('App:Configuracion')->find(1);
        return $arConfiguracion->getServiceUrl();
    }

}
