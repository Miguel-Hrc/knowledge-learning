<?php

namespace App\Repository;

use App\Document\UserDocument;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Repository class for managing UserDocument entities in MongoDB.
 *
 * Provides methods to find, add, remove, and update UserDocument entities.
 */
class UserRepositoryMongo extends DocumentRepository
{
    /**
     * @var DocumentManager|null
     */
    protected $dm;

    /**
     * UserRepositoryMongo constructor.
     *
     * Initializes the repository with a DocumentManager.
     * If $dm is null, MongoDB is considered disabled and repository operations are skipped.
     *
     * @param DocumentManager|null $dm The MongoDB document manager
     */
    public function __construct(?DocumentManager $dm = null)
    {
        if ($dm === null) {
            // Mongo disabled, skip initialization
            return;
        }

        $unitOfWork = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(UserDocument::class);

        parent::__construct($dm, $unitOfWork, $classMetadata);

        $this->dm = $dm;
    }

    /**
     * Adds a UserDocument to the database.
     *
     * @param UserDocument $entity The user document to add
     * @param bool $flush Whether to flush changes immediately
     */
    public function add(UserDocument $entity, bool $flush = false): void
    {
        if ($this->dm === null) {
            // Mongo disabled, ignore
            return;
        }

        $this->dm->persist($entity);
        if ($flush) {
            $this->dm->flush();
        }
    }

    /**
     * Removes a UserDocument from the database.
     *
     * @param UserDocument $entity The user document to remove
     * @param bool $flush Whether to flush changes immediately
     */
    public function remove(UserDocument $entity, bool $flush = false): void
    {
        if ($this->dm === null) {
            return;
        }

        $this->dm->remove($entity);
        if ($flush) {
            $this->dm->flush();
        }
    }

    /**
     * Upgrades (rehashes) the user's password automatically over time.
     *
     * @param PasswordAuthenticatedUserInterface $user The user whose password will be upgraded
     * @param string $newHashedPassword The new hashed password
     *
     * @throws UnsupportedUserException If the provided user is not an instance of UserDocument
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof UserDocument) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->add($user, true);
    }

    /**
     * Finds a UserDocument by email.
     *
     * @param string $email The email to search for
     * @return UserDocument|null The matching user document or null if not found
     */
    public function findOneByEmail(string $email): ?UserDocument
    {
        return $this->createQueryBuilder()
            ->field('email')->equals($email)
            ->getQuery()
            ->getSingleResult();
    }
}