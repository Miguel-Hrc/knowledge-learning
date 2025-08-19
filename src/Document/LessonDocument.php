<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use App\Document\CourseDocument;
use App\Document\UserDocument;

#[MongoDB\Document]
class LessonDocument
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $title = null;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $videoName = null;

    /**
     * @var File|null The uploaded video file (not persisted)
     */
    private ?File $videoFile = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $content = null;

    #[MongoDB\Field(type: 'float')]
    private ?float $price = null;

    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[MongoDB\ReferenceOne(targetDocument: CourseDocument::class, inversedBy: 'lessons')]
    private ?CourseDocument $course = null;

    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, nullable: true, storeAs: 'id')]
    private ?UserDocument $createdBy = null;

    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, nullable: true, storeAs: 'id')]
    private ?UserDocument $updatedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get the lesson ID.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the lesson title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the lesson title.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the video file name stored in the database.
     */
    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    /**
     * Set the video file name.
     */
    public function setVideoName(?string $videoName): static
    {
        $this->videoName = $videoName;
        return $this;
    }

    /**
     * Get the uploaded video file (not persisted in database).
     */
    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    /**
     * Set the uploaded video file (not persisted in database).
     */
    public function setVideoFile(?File $videoFile): void
    {
        $this->videoFile = $videoFile;
    }

    /**
     * Get the lesson content.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the lesson content.
     */
    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the lesson price.
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the lesson price.
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get the creation date of the lesson.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date of the lesson.
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
        return $this;
    }

    /**
     * Get the last update date of the lesson.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update date of the lesson.
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
        return $this;
    }

    /**
     * Get the associated course.
     */
    public function getCourse(): ?CourseDocument
    {
        return $this->course;
    }

    /**
     * Set the associated course.
     */
    public function setCourse(?CourseDocument $course): static
    {
        $this->course = $course;
        return $this;
    }

    /**
     * Get the user who created this lesson.
     */
    public function getCreatedBy(): ?UserDocument
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created this lesson.
     * Automatically adds this lesson to the user's created lessons collection.
     */
    public function setCreatedBy(?UserDocument $user): static
    {
        $this->createdBy = $user;
        $user?->addCreateLesson($this); 
        return $this;
    }

    /**
     * Get the user who last updated this lesson.
     */
    public function getUpdatedBy(): ?UserDocument
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated this lesson.
     * Automatically adds this lesson to the user's updated lessons collection.
     */
    public function setUpdatedBy(?UserDocument $user): static
    {
        $this->updatedBy = $user;
        $user?->addUpdateLesson($this); 
        return $this;
    }
}