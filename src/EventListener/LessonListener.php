<?php

namespace App\EventListener;

use App\Entity\Lesson;
use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine ORM listener for the Lesson entity.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Lesson entity
 * is persisted or updated in the database.
 */
class LessonListener
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface|null $tokenStorage Symfony service to retrieve the current authenticated user token.
     */
    public function __construct(private ?TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for Lesson entity.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Lesson entity is persisted.
     *
     * @param Lesson $lesson The Lesson entity being persisted.
     * @param PrePersistEventArgs $args The Doctrine ORM pre-persist event arguments.
     */
    public function prePersist(Lesson $lesson, PrePersistEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $lesson->setCreatedBy($user);
            $lesson->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for Lesson entity.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the entity is updated.
     * Also recomputes the entity change set so that Doctrine recognizes the updated field.
     *
     * @param Lesson $lesson The Lesson entity being updated.
     * @param PreUpdateEventArgs $args The Doctrine ORM pre-update event arguments.
     */
    public function preUpdate(Lesson $lesson, PreUpdateEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $lesson->setUpdatedBy($user);

            $em = $args->getObjectManager();
            $classMetadata = $em->getClassMetadata(Lesson::class);
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $lesson);
        }
    }

    /**
     * Get the currently authenticated user.
     *
     * @return User|null Returns the User entity if a user is logged in, null otherwise.
     */
    private function getUser(): ?User
    {
        $token = $this->tokenStorage?->getToken();
        if ($token && $token->getUser() instanceof User) {
            return $token->getUser();
        }
        return null;
    }
}