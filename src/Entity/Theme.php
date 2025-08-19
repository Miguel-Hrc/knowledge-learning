<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a Theme, which can contain multiple Courses and Certifications.
 */
#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    /**
     * The unique identifier of the theme.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The name of the theme.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * The datetime when the theme was created.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The datetime when the theme was last updated.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * The user who created the theme.
     */
    #[ORM\ManyToOne(inversedBy: 'createThemes')]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $createdBy = null;

    /**
     * The user who last updated the theme.
     */
    #[ORM\ManyToOne(inversedBy: 'updateThemes')]
    #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    /**
     * Collection of courses associated with this theme.
     *
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'theme')]
    private Collection $courses;

    /**
     * Collection of certifications associated with this theme.
     *
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'theme')]
    private Collection $certificationTheme;

    /**
     * Constructor.
     *
     * Initializes createdAt, updatedAt, courses, and certification collections.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->courses = new ArrayCollection();
        $this->certificationTheme = new ArrayCollection();
    }

    /**
     * Gets the theme ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the theme name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the theme name.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Gets the creation datetime.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation datetime.
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
     * Gets the last update datetime.
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Sets the last update datetime.
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
     * Gets the user who created the theme.
     *
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Sets the user who created the theme.
     *
     * @param User|null $user
     * @return $this
     */
    public function setCreatedBy(?User $user): self
    {
        $this->createdBy = $user;
        $user?->addCreateTheme($this); 
        return $this;
    }

    /**
     * Gets the user who last updated the theme.
     *
     * @return User|null
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Sets the user who last updated the theme.
     *
     * @param User|null $user
     * @return $this
     */
    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;
        $user?->addUpdateTheme($this); 
        return $this;
    }

    /**
     * Gets the collection of courses associated with the theme.
     *
     * @return Collection<int, Course>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    /**
     * Adds a course to the theme.
     *
     * @param Course $course
     * @return $this
     */
    public function addCourse(Course $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
            $course->setTheme($this);
        }
        return $this;
    }

    /**
     * Removes a course from the theme.
     *
     * @param Course $course
     * @return $this
     */
    public function removeCourse(Course $course): static
    {
        if ($this->courses->removeElement($course)) {
            if ($course->getTheme() === $this) {
                $course->setTheme(null);
            }
        }
        return $this;
    }

    /**
     * Gets the collection of certifications associated with the theme.
     *
     * @return Collection<int, Certification>
     */
    public function getCertificationTheme(): Collection
    {
        return $this->certificationTheme;
    }

    /**
     * Adds a certification to the theme.
     *
     * @param Certification $certificationTheme
     * @return $this
     */
    public function addCertificationTheme(Certification $certificationTheme): static
    {
        if (!$this->certificationTheme->contains($certificationTheme)) {
            $this->certificationTheme->add($certificationTheme);
            $certificationTheme->setTheme($this);
        }
        return $this;
    }

    /**
     * Removes a certification from the theme.
     *
     * @param Certification $certificationTheme
     * @return $this
     */
    public function removeCertificationTheme(Certification $certificationTheme): static
    {
        if ($this->certificationTheme->removeElement($certificationTheme)) {
            if ($certificationTheme->getTheme() === $this) {
                $certificationTheme->setTheme(null);
            }
        }
        return $this;
    }

    /**
     * Returns all lessons from all courses of this theme.
     *
     * @return array<int, Lesson>
     */
    public function getLessons(): array
    {
        $lessons = [];
        foreach ($this->getCourses() as $course) {
            foreach ($course->getLessons() as $lesson) {
                $lessons[] = $lesson;
            }
        }
        return $lessons;
    }
}