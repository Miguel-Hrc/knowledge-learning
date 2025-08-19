<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use App\Document\ThemeDocument;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class CertificationDocument
 *
 * Represents a certification obtained by a user for a specific theme.
 *
 * @package App\Document
 */
#[MongoDB\Document]
#[MongoDB\UniqueIndex(keys: ['user.$id' => 1, 'theme.$id' => 1])]
class CertificationDocument
{
    /**
     * @var string|null The unique identifier of the certification document.
     */
    #[MongoDB\Id]
    private ?string $id = null;

    /**
     * @var bool Indicates whether the certification has been obtained.
     */
    #[MongoDB\Field(type: 'bool')]
    private bool $isObtained = false;

    /**
     * @var \DateTimeImmutable|null The date when the certification was obtained.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $dateObtention = null;

    /**
     * @var \DateTimeImmutable|null The date when this document was created.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null The date when this document was last updated.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var UserDocument|null Reference to the user who obtained the certification.
     */
    #[MongoDB\ReferenceOne( targetDocument: UserDocument::class)]
    private ?UserDocument $user = null;

    /**
     * @var ThemeDocument|null Reference to the theme of the certification.
     */
    #[MongoDB\ReferenceOne( targetDocument: ThemeDocument::class)]
    private ?ThemeDocument $theme = null;

    /**
     * @var int|null ID of the user who created this document.
     */
    #[MongoDB\Field(type: 'int', nullable: true)]
    private ?int $createdById = null;

    /**
     * @var int|null ID of the user who last updated this document.
     */
    #[MongoDB\Field(type: 'int', nullable: true)]
    private ?int $updatedById = null;

    /**
     * CertificationDocument constructor.
     *
     * Initializes the createdAt, updatedAt, and dateObtention fields with the current date,
     * and sets isObtained to false.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->dateObtention = new \DateTimeImmutable();
        $this->isObtained = false;
    }

    /**
     * Get the document ID.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Check if the certification has been obtained.
     *
     * @return bool
     */
    public function isObtained(): bool
    {
        return $this->isObtained;
    }

    /**
     * Set the obtained status.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsObtained(bool $value): self
    {
        $this->isObtained = $value;
        return $this;
    }

    /**
     * Get the date of obtaining the certification.
     *
     * @return \DateTimeImmutable|null
     */
    public function getDateObtention(): ?\DateTimeImmutable
    {
        return $this->dateObtention;
    }

    /**
     * Set the date of obtaining the certification.
     *
     * @param \DateTimeImmutable $date
     * @return $this
     */
    public function setDateObtention(\DateTimeImmutable $date): self
    {
        $this->dateObtention = $date;
        return $this;
    }

    /**
     * Get the creation date of this document.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date.
     *
     * @param \DateTimeInterface $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
        return $this;
    }

    /**
     * Get the last updated date of this document.
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last updated date.
     *
     * @param \DateTimeInterface $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
        return $this;
    }

    /**
     * Get the user associated with this certification.
     *
     * @return UserDocument|null
     */
    public function getUser(): ?UserDocument
    {
        return $this->user;
    }

    /**
     * Set the user associated with this certification.
     *
     * @param UserDocument|null $user
     */
    public function setUser(?UserDocument $user): void
    {
        $this->user = $user;
    }

    /**
     * Get the theme associated with this certification.
     *
     * @return ThemeDocument|null
     */
    public function getTheme(): ?ThemeDocument
    {
        return $this->theme;
    }

    /**
     * Set the theme associated with this certification.
     *
     * @param ThemeDocument|null $theme
     */
    public function setTheme(?ThemeDocument $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Get the ID of the user who created this document.
     *
     * @return int|null
     */
    public function getCreatedById(): ?int
    {
        return $this->createdById;
    }

    /**
     * Set the ID of the user who created this document.
     *
     * @param int|null $id
     * @return $this
     */
    public function setCreatedById(?int $id): self
    {
        $this->createdById = $id;
        return $this;
    }

    /**
     * Get the ID of the user who last updated this document.
     *
     * @return int|null
     */
    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    /**
     * Set the ID of the user who last updated this document.
     *
     * @param int|null $id
     * @return $this
     */
    public function setUpdatedById(?int $id): self
    {
        $this->updatedById = $id;
        return $this;
    }
}