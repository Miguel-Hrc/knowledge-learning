<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    /**
     * The unique identifier of the command.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The date and time when the command was created.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The date and time when the command was last updated.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The user who created this command.
     */
    #[ORM\ManyToOne(inversedBy: 'commands')]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /**
     * A collection of payments associated with this command.
     *
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'command')]
    private Collection $payments;

    /**
     * The user who last updated this command.
     */
    #[ORM\ManyToOne(inversedBy: 'updateCommands')]
    #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    /**
     * The user who owns or placed this command.
     */
    #[ORM\ManyToOne(inversedBy: 'makeCommands')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * A collection of command items associated with this command.
     *
     * @var Collection<int, CommandItem>
     */
    #[ORM\OneToMany(mappedBy: 'command', targetEntity: CommandItem::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private Collection $commandItems;

    /**
     * Constructor initializes dates and collections.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->payments = new ArrayCollection();
        $this->commandItems = new ArrayCollection();
    }

    /**
     * Get the command ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the creation date of the command.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date of the command.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get the last update date of the command.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update date of the command.
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get the user who created this command.
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created this command.
     */
    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        $user?->addCreateComamnd($this); 
        return $this;
    }

    /**
     * Get all payments associated with this command.
     *
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * Add a payment to this command.
     */
    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setCommand($this);
        }
        return $this;
    }

    /**
     * Remove a payment from this command.
     */
    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getCommand() === $this) {
                $payment->setCommand(null);
            }
        }
        return $this;
    }

    /**
     * Get the user who last updated this command.
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated this command.
     */
    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        $user?->addUpdateCommand($this); 
        return $this;
    }

    /**
     * Get the owner of this command.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the owner of this command.
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get all items associated with this command.
     *
     * @return Collection<int, CommandItem>
     */
    public function getCommandItems(): Collection
    {
        return $this->commandItems;
    }

    /**
     * Add an item to this command.
     */
    public function addCommandItem(CommandItem $commandItem): static
    {
        if (!$this->commandItems->contains($commandItem)) {
            $this->commandItems->add($commandItem);
            $commandItem->setCommande($this);
        }
        return $this;
    }

    /**
     * Remove an item from this command.
     */
    public function removeCommandItem(CommandItem $commandItem): static
    {
        if ($this->commandItems->removeElement($commandItem)) {
            if ($commandItem->getCommande() === $this) {
                $commandItem->setCommande(null);
            }
        }
        return $this;
    }
}