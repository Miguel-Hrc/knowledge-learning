<?php

namespace App\EventListener;

use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine ORM listener for the Theme entity.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Theme entity
 * is persisted or updated in the database.
 */
class ThemeListener
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage Symfony service to retrieve the currently authenticated user.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for Theme entity.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Theme entity is persisted.
     *
     * @param Theme $theme The Theme entity being persisted.
     * @param PrePersistEventArgs $args The Doctrine ORM pre-persist event arguments.
     */
    public function prePersist(Theme $theme, PrePersistEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $theme->setCreatedBy($user);
            $theme->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for Theme entity.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the entity is updated.
     * Also recomputes the entity change set so that Doctrine recognizes the updated field.
     *
     * @param Theme $theme The Theme entity being updated.
     * @param PreUpdateEventArgs $args The Doctrine ORM pre-update event arguments.
     */
    public function preUpdate(Theme $theme, PreUpdateEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $theme->setUpdatedBy($user);

            $em = $args->getObjectManager();
            $classMetadata = $em->getClassMetadata(Theme::class);
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $theme);
        }
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User|null Returns the User entity if a user is logged in, null otherwise.
     */
    private function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof User) {
            return $token->getUser();
        }
        return null;
    }
}