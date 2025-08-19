<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * Repository for managing ResetPasswordRequest entities.
 *
 * This repository provides methods to create, persist, and remove
 * ResetPasswordRequest entities used for password reset functionality.
 *
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 *
 * @method ResetPasswordRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResetPasswordRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResetPasswordRequest[]    findAll()
 * @method ResetPasswordRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    /**
     * Constructor.
     *
     * @param ManagerRegistry|null $registry The Doctrine manager registry
     */
    public function __construct(?ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * Adds a ResetPasswordRequest entity to the database.
     *
     * @param ResetPasswordRequest $entity The entity to persist
     * @param bool $flush Whether to immediately flush changes to the database
     */
    public function add(ResetPasswordRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Removes a ResetPasswordRequest entity from the database.
     *
     * @param ResetPasswordRequest $entity The entity to remove
     * @param bool $flush Whether to immediately flush changes to the database
     */
    public function remove(ResetPasswordRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Creates a new ResetPasswordRequest instance.
     *
     * @param object $user The user requesting the password reset
     * @param \DateTimeInterface $expiresAt Expiration datetime for the request
     * @param string $selector The selector string
     * @param string $hashedToken The hashed token string
     *
     * @return ResetPasswordRequestInterface The new ResetPasswordRequest instance
     */
    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }
}