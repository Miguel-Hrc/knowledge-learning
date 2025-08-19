<?php

namespace App\Repository;

use App\Entity\Certification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for managing Certification entities.
 *
 * This repository provides the ability to fetch, save, and
 * manipulate Certification entities using Doctrine ORM.
 *
 * @extends ServiceEntityRepository<Certification>
 */
class CertificationRepository extends ServiceEntityRepository
{
    /**
     * CertificationRepository constructor.
     *
     * @param ManagerRegistry $registry The manager registry used by Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certification::class);
    }

}