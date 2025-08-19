<?php

namespace App\EventListener;

use App\Document\Course;
use App\Document\User;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine MongoDB ODM listener for the Course document.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Course document
 * is persisted or updated in the MongoDB database.
 */
class CourseListenerMongo
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage Symfony service to retrieve the current authenticated user token.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for Course document.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Course document is persisted.
     *
     * @param LifecycleEventArgs $args The Doctrine MongoDB ODM lifecycle event arguments.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $course = $args->getDocument();
        if (!$course instanceof Course) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $course->setCreatedBy($user);
            $course->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for Course document.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the document is updated.
     * Also recomputes the document change set so that Doctrine recognizes the updated field.
     *
     * @param PreUpdateEventArgs $args The Doctrine MongoDB ODM pre-update event arguments.
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $course = $args->getDocument();
        if (!$course instanceof Course) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $course->setUpdatedBy($user);

            $dm = $args->getDocumentManager();
            $uow = $dm->getUnitOfWork();
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(Course::class), $course);
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