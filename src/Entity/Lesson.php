<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use Doctrine\ORM\Mapping\EntityListeners;
use App\EventListener\LessonListener;

/**
 * Class Lesson
 *
 * Represents a lesson within a course.
 *
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    /**
     * Primary key of the Lesson.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Title of the lesson.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Name of the uploaded video file.
     *
     * @var string|null
     */
    #[ORM\Column(length: 255 , nullable: true)]
    private ?string $videoName  = null;

    /**
     * Uploaded video file.
     *
     * @var File|null
     */
    #[Assert\File(
        maxSize: '50M',
        mimeTypes: [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska'
        ],
        mimeTypesMessage: 'Please upload a video in MP4, MOV, AVI or MKV format.'
    )]
    private ?File $videoFile = null;

    /**
     * Content/description of the lesson.
     *
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    /**
     * Price of the lesson.
     *
     * @var float|null
     */
    #[ORM\Column]
    private ?float $price = null;

    /**
     * Date and time when the lesson was created.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date and time when the lesson was last updated.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The course this lesson belongs to.
     *
     * @var Course|null
     */
    #[ORM\ManyToOne(inversedBy: 'lessons')]
    private ?Course $course = null;

    /**
     * User who created this lesson.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'createLessons')]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /**
     * User who last updated this lesson.
     *
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'updateLessons')]
    #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    /**
     * Checks if a given user is certified for this lesson.
     *
     * @param User $user
     * @return bool
     */
    public function isCertifiedBy(User $user): bool
    {
        foreach ($this->certifications as $certification) {
            if ($certification->getUser() === $user) {
                return true;
            }
        }
        return false;
    }

    /**
     * Lesson constructor.
     * Initializes creation and update timestamps.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get the lesson ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the lesson title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the lesson title.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the name of the video file.
     *
     * @return string|null
     */
    public function getVideoName(): ?string
    {
        return $this->videoName;
    }

    /**
     * Set the name of the video file.
     *
     * @param string|null $videoName
     * @return $this
     */
    public function setVideoName(?string $videoName): self
    {
        $this->videoName = $videoName;
        return $this;
    }

    /**
     * Get the uploaded video file.
     *
     * @return File|null
     */
    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    /**
     * Set the uploaded video file.
     *
     * @param File|null $videoFile
     */
    public function setVideoFile(?File $videoFile): void
    {
        $this->videoFile = $videoFile;
    }

    /**
     * Get the lesson content.
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the lesson content.
     *
     * @param string $content
     * @return $this
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get the lesson price.
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the lesson price.
     *
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get the creation timestamp.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp.
     *
     * @param \DateTimeImmutable $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the last update timestamp.
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update timestamp.
     *
     * @param \DateTimeImmutable $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get the course this lesson belongs to.
     *
     * @return Course|null
     */
    public function getCourse(): ?Course
    {
        return $this->course;
    }

    /**
     * Set the course this lesson belongs to.
     *
     * @param Course|null $course
     * @return $this
     */
    public function setCourse(?Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get the user who created this lesson.
     *
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created this lesson.
     *
     * @param User|null $user
     * @return $this
     */
    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        $user?->addCreateLesson($this);
        return $this;
    }

    /**
     * Get the user who last updated this lesson.
     *
     * @return User|null
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated this lesson.
     *
     * @param User|null $user
     * @return $this
     */
    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        $user?->addUpdateLesson($this);
        return $this;
    }
}