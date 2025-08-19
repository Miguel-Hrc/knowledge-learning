<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a payment linked to a command/order.
 */
#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    /**
     * The unique identifier of the payment.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The date when the payment was made.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    /**
     * The amount paid.
     */
    #[ORM\Column]
    private ?float $sum = null;

    /**
     * The payment method used (e.g., credit card, PayPal).
     */
    #[ORM\Column(length: 255)]
    private ?string $means = null;

    /**
     * The date when the payment entity was created.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The date when the payment entity was last updated.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The user who created the payment entry.
     */
    #[ORM\ManyToOne(inversedBy: 'createPayments')]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /**
     * The command/order associated with this payment.
     */
    #[ORM\ManyToOne(inversedBy: 'payments')]
    private ?Command $command = null;

    /**
     * The user who last updated the payment entry.
     */
    #[ORM\ManyToOne(inversedBy: 'updatePayments')]
    #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    /**
     * Gets the unique identifier of the payment.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the payment date.
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Sets the payment date.
     */
    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Gets the payment amount.
     */
    public function getSum(): ?float
    {
        return $this->sum;
    }

    /**
     * Sets the payment amount.
     */
    public function setSum(float $sum): static
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Gets the payment method.
     */
    public function getMeans(): ?string
    {
        return $this->means;
    }

    /**
     * Sets the payment method.
     */
    public function setMeans(string $means): static
    {
        $this->means = $means;

        return $this;
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
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the user who created the payment.
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Sets the user who created the payment.
     */
    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        $user?->addCreatePayment($this); 
        return $this;
    }

    /**
     * Gets the associated command/order.
     */
    public function getCommand(): ?Command
    {
        return $this->command;
    }

    /**
     * Sets the associated command/order.
     */
    public function setCommand(?Command $command): static
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Gets the user who last updated the payment.
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Sets the user who last updated the payment.
     */
    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        $user?->addUpdatePayment($this); 
        return $this;
    }
}