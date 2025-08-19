<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository class for managing Theme entities.
 *
 * This class provides methods to interact with the database for Theme entities,
 * including finding, adding, and removing themes.
 *
 * @extends ServiceEntityRepository<Theme>
 *
 * @method Theme|null find($id, $lockMode = null, $lockVersion = null) Finds a Theme by its ID
 * @method Theme|null findOneBy(array $criteria, array $orderBy = null) Finds a single Theme by criteria
 * @method Theme[]    findAll() Finds all Theme entities
 * @method Theme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) Finds Themes by criteria
 */
class ThemeRepository extends ServiceEntityRepository
{
    /**
     * ThemeRepository constructor.
     *
     * @param ManagerRegistry $registry The Doctrine ManagerRegistry instance
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }
}