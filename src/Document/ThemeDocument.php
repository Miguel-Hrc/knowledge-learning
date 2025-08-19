<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ThemeRepositoryMongo;

/**
 * Class ThemeDocument
 *
 * Represents a theme that can contain multiple courses and certifications.
 */
#[MongoDB\Document(repositoryClass: ThemeRepositoryMongo::class)]
class ThemeDocument
{
    /**
     * @var string|null The unique identifier of the theme.
     */
    #[MongoDB\Id]
    private ?string $id = null;

    /**
     * @var string|null The name of the theme.
     */
    #[MongoDB\Field(type: 'string')]
    private ?string $name = null;

    /**
     * @var \DateTimeImmutable|null The creation date of the theme.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var \DateTimeImmutable|null The last update date of the theme.
     */
    #[MongoDB\Field(type: 'date_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var UserDocument|null The user who created this theme.
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, nullable: true, storeAs: 'id')]
    private ?UserDocument $createdBy = null;

    /**
     * @var UserDocument|null The user who last updated this theme.
     */
    #[MongoDB\ReferenceOne(targetDocument: UserDocument::class, nullable: true, storeAs: 'id')]
    private ?UserDocument $updatedBy = null;

    /**
     * @var Collection<int, CourseDocument> Courses associated with this theme.
     */
    #[MongoDB\ReferenceMany(targetDocument: CourseDocument::class, mappedBy: 'theme')]
    private Collection $courses;

    /**
     * @var Collection<int, CertificationDocument> Certifications associated with this theme.
     */
    #[MongoDB\ReferenceMany(targetDocument: CertificationDocument::class, mappedBy: 'theme')]
    private Collection $certificationTheme;

    /**
     * ThemeDocument constructor.
     *
     * Initializes collections and sets creation and update timestamps.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->courses = new ArrayCollection();
        $this->certificationTheme = new ArrayCollection();
    }

    /**
     * Get the unique identifier of the theme.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the name of the theme.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the theme.
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
     * Get the creation timestamp of the theme.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp of the theme.
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
     * Get the last update timestamp of the theme.
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update timestamp of the theme.
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
     * Get the user who created the theme.
     *
     * @return UserDocument|null
     */
    public function getCreatedBy(): ?UserDocument
    {
        return $this->createdBy;
    }

    /**
     * Set the user who created the theme.
     *
     * @param UserDocument|null $user
     * @return $this
     */
    public function setCreatedBy(?UserDocument $user): static
    {
        $this->createdBy = $user;
        $user?->addCreateTheme($this);
        return $this;
    }

    /**
     * Get the user who last updated the theme.
     *
     * @return UserDocument|null
     */
    public function getUpdatedBy(): ?UserDocument
    {
        return $this->updatedBy;
    }

    /**
     * Set the user who last updated the theme.
     *
     * @param UserDocument|null $user
     * @return $this
     */
    public function setUpdatedBy(?UserDocument $user): static
    {
        $this->updatedBy = $user;
        return $this;
    }

    /**
     * Get all courses associated with this theme.
     *
     * @return Collection<int, CourseDocument>
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    /**
     * Add a course to this theme.
     *
     * @param CourseDocument $course
     * @return $this
     */
    public function addCourse(CourseDocument $course): static
    {
        if (!$this->courses->contains($course)) {
            $this->courses->add($course);
        }
        return $this;
    }

    /**
     * Remove a course from this theme.
     *
     * @param CourseDocument $course
     * @return $this
     */
    public function removeCourse(CourseDocument $course): static
    {
        $this->courses->removeElement($course);
        return $this;
    }

    /**
     * Get all certifications associated with this theme.
     *
     * @return Collection<int, CertificationDocument>
     */
    public function getCertificationTheme(): Collection
    {
        return $this->certificationTheme;
    }

    /**
     * Add a certification to this theme.
     *
     * @param CertificationDocument $certificationTheme
     * @return $this
     */
    public function addCertificationTheme(CertificationDocument $certificationTheme): static
    {
        if (!$this->certificationTheme->contains($certificationTheme)) {
            $this->certificationTheme->add($certificationTheme);
        }
        return $this;
    }

    /**
     * Remove a certification from this theme.
     *
     * @param CertificationDocument $certificationTheme
     * @return $this
     */
    public function removeCertificationTheme(CertificationDocument $certificationTheme): static
    {
        $this->certificationTheme->removeElement($certificationTheme);
        return $this;
    }

    /**
     * Get all lessons from all courses under this theme.
     *
     * @return array An array of LessonDocument objects.
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