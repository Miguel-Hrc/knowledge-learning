<?php

namespace App\Security;

use App\Document\UserDocument;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * MongoUserProvider handles loading, refreshing, and upgrading passwords
 * for users stored in MongoDB.
 *
 * Implements Symfony's UserProviderInterface and PasswordUpgraderInterface.
 */
class MongoUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private ?DocumentManager $dm;

    /**
     * Constructor.
     *
     * @param DocumentManager|null $dm The Doctrine MongoDB DocumentManager
     */
    public function __construct(?DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Loads a user by their unique identifier (email).
     *
     * @param string $identifier The user's email
     *
     * @return UserInterface The loaded user
     *
     * @throws UserNotFoundException If no user is found with the given email
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->dm->getRepository(UserDocument::class)
            ->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
        }

        return $user;
    }

    /**
     * Refreshes a user from the database.
     *
     * @param UserInterface $user The user to refresh
     *
     * @return UserInterface The refreshed user
     *
     * @throws \InvalidArgumentException If the provided user is not a UserDocument
     * @throws \RuntimeException If the user cannot be found in the database
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserDocument) {
            throw new \InvalidArgumentException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $reloadedUser = $this->dm->getRepository(UserDocument::class)->find($user->getId());

        if (!$reloadedUser) {
            throw new \RuntimeException('User not found');
        }

        return $reloadedUser;
    }

    /**
     * Checks whether this provider supports the given user class.
     *
     * @param string $class The user class
     *
     * @return bool True if the class is UserDocument or a subclass
     */
    public function supportsClass(string $class): bool
    {
        return $class === UserDocument::class || is_subclass_of($class, UserDocument::class);
    }

    /**
     * Upgrades the hashed password of a user.
     *
     * @param PasswordAuthenticatedUserInterface $user             The user to update
     * @param string                              $newHashedPassword The new hashed password
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof UserDocument) {
            return;
        }

        $user->setPassword($newHashedPassword);
        $this->dm->persist($user);
        $this->dm->flush();
    }
}