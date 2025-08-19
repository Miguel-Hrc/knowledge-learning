<?php

namespace App\EventListener;

use App\Entity\Certification;
use App\Entity\User;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Doctrine listener for the Certification entity.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a Certification is persisted or updated.
 */
class CertificationListener
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage The Symfony token storage service to get the current user.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the Certification entity is persisted.
     *
     * @param Certification $certification The Certification entity being persisted.
     * @param PrePersistEventArgs $args The Doctrine pre-persist event arguments.
     */
    public function prePersist(Certification $certification, PrePersistEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $certification->setCreatedBy($user);
            $certification->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the entity is updated.
     * Also recomputes the change set so that Doctrine recognizes the updated field.
     *
     * @param Certification $certification The Certification entity being updated.
     * @param PreUpdateEventArgs $args The Doctrine pre-update event arguments.
     */
    public function preUpdate(Certification $certification, PreUpdateEventArgs $args): void
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $certification->setUpdatedBy($user);

            $em = $args->getObjectManager();
            $classMetadata = $em->getClassMetadata(Certification::class);
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $certification);
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