<?php

namespace App\Repository;

use App\Entity\Command;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for managing Command entities.
 *
 * This class provides methods to access and manipulate Command entities
 * from the database using Doctrine ORM.
 *
 * @extends ServiceEntityRepository<Command>
 */
class CommandRepository extends ServiceEntityRepository
{
    /**
     * CommandRepository constructor.
     *
     * @param ManagerRegistry $registry The ManagerRegistry instance used by Doctrine.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Command::class);
    }

}