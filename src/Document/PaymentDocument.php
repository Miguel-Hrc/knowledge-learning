<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use App\Document\UserDocument;
use App\Document\CommandDocument;

/**
 * Class PaymentDocument
 *
 * Represents a payment associated with a command, including information about 
 * the amount, payment method, timestamps, and the users who created/updated it.
 */
#[MongoDB\Document]
class PaymentDocument
{
    /**
     * @var string|null The unique identifier of the payment.
     */
    #[MongoDB\Id]
    private ?string $id = null;

    /**
     * @var \DateTimeImmutable|null The date when the payment was made.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $date = null;

    /**
     * @var float|null The total sum of the payment.
     */
    #[MongoDB\Field(type: 'float')]
    private ?float $sum = null;

    /**
     * @var string|null The payment method used (e.g., 'credit card', 'paypal').
     */
    #[MongoDB\Field(type: 'string')]
    private ?string $means = null;

    /**
     * @var \DateTimeImmutable|null The timestamp when the payment record was created.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null The timestamp when the payment record was last updated.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var UserDocument|null The user who created this payment record.
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, inversedBy: 'payments', storeAs: 'id')]
    private ?UserDocument $createdBy = null;

    /**
     * @var UserDocument|null The user who last updated this payment record.
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, inversedBy: 'updatePayments', storeAs: 'id')]
    private ?UserDocument $updatedBy = null;

    /**
     * @var CommandDocument|null The command associated with this payment.
     */
    #[MongoDB\ReferenceOne(targetDocument: CommandDocument::class, inversedBy: 'payments')]
    private ?CommandDocument $command = null;

    /**
     * PaymentDocument constructor.
     *
     * Initializes creation and update timestamps to the current time.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get the unique identifier of the payment.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the date of the payment.
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Set the date of the payment.
     */
    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get the total sum of the payment.
     */
    public function getSum(): ?float
    {
        return $this->sum;
    }

    /**
     * Set the total sum of the payment.
     */
    public function setSum(float $sum): static
    {
        $this->sum = $sum;
        return $this;
    }

    /**
     * Get the payment method.
     */
    public function getMeans(): ?string
    {
        return $this->means;
    }

    /**
     * Set the payment method.
     */
    public function setMeans(string $means): static
    {
        $this->means = $means;
        return $this;
    }

    /**
     * Get the creation timestamp.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
        return $this;
    }

    /**
     * Get the last update timestamp.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update timestamp.
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
        return $this;
    }

    /**
     * Get the user who created this payment record.
     */
    public function getCreatedBy(): ?UserDocument
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created this payment record.
     */
    public function setCreatedBy(?UserDocument $user): static
    {
        $this->createdBy = $user;
        $user?->addCreatePayment($this);
        return $this;
    }

    /**
     * Get the user who last updated this payment record.
     */
    public function getUpdatedBy(): ?UserDocument
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated this payment record.
     */
    public function setUpdatedBy(?UserDocument $user): static
    {
        $this->updatedBy = $user;
        $user?->addUpdatePayment($this);
        return $this;
    }

    /**
     * Get the associated command.
     */
    public function getCommand(): ?CommandDocument
    {
        return $this->command;
    }

    /**
     * Set the associated command.
     */
    public function setCommand(?CommandDocument $command): static
    {
        $this->command = $command;
        return $this;
    }
}