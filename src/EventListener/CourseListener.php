<?php

namespace App\EventListener;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine ORM listener for the Course entity.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Course entity
 * is persisted or updated in the database.
 */
class CourseListener
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage Symfony service to retrieve the current authenticated user token.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for Course entity.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Course entity is persisted.
     *
     * @param Course $course The Course entity being persisted.
     * @param PrePersistEventArgs $args The Doctrine pre-persist event arguments.
     */
    public function prePersist(Course $course, PrePersistEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $course->setCreatedBy($user);
            $course->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for Course entity.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the entity is updated.
     * Also recomputes the entity change set so that Doctrine recognizes the updated field.
     *
     * @param Course $course The Course entity being updated.
     * @param PreUpdateEventArgs $args The Doctrine pre-update event arguments.
     */
    public function preUpdate(Course $course, PreUpdateEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $course->setUpdatedBy($user);

            $em = $args->getObjectManager();
            $classMetadata = $em->getClassMetadata(Course::class);
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $course);
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