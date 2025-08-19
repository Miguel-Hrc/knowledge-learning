<?php

namespace App\Entity;

use App\Repository\ResetPasswordRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * Represents a password reset request for a user.
 * 
 * Implements ResetPasswordRequestInterface from SymfonyCasts ResetPassword bundle.
 */
#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    /**
     * The unique identifier of the reset password request.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * The user associated with this password reset request.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    /**
     * Constructor.
     *
     * @param object $user The user requesting the password reset
     * @param \DateTimeInterface $expiresAt The expiration date of the reset request
     * @param string $selector The unique selector for this reset request
     * @param string $hashedToken The hashed token for validation
     */
    public function __construct(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * Gets the unique identifier of the reset password request.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the user associated with this password reset request.
     *
     * @return object The user entity
     */
    public function getUser(): object
    {
        return $this->user;
    }
}