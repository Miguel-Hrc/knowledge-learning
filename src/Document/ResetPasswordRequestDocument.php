<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

/**
 * Class ResetPasswordRequestDocument
 *
 * Represents a password reset request associated with a specific user.
 * Implements the ResetPasswordRequestInterface from SymfonyCasts ResetPassword bundle.
 */
#[MongoDB\Document]
class ResetPasswordRequestDocument implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    /**
     * @var string The unique identifier of the reset password request.
     */
    #[MongoDB\Id]
    private string $id;

    /**
     * @var UserDocument The user associated with this password reset request.
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, storeAs: 'id')]
    private UserDocument $user;

    /**
     * ResetPasswordRequestDocument constructor.
     *
     * @param UserDocument $user The user requesting a password reset.
     * @param \DateTimeInterface $expiresAt The expiration date and time of this request.
     * @param string $selector The selector part of the reset token.
     * @param string $hashedToken The hashed token for verification.
     */
    public function __construct(UserDocument $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    /**
     * Get the unique identifier of the password reset request.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the user associated with this password reset request.
     *
     * @return UserDocument
     */
    public function getUser(): UserDocument
    {
        return $this->user;
    }
}