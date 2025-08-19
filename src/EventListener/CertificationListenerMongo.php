<?php

namespace App\EventListener;

use App\Document\CertificationDocument;
use App\Document\UserDocument;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * MongoDB Doctrine listener for the CertificationDocument.
 *
 * This listener automatically sets the "createdBy" and "updatedBy" fields
 * based on the currently authenticated user when a CertificationDocument
 * is persisted or updated in MongoDB.
 */
class CertificationListenerMongo
{
    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage Symfony service to retrieve the current user token.
     */
    public function __construct(private TokenStorageInterface $tokenStorage) {}

    /**
     * Pre-persist lifecycle callback for MongoDB documents.
     *
     * Sets both createdBy and updatedBy fields to the currently authenticated user
     * before the CertificationDocument is persisted.
     *
     * @param LifecycleEventArgs $args The MongoDB lifecycle event arguments.
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $certification = $args->getDocument();
        if (!$certification instanceof CertificationDocument) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof UserDocument) {
            $certification->setCreatedBy($user);
            $certification->setUpdatedBy($user);
        }
    }

    /**
     * Pre-update lifecycle callback for MongoDB documents.
     *
     * Updates the "updatedBy" field to the currently authenticated user before the document is updated.
     * Also recomputes the document change set so that Doctrine recognizes the updated field.
     *
     * @param PreUpdateEventArgs $args The MongoDB pre-update event arguments.
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $certification = $args->getDocument();
        if (!$certification instanceof CertificationDocument) {
            return;
        }

        $user = $this->getUser();
        if ($user instanceof UserDocument) {
            $certification->setUpdatedBy($user);

            $dm = $args->getDocumentManager();
            $uow = $dm->getUnitOfWork();
            $uow->recomputeSingleDocumentChangeSet($dm->getClassMetadata(CertificationDocument::class), $certification);
        }
    }

    /**
     * Get the currently authenticated user.
     *
     * @return UserDocument|null Returns the UserDocument if a user is logged in, null otherwise.
     */
    private function getUser(): ?UserDocument
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof UserDocument) {
            return $token->getUser();
        }
        return null;
    }
}