<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Represents a command (order) in the system.
 *
 * @MongoDB\Document
 */
#[MongoDB\Document]
class CommandDocument
{
    /**
     * The unique identifier of the command.
     *
     * @var string|null
     */
    #[MongoDB\Id]
    private ?string $id = null;

    /**
     * The date and time when the command was created.
     *
     * @var \DateTimeImmutable|null
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The date and time when the command was last updated.
     *
     * @var \DateTimeImmutable|null
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The ID of the user who created the command.
     *
     * @var int|null
     */
    #[MongoDB\Field(type: 'int', nullable: true)]
    private ?int $createdById = null;

    /**
     * The ID of the user who last updated the command.
     *
     * @var int|null
     */
    #[MongoDB\Field(type: 'int', nullable: true)]
    private ?int $updatedById = null;

    /**
     * The ID of the user who owns this command.
     *
     * @var int|null
     */
    #[MongoDB\Field(type: 'int')]
    private ?int $userId = null;

    /**
     * The payments associated with this command.
     *
     * @var Collection<int, PaymentDocument>
     */
    #[MongoDB\EmbedMany(targetDocument: PaymentDocument::class)]
    private Collection $payments;

    /**
     * The items included in this command.
     *
     * @var Collection<int, CommandItemDocument>
     */
    #[MongoDB\EmbedMany(targetDocument: CommandItemDocument::class)]
    private Collection $commandItems;

    /**
     * CommandDocument constructor.
     * Initializes creation and update timestamps, and sets up empty collections.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->payments = new ArrayCollection();
        $this->commandItems = new ArrayCollection();
    }

    /**
     * Gets the unique identifier of the command.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Gets the creation timestamp.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation timestamp.
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
        return $this;
    }

    /**
     * Gets the last update timestamp.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Sets the last update timestamp.
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
        return $this;
    }

    /**
     * Gets the ID of the user who created this command.
     */
    public function getCreatedById(): ?int
    {
        return $this->createdById;
    }

    /**
     * Sets the ID of the user who created this command.
     */
    public function setCreatedById(?int $createdById): static
    {
        $this->createdById = $createdById;
        return $this;
    }

    /**
     * Gets the ID of the user who last updated this command.
     */
    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    /**
     * Sets the ID of the user who last updated this command.
     */
    public function setUpdatedById(?int $updatedById): static
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    /**
     * Gets the ID of the user who owns this command.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Sets the ID of the user who owns this command.
     */
    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Gets all payments associated with this command.
     *
     * @return Collection<int, PaymentDocument>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * Adds a payment to this command.
     */
    public function addPayment(PaymentDocument $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
        }
        return $this;
    }

    /**
     * Removes a payment from this command.
     */
    public function removePayment(PaymentDocument $payment): static
    {
        $this->payments->removeElement($payment);
        return $this;
    }

    /**
     * Gets all items included in this command.
     *
     * @return Collection<int, CommandItemDocument>
     */
    public function getCommandItems(): Collection
    {
        return $this->commandItems;
    }

    /**
     * Adds an item to this command.
     */
    public function addCommandItem(CommandItemDocument $commandItem): static
    {
        if (!$this->commandItems->contains($commandItem)) {
            $this->commandItems->add($commandItem);
        }
        return $this;
    }

    /**
     * Removes an item from this command.
     */
    public function removeCommandItem(CommandItemDocument $commandItem): static
    {
        $this->commandItems->removeElement($commandItem);
        return $this;
    }
}