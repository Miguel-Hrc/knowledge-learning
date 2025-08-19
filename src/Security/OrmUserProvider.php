<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * OrmUserProvider handles user loading and password upgrades for users stored in a SQL database using Doctrine ORM.
 *
 * Implements Symfony's UserProviderInterface and PasswordUpgraderInterface.
 */
class OrmUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private ?EntityManagerInterface $em;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface|null $em The Doctrine entity manager
     */
    public function __construct(?EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Loads a user by their unique identifier (email).
     *
     * @param string $identifier The user identifier (email)
     *
     * @return UserInterface The loaded user
     *
     * @throws UserNotFoundException If the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
        }

        return $user;
    }

    /**
     * Loads a user by their username.
     *
     * @param string $username The username
     *
     * @return UserInterface The loaded user
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Refreshes the user object by reloading it from the database.
     *
     * @param UserInterface $user The user to refresh
     *
     * @return UserInterface The refreshed user
     *
     * @throws UnsupportedUserException If the user class is invalid
     * @throws UserNotFoundException If the user no longer exists
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        $reloadedUser = $this->em->getRepository(User::class)->find($user->getId());

        if (!$reloadedUser) {
            throw new UserNotFoundException('User not found during refresh.');
        }

        return $reloadedUser;
    }

    /**
     * Checks if this provider supports the given user class.
     *
     * @param string $class The class name to check
     *
     * @return bool True if supported, false otherwise
     */
    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    /**
     * Upgrades the user's password hash.
     *
     * @param PasswordAuthenticatedUserInterface $user The user whose password should be upgraded
     * @param string $newHashedPassword The new hashed password
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            return;
        }

        $user->setPassword($newHashedPassword);
        $this->em->persist($user);
        $this->em->flush();
    }
}