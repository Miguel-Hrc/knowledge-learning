<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\EventListener\CourseListener;

/**
 * Represents a course, which belongs to a theme and contains multiple lessons.
 */
#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    /**
     * The unique identifier of the course.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The title of the course.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * The price of the course.
     */
    #[ORM\Column]
    private ?float $price = null;

    /**
     * The creation date of the course.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The last update date of the course.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The theme to which the course belongs.
     */
    #[ORM\ManyToOne(inversedBy: 'courses')]
    private ?Theme $theme = null;

    /**
     * The user who created the course.
     */
    #[ORM\ManyToOne(inversedBy: 'createCourses')]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /**
     * The user who last updated the course.
     */
    #[ORM\ManyToOne(inversedBy: 'updateCourses')]
    #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    /**
     * The collection of lessons belonging to this course.
     *
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'course')]
    private Collection $lessons;

    public function __construct()
    {   
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->lessons = new ArrayCollection();
    }

    /**
     * Get the unique identifier of the course.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the title of the course.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title of the course.
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
     * Get the price of the course.
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the price of the course.
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
     * Get the creation date of the course.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date of the course.
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
     * Get the last update date of the course.
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update date of the course.
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
     * Get the theme associated with this course.
     *
     * @return Theme|null
     */
    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    /**
     * Set the theme associated with this course.
     *
     * @param Theme|null $theme
     * @return $this
     */
    public function setTheme(?Theme $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get the user who created this course.
     *
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created this course.
     *
     * @param User|null $user
     * @return $this
     */
    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        $user?->addCreateCourse($this); 
        return $this;
    }

    /**
     * Get the user who last updated this course.
     *
     * @return User|null
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated this course.
     *
     * @param User|null $user
     * @return $this
     */
    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        $user?->addUpdateCourse($this); 
        return $this;
    }

    /**
     * Get the lessons belonging to this course.
     *
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    /**
     * Add a lesson to this course.
     *
     * @param Lesson $lesson
     * @return $this
     */
    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setCursus($this);
        }
        return $this;
    }

    /**
     * Remove a lesson from this course.
     *
     * @param Lesson $lesson
     * @return $this
     */
    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getCursus() === $this) {
                $lesson->setCursus(null);
            }
        }
        return $this;
    }
}