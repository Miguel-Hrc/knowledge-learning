<?php

namespace App\Repository;

use App\Document\ResetPasswordRequestDocument;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * MongoDB repository for managing ResetPasswordRequestDocument entities.
 *
 * This repository provides methods to persist, retrieve, and remove
 * ResetPasswordRequest documents used for password reset functionality.
 */
class ResetPasswordRequestRepositoryMongo extends DocumentRepository implements ResetPasswordRequestRepositoryInterface
{
    /**
     * Returns the unique identifier for a given user object.
     *
     * @param object $user The user object
     * @return string The unique user identifier
     */
    public function getUserIdentifier(object $user): string
    {
        return $user->getId();
    }

    /**
     * Persists a ResetPasswordRequest document to the database.
     *
     * @param ResetPasswordRequestInterface $resetPasswordRequest The reset password request to persist
     */
    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->dm->persist($resetPasswordRequest);
        $this->dm->flush();
    }

    /**
     * Finds a ResetPasswordRequest document by its selector.
     *
     * @param string $selector The selector string
     * @return ResetPasswordRequestInterface|null The found document or null if not found
     */
    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    /**
     * Removes all expired ResetPasswordRequest documents.
     *
     * @return int The number of deleted documents
     */
    public function removeExpiredResetPasswordRequests(): int
    {
        $qb = $this->createQueryBuilder()
            ->remove()
            ->field('expiresAt')->lt(new \DateTimeImmutable());

        $result = $qb->getQuery()->execute();

        return $result->getDeletedCount() ?? 0;
    }

    /**
     * Retrieves the most recent non-expired reset password request date for a user.
     *
     * @param object $user The user object
     * @return \DateTimeInterface|null The expiration date of the most recent request, or null if none exists
     */
    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        $userId = $this->getUserIdentifier($user);

        $qb = $this->createQueryBuilder()
            ->field('user')->equals($userId)
            ->field('expiresAt')->gt(new \DateTimeImmutable())
            ->sort('expiresAt', 'desc')
            ->limit(1);

        $result = $qb->getQuery()->getSingleResult();

        return $result ? $result->getExpiresAt() : null;
    }

    /**
     * Creates a new ResetPasswordRequest instance.
     *
     * @param object $user The user requesting the password reset
     * @param \DateTimeInterface $expiresAt Expiration datetime for the request
     * @param string $selector The selector string
     * @param string $hashedToken The hashed token string
     * @return ResetPasswordRequestInterface The new reset password request instance
     */
    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    /**
     * Removes a specific ResetPasswordRequest document from the database.
     *
     * @param ResetPasswordRequestInterface $resetPasswordRequest The reset password request to remove
     */
    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->dm->remove($resetPasswordRequest);
        $this->dm->flush();
    }
}