<?php

namespace App\EventListener;

use App\Document\Lesson;
use App\Document\User;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine MongoDB ODM listener for the Lesson document.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Lesson document
 * is persisted or updated in MongoDB.
 */
class LessonListenerMongo
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage Symfony service to retrieve the currently authenticated user.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for Lesson document.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Lesson document is persisted.
     *
     * @param LifecycleEventArgs $args The Doctrine ODM lifecycle event arguments.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $lesson = $args->getDocument();
        if (!$lesson instanceof Lesson) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $lesson->setCreatedBy($user);
            $lesson->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for Lesson document.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the document is updated.
     * Also recomputes the document change set so that Doctrine recognizes the updated field.
     *
     * @param PreUpdateEventArgs $args The Doctrine ODM pre-update event arguments.
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $lesson = $args->getDocument();
        if (!$lesson instanceof Lesson) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $lesson->setUpdatedBy($user);

            $dm = $args->getDocumentManager();
            $uow = $dm->getUnitOfWork();
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(Lesson::class), $lesson);
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