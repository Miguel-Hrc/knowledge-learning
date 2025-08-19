<?php

namespace App\EventListener;

use App\Document\Theme; 
use App\Document\User;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine MongoDB ODM listener for the Theme document.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Theme document
 * is persisted or updated in the database.
 */
class ThemeListenerMongo
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage Symfony service to retrieve the currently authenticated user.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for Theme document.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Theme document is persisted.
     *
     * @param LifecycleEventArgs $args The Doctrine MongoDB lifecycle event arguments.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $theme = $args->getDocument();
        if (!$theme instanceof Theme) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $theme->setCreatedBy($user);
            $theme->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for Theme document.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the document is updated.
     * Also recomputes the document change set so that Doctrine ODM recognizes the updated field.
     *
     * @param PreUpdateEventArgs $args The Doctrine MongoDB pre-update event arguments.
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $theme = $args->getDocument();
        if (!$theme instanceof Theme) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $theme->setUpdatedBy($user);

            $dm = $args->getDocumentManager();
            $uow = $dm->getUnitOfWork();
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(Theme::class), $theme);
        }
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User|null Returns the User document if a user is logged in, null otherwise.
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