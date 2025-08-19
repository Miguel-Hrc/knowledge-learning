<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Repository class for managing User entities.
 *
 * Provides methods to find, add, remove, and update User entities.
 *
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null) Finds a User by its ID
 * @method User|null findOneBy(array $criteria, array $orderBy = null) Finds a single User by criteria
 * @method User[]    findAll() Returns all User entities
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) Returns Users by criteria
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * UserRepository constructor.
     *
     * @param ManagerRegistry $registry The Doctrine registry manager
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Adds a User entity to the database.
     *
     * @param User $entity The User entity to add
     * @param bool $flush Whether to flush changes immediately
     */
    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes a User entity from the database.
     *
     * @param User $entity The User entity to remove
     * @param bool $flush Whether to flush changes immediately
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Upgrades (rehashes) the user's password automatically over time.
     *
     * @param PasswordAuthenticatedUserInterface $user The user whose password will be upgraded
     * @param string $newHashedPassword The new hashed password
     *
     * @throws UnsupportedUserException If the provided user is not an instance of User
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->add($user, true);
    }

    /**
     * Finds a User by username or email.
     *
     * @param string $identifier The username or email to search for
     * @return User|null The matching User entity or null if not found
     */
    public function findOneByUsernameOrEmail(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.name = :identifier')
            ->orWhere('u.email = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }
}