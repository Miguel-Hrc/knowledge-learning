<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\EntityListeners;
use App\EventListener\CertificationListener;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
class Certification
{
    /**
     * The unique identifier of the certification.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Indicates whether the certification has been obtained.
     */
    #[ORM\Column]
    private ?bool $isObtained = false;

    /**
     * The date when the certification was obtained.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateObtention = null;

    /**
     * The date and time when the certification was created.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    /**
     * The date and time when the certification was last updated.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The user associated with this certification.
     */
    #[ORM\ManyToOne(inversedBy: 'certifications')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * The theme associated with this certification.
     */
    #[ORM\ManyToOne(inversedBy: 'certificationTheme')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Theme $theme = null;

    /**
     * The user who created this certification record.
     */
    #[ORM\ManyToOne(inversedBy: 'createCertifications')]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /**
     * The user who last updated this certification record.
     */
    #[ORM\ManyToOne(inversedBy: 'updateCertifications')]
    #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    /**
     * Constructor initializes dates and default status.
     */
    public function __construct()
    {   
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->dateObtention = new \DateTimeImmutable();
        $this->isObtained = false;
    }

    /**
     * Get the unique identifier.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Check if the certification is obtained.
     */
    public function isObtained(): ?bool
    {
        return $this->isObtained;
    }

    /**
     * Set the obtained status of the certification.
     */
    public function setIsObtained(bool $isObtained): static
    {
        $this->isObtained = $isObtained;
        return $this;
    }

    /**
     * Get the creation date.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get the last update date.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update date.
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get the user associated with this certification.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the user associated with this certification.
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get the theme associated with this certification.
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * Set the theme associated with this certification.
     */
    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }
    
    /**
     * Get the user who created this certification.
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created this certification.
     */
    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        $user?->addCreateCertification($this); 
        return $this;
    }

    /**
     * Get the user who last updated this certification.
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated this certification.
     */
    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        $user?->addUpdateCertification($this); 
        return $this;
    }

    /**
     * Get the date when the certification was obtained.
     */
    public function getDateObtention(): ?\DateTimeImmutable
    {
        return $this->dateObtention;
    }

    /**
     * Set the date when the certification was obtained.
     */
    public function setDateObtention(\DateTimeImmutable $date): self
    {
        $this->dateObtention = $date;
        return $this;
    }
}