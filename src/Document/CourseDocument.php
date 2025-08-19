<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Represents a course within the system.
 *
 * @MongoDB\Document
 */
#[MongoDB\Document]
class CourseDocument
{
    /**
     * The unique identifier of the course.
     *
     * @var string|null
     */
    #[MongoDB\Id]
    private ?string $id = null;

    /**
     * The title of the course.
     *
     * @var string|null
     */
    #[MongoDB\Field(type: 'string')]
    private ?string $title = null;

    /**
     * The price of the course.
     *
     * @var float|null
     */
    #[MongoDB\Field(type: 'float')]
    private ?float $price = null;

    /**
     * The date and time when the course was created.
     *
     * @var \DateTimeImmutable|null
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The date and time when the course was last updated.
     *
     * @var \DateTimeImmutable|null
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The theme associated with this course.
     *
     * @var ThemeDocument|null
     */
    #[MongoDB\ReferenceOne(targetDocument: ThemeDocument::class, nullable: true)]
    private ?ThemeDocument $theme = null;

    /**
     * The user who created this course.
     *
     * @var UserDocument|null
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, nullable: true, storeAs: 'id')]
    private ?UserDocument $createdBy = null;

    /**
     * The user who last updated this course.
     *
     * @var UserDocument|null
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, nullable: true, storeAs: 'id')]
    private ?UserDocument $updatedBy = null;

    /**
     * The lessons that belong to this course.
     *
     * @var Collection<int, LessonDocument>
     */
    #[MongoDB\ReferenceMany(targetDocument: LessonDocument::class, mappedBy: 'course')]
    private Collection $lessons;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->lessons = new ArrayCollection();
    }

    /**
     * Gets the ID of the course.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Gets the title of the course.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title of the course.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gets the price of the course.
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Sets the price of the course.
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Gets the creation date of the course.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date of the course.
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
        return $this;
    }

    /**
     * Gets the last update date of the course.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Sets the last update date of the course.
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
        return $this;
    }

    /**
     * Gets the theme associated with the course.
     */
    public function getTheme(): ?ThemeDocument
    {
        return $this->theme;
    }

    /**
     * Sets the theme for the course.
     */
    public function setTheme(?ThemeDocument $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Gets the user who created the course.
     */
    public function getCreatedBy(): ?UserDocument
    {
        return $this->createdBy;
    }

    /**
     * Sets the user who created the course.
     */
    public function setCreatedBy(?UserDocument $user): static
    {
        $this->createdBy = $user;
        $user?->addCreateCourse($this);
        return $this;
    }

    /**
     * Gets the user who last updated the course.
     */
    public function getUpdatedBy(): ?UserDocument
    {
        return $this->updatedBy;
    }

    /**
     * Sets the user who last updated the course.
     */
    public function setUpdatedBy(?UserDocument $user): static
    {
        $this->updatedBy = $user;
        $user?->addUpdateCourse($this);
        return $this;
    }

    /**
     * Gets all lessons of the course.
     *
     * @return Collection<int, LessonDocument>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    /**
     * Adds a lesson to the course.
     */
    public function addLesson(LessonDocument $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
        }
        return $this;
    }

    /**
     * Removes a lesson from the course.
     */
    public function removeLesson(LessonDocument $lesson): static
    {
        $this->lessons->removeElement($lesson);
        return $this;
    }
}