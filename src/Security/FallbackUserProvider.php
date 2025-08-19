<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Entity\User as OrmUser;
use App\Document\UserDocument as MongoUser;

/**
 * FallbackUserProvider is a user provider that can handle both SQL/ORM and MongoDB users.
 * 
 * Depending on the configuration ($useMongo), it will load and refresh users from the
 * appropriate storage system.
 */
class FallbackUserProvider implements UserProviderInterface
{
    private ?EntityManagerInterface $em;
    private ?DocumentManager $dm;
    private bool $useMongo;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface|null $em       Entity Manager for ORM users
     * @param DocumentManager|null        $dm       Document Manager for MongoDB users
     * @param bool                        $useMongo Whether to use MongoDB (true) or ORM (false)
     */
    public function __construct(?EntityManagerInterface $em, ?DocumentManager $dm, bool $useMongo)
    {
        $this->em = $em;
        $this->dm = $dm;
        $this->useMongo = $useMongo;
    }

    /**
     * Loads a user by their identifier (email in this case).
     *
     * @param string $identifier The unique user identifier (email)
     *
     * @return UserInterface The loaded user
     *
     * @throws UserNotFoundException If the user cannot be found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {   
        if ($this->useMongo) {
            $user = $this->dm?->getRepository(MongoUser::class)->findOneBy(['email' => $identifier]);
        } else {
            $user = $this->em?->getRepository(OrmUser::class)->findOneBy(['email' => $identifier]);
        }

        if (!$user) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    /**
     * Refreshes the user from the database.
     *
     * @param UserInterface $user The user to refresh
     *
     * @return UserInterface The refreshed user
     *
     * @throws UserNotFoundException If the user cannot be found or the type is unsupported
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof MongoUser) {
            $refreshedUser = $this->dm?->getRepository(MongoUser::class)->find($user->getId());
        } elseif ($user instanceof OrmUser) {
            $refreshedUser = $this->em?->getRepository(OrmUser::class)->find($user->getId());
        } else {
            throw new UserNotFoundException('User type not supported for refresh.');
        }

        if (!$refreshedUser) {
            throw new UserNotFoundException(sprintf('User with ID "%s" not found.', $user->getId()));
        }

        return $refreshedUser;
    }

    /**
     * Checks if this provider supports the given user class.
     *
     * @param string $class The fully-qualified class name
     *
     * @return bool True if the class is supported by this provider
     */
    public function supportsClass(string $class): bool
    {
        return $this->useMongo
            ? $class === MongoUser::class
            : $class === OrmUser::class;
    }
}