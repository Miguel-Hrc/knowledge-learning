<?php

namespace App\Repository;

use App\Entity\CommandItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for managing CommandItem entities.
 *
 * This class provides methods to access and manipulate CommandItem entities
 * from the database using Doctrine ORM.
 *
 * @extends ServiceEntityRepository<CommandItem>
 */
class CommandItemRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The Doctrine manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandItem::class);
    }

}