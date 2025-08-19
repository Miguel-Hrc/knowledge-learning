<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MongoDB\BSON\ObjectId;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * Class UserDocument
 * Represents a user in the system stored in MongoDB.
 *
 * Implements Symfony's UserInterface, PasswordAuthenticatedUserInterface, and EquatableInterface
 * for authentication and equality checks.
 */
#[MongoDB\Document]
class UserDocument implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    /** 
     * @var string|ObjectId|null The unique identifier of the user in MongoDB
     */
    #[MongoDB\Id]
    private $id;

    /**
     * @var string The email of the user (used as username)
     */
    #[MongoDB\Field(type: "string")]
    private string $email;

    /**
     * @var string The hashed password of the user
     */
    #[MongoDB\Field(type: "string")]
    private string $password = '';

    /**
     * @var array User roles (e.g., ROLE_USER, ROLE_ADMIN)
     */
    #[MongoDB\Field(type: "collection")]
    private array $roles = ["ROLE_USER"];

    /**
     * @var bool Whether the user is verified
     */
    #[MongoDB\Field(type: "bool")]
    private bool $isVerified = false;

    /**
     * @var \DateTimeInterface|null The date and time when the user was created
     */
    #[MongoDB\Field(type: "date")]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @var \DateTimeInterface|null The date and time when the user was last updated
     */
    #[MongoDB\Field(type: "date")]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, CertificationDocument> Certifications linked to the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CertificationDocument::class, mappedBy: "user", nullable: true)]
    private Collection $certifications;

    /**
     * @var Collection<int, CommandDocument> Commands created by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CommandDocument::class, mappedBy: "user", nullable: true)]
    private Collection $commands;

    /**
     * @var Collection<int, PaymentDocument> Payments created by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: PaymentDocument::class, mappedBy: "user", nullable: true)]
    private Collection $createPayments;

    /**
     * @var Collection<int, PaymentDocument> Payments  updated by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: PaymentDocument::class, mappedBy: "user", nullable: true)]
    private Collection $updatePayments;

     /**
     * @var Collection<int, ThemeDocument> Themes created by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: ThemeDocument::class, mappedBy: "createdBy")]
    private Collection $createThemes;

    /**
     * @var Collection<int, ThemeDocument> Themes updated by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: ThemeDocument::class, mappedBy: "updatedBy")]
    private Collection $updateThemes;

    /**
     * @var Collection<int, CourseDocument> Courses created by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CourseDocument::class, mappedBy: "createdBy")]
    private Collection $createCourses;

    /**
     * @var Collection<int, CourseDocument> Courses updated by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CourseDocument::class, mappedBy: "updatedBy")]
    private Collection $updateCourses;

    /**
     * @var Collection<int, LessonDocument> Lessons created by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: LessonDocument::class, mappedBy: "createdBy")]
    private Collection $createLessons;

    /**
     * @var Collection<int, LessonDocument> Lessons updated by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: LessonDocument::class, mappedBy: "updatedBy")]
    private Collection $updateLessons;
    /**
     * @var Collection<int, CertificationDocument> Certifications created by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CertificationDocument::class, mappedBy: "createdBy")]
    private Collection $createCertifications;

    /**
     * @var Collection<int, CertificationDocument> Certifications updated by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CertificationDocument::class, mappedBy: "updatedBy")]
    private Collection $updateCertifications;
    /**
     * @var Collection<int, CommandDocument> Commands created by the user
     */

    #[MongoDB\ReferenceMany(targetDocument: CommandDocument::class, mappedBy: "createdBy")]
    private Collection $makeCommands;

    /**
     * @var Collection<int, LessonDocument> Lessons purchased by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: LessonDocument::class)]
    private Collection $purchasedLessons;

    /**
     * @var Collection<int, CourseDocument> Courses purchased by the user
     */
    #[MongoDB\ReferenceMany(targetDocument: CourseDocument::class)]
    private Collection $purchasedCourses;


    /**
     * Constructor initializes all collections and timestamps
     */
    public function __construct()
    {   
        $this->certifications = new ArrayCollection();
        $this->commands = new ArrayCollection();
        $this->createPayments = new ArrayCollection();
        $this->updatePayments = new ArrayCollection();
        $this->createThemes = new ArrayCollection();
        $this->updateThemes = new ArrayCollection();
        $this->createCourses = new ArrayCollection();
        $this->updateCourses = new ArrayCollection();
        $this->createLessons = new ArrayCollection();
        $this->updateLessons = new ArrayCollection();
        $this->createCertifications = new ArrayCollection();
        $this->updateCertifications = new ArrayCollection();
        $this->makeCommands = new ArrayCollection();
        $this->purchasedLessons = new ArrayCollection();
        $this->purchasedCourses = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get the user ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        if ($this->id instanceof ObjectId) {
            return (string) $this->id;
        }
        return $this->id;
    }

    /**
     * Get the user's email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the user's email
     *
     * @param string $email
     * @return static
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Returns the identifier for this user (email)
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Get the hashed password
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the hashed password
     *
     * @param string $password
     * @return static
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Erase sensitive credentials (not used)
     */
    public function eraseCredentials(): void {}

    /**
     * Get roles assigned to the user
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    /**
     * Set roles for the user
     *
     * @param array $roles
     * @return static
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Check if the user is verified
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Set verification status
     *
     * @param bool $isVerified
     * @return static
     */
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * Get creation timestamp
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt instanceof \DateTimeImmutable ? $this->createdAt : \DateTimeImmutable::createFromInterface($this->createdAt);
    }

    /**
     * Set creation timestamp
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
     * Get update timestamp
     *
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt instanceof \DateTimeImmutable ? $this->updatedAt : \DateTimeImmutable::createFromInterface($this->updatedAt);
    }

    /**
     * Set update timestamp
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
     * Get all certifications associated with the user.
     *
     * @return Collection<int, CertificationDocument>
     */
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    /**
     * Add a certification to the user.
     *
     * @param CertificationDocument $certification
     * @return static
     */
    public function addCertification(CertificationDocument $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setUtilisateur($this);
        }

        return $this;
    }

    /**
     * Remove a certification from the user.
     *
     * @param CertificationDocument $certification
     * @return static
     */
    public function removeCertification(CertificationDocument $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            if ($certification->getUtilisateur() === $this) {
                $certification->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * Get all commands created by the user.
     *
     * @return Collection<int, CommandeDocument>
     */
    public function getCommands(): Collection
    {
        return $this->commands;
    }

    /**
     * Add a command created by the user.
     *
     * @param CommandDocument $command
     * @return static
     */
    public function addCommand(CommandDocument $command): static
    {
        if (!$this->commands->contains($command)) {
            $this->commands->add($command);
            $command->setCreatedBy($this);
        }

        return $this;
    }

    /**
     * Remove a command created by the user.
     *
     * @param CommandDocument $command
     * @return static
     */
    public function removeCommand(CommandDocument $command): static
    {
        if ($this->commands->removeElement($command)) {
            if ($command->getCreatedBy() === $this) {
                $command->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all payments created by this user.
     *
     * @return Collection<int, Payment> Returns a collection of Payment entities.
     */
    public function getCreatePayments(): Collection
    {
        return $this->payments;
    }

    /**
     * Add a payment created by this user.
     *
     * @param Payment $createPayment The payment to add.
     * @return static
     */
    public function addCreatePayment(PaymentDocument $createPayment): static
    {
        if (!$this->createPayments->contains($createPayment)) {
            $this->createPayments->add($createPayment);
            if ($createPayment->getCreatedBy() !== $this) {
                $createPayment->setCreatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a payment created by this user.
     *
     * @param PaymentDocument $createPayment The payment to remove.
     * @return static
     */
    public function removeCreatePayment(PaymentDocument $createPayment): static
    {
        if ($this->createPayments->removeElement($createPayment)) {
            if ($createPayment->getCreatedBy() === $this) {
                $createPayment->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all commands updated by the user.
     *
     * @return Collection<int, CommandeDocument>
     */
    public function getUpdateCommands(): Collection
    {
        return $this->updateCommands;
    }

    /**
     * Add a command updated by the user.
     *
     * @param CommandDocument $updateCommand
     * @return static
     */
    public function addUpdateCommand(CommandDocument $updateCommand): static
    {
        if (!$this->updateCommands->contains($updateCommand)) {
            $this->updateCommands->add($updateCommand);
            $updateCommand->setUpdatedBy($this);
        }

        return $this;
    }

    /**
     * Remove a command updated by the user.
     *
     * @param CommandDocument $updateCommand
     * @return static
     */
    public function removeUpdateCommand(CommandDocument $updateCommand): static
    {
        if ($this->updateCommands->removeElement($updateCommand)) {
            if ($updateCommand->getUpdatedBy() === $this) {
                $updateCommand->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all payments updated by the user.
     *
     * @return Collection<int, PaymentDocument>
     */
    public function getUpdatePayments(): Collection
    {
        return $this->updatePayments;
    }

    /**
     * Add a payment updated by the user.
     *
     * @param PaymentDocument $updatePayment
     * @return static
     */
    public function addUpdatePayment(PaymentDocument $updatePayment): static
    {
        if (!$this->updatePayments->contains($updatePayment)) {
            $this->updatePayments->add($updatePayment);
            $updatePayment->setUpdatedBy($this);
        }

        return $this;
    }

    /**
     * Remove a payment updated by the user.
     *
     * @param PaymentDocument $updatePayment
     * @return static
     */
    public function removeUpdatePayment(PaymentDocument $updatePayment): static
    {
        if ($this->updatePayments->removeElement($updatePayment)) {
            if ($updatePayment->getUpdatedBy() === $this) {
                $updatePayment->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all themes created by the user.
     *
     * @return Collection<int, ThemeDocument>
     */
    public function getCreateThemes(): Collection
    {
        return $this->createThemes;
    }

    /**
     * Add a theme created by the user.
     *
     * @param ThemeDocument $createTheme
     * @return static
     */
    public function addCreateTheme(ThemeDocument $createTheme): static
    {
        if (!$this->createThemes->contains($createTheme)) {
            $this->createThemes->add($createTheme);
            if ($createTheme->getCreatedBy() !== $this) {
                $createTheme->setCreatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a theme created by the user.
     *
     * @param ThemeDocument $createTheme
     * @return static
     */
    public function removeCreateTheme(ThemeDocument $createTheme): static
    {
        if ($this->createThemes->removeElement($createTheme)) {
            if ($createTheme->getCreatedBy() === $this) {
                $createTheme->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all themes updated by the user.
     *
     * @return Collection<int, ThemeDocument>
     */
    public function getUpdateThemes(): Collection
    {
        return $this->updateThemes;
    }

    /**
     * Add a theme updated by the user.
     *
     * @param ThemeDocument $updateTheme
     * @return static
     */
    public function addUpdateTheme(ThemeDocument $updateTheme): static
    {
        if (!$this->updateThemes->contains($updateTheme)) {
            $this->updateThemes->add($updateTheme);
            if ($updateTheme->getUpdatedBy() !== $this) {
                $updateTheme->setUpdatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a theme updated by the user.
     *
     * @param ThemeDocument $updateTheme
     * @return static
     */
    public function removeUpdateTheme(ThemeDocument $updateTheme): static
    {
        if ($this->updateThemes->removeElement($updateTheme)) {
            if ($updateTheme->getUpdatedBy() === $this) {
                $updateTheme->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all courses created by the user.
     *
     * @return Collection<int, CourseDocument>
     */
    public function getCreateCourses(): Collection
    {
        return $this->createCourses;
    }

    /**
     * Add a course created by the user.
     *
     * @param CourseDocument $createCourse
     * @return static
     */
    public function addCreateCourse(CourseDocument $createCourse): static
    {
        if (!$this->createCourses->contains($createCourse)) {
            $this->createCourses->add($createCourse);
            if ($createCourse->getCreatedBy() !== $this) {
                $createCourse->setCreatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a course created by the user.
     *
     * @param CourseDocument $createCourse
     * @return static
     */
    public function removeCreateCourse(CourseDocument $createCourse): static
    {
        if ($this->createCourses->removeElement($createCourse)) {
            if ($createCourse->getCreatedBy() === $this) {
                $createCourse->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all courses updated by the user.
     *
     * @return Collection<int, CourseDocument>
     */
    public function getUpdateCourses(): Collection
    {
        return $this->updateCourses;
    }

    /**
     * Add a course updated by the user.
     *
     * @param CourseDocument $updateCourse
     * @return static
     */
    public function addUpdateCourse(CourseDocument $updateCourse): static
    {
        if (!$this->updateCourses->contains($updateCourse)) {
            $this->updateCourses->add($updateCourse);
            if ($updateCourse->getUpdatedBy() !== $this) {
                $updateCourse->setUpdatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a course updated by the user.
     *
     * @param CourseDocument $updateCourse
     * @return static
     */
    public function removeUpdateCourse(CourseDocument $updateCourse): static
    {
        if ($this->updateCourses->removeElement($updateCourse)) {
            if ($updateCourse->getUpdatedBy() === $this) {
                $updateCourse->setUpdatedBy(null);
            }
        }

        return $this;
    }
    /**
     * Get the lessons created by the user.
     *
     * @return Collection<int, LessonDocument>
     */
    public function getCreateLessons(): Collection
    {
        return $this->createLessons;
    }

    /**
     * Add a lesson created by the user.
     *
     * @param LessonDocument $createLesson
     * @return static
     */
    public function addCreateLesson(LessonDocument $createLesson): static
    {
        if (!$this->createLessons->contains($createLesson)) {
            $this->createLessons->add($createLesson);
            if ($createLesson->getCreatedBy() !== $this) {
                $createLesson->setCreatedBy($this); 
            }
        }

        return $this;
    }

    /**
     * Remove a lesson created by the user.
     *
     * @param LessonDocument $createLesson
     * @return static
     */
    public function removeCreateLesson(LessonDocument $createLesson): static
    {
        if ($this->createLessons->removeElement($createLesson)) {
            if ($createLesson->getCreatedBy() === $this) {
                $createLesson->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get lessons updated by the user.
     *
     * @return Collection<int, LessonDocument>
     */
    public function getUpdateLesson(): Collection
    {
        return $this->updateLesson;
    }

    /**
     * Add a lesson updated by the user.
     *
     * @param LessonDocument $updateLesson
     * @return static
     */
    public function addUpdateLesson(LessonDocument $updateLesson): static
    {
        if (!$this->updateLessons->contains($updateLesson)) {
            $this->updateLessons->add($updateLesson);
            if ($updateLesson->getUpdatedBy() !== $this) {
                $updateLesson->setUpdatedBy($this); 
            }
        }

        return $this;
    }

    /**
     * Remove a lesson updated by the user.
     *
     * @param LessonDocument $updateLesson
     * @return static
     */
    public function removeUpdateLesson(LessonDocument $updateLesson): static
    {
        if ($this->updateLesson->removeElement($updateLesson)) {
            if ($updateLesson->getUpdatedBy() === $this) {
                $updateLesson->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get certifications created by the user.
     *
     * @return Collection<int, CertificationDocument>
     */
    public function getCreateCertifications(): Collection
    {
        return $this->createCertifications;
    }

    /**
     * Add a certification created by the user.
     *
     * @param CertificationDocument $createCertification
     * @return static
     */
    public function addCreateCertification(CertificationDocument $createCertification): static
    {
        if (!$this->createCertifications->contains($createCertification)) {
            $this->createCertifications->add($createCertification);
            if ($createCertification->getCreatedBy() !== $this) {
                $createCertification->setCreatedBy($this); 
            }
        }

        return $this;
    }

    /**
     * Remove a certification created by the user.
     *
     * @param CertificationDocument $createCertification
     * @return static
     */
    public function removeCreateCertification(CertificationDocument $createCertification): static
    {
        if ($this->createCertifications->removeElement($createCertification)) {
            if ($createCertification->getCreatedBy() === $this) {
                $createCertification->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get certifications updated by the user.
     *
     * @return Collection<int, CertificationDocument>
     */
    public function getUpdateCertification(): Collection
    {
        return $this->updateCertification;
    }

    /**
     * Add a certification updated by the user.
     *
     * @param CertificationDocument $updateCertification
     * @return static
     */
    public function addUpdateCertification(CertificationDocument $updateCertification): static
    {
        if (!$this->updateCertifications->contains($updateCertification)) {
            $this->updateCertifications->add($updateCertification);
            if ($updateCertification->getUpdatedBy() !== $this) {
                $updateCertification->setUpdatedBy($this); 
            }
        }

        return $this;
    }

    /**
     * Remove a certification updated by the user.
     *
     * @param CertificationDocument $updateCertification
     * @return static
     */
    public function removeUpdateCertification(CertificationDocument $updateCertification): static
    {
        if ($this->updateCertifications->removeElement($updateCertification)) {
            if ($updateCertification->getUpdatedBy() === $this) {
                $updateCertification->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get commands made by the user.
     *
     * @return Collection<int, CommandDocument>
     */
    public function getMakeCommands(): Collection
    {
        return $this->makeCommands;
    }

    /**
     * Add a command made by the user.
     *
     * @param CommandDocument $makeCommand
     * @return static
     */
    public function addMakeCommand(CommandDocument $makeCommand): static
    {
        if (!$this->makeCommands->contains($makeCommand)) {
            $this->makeCommands->add($makeCommand);
            $makeCommand->setUser($this);
        }

        return $this;
    }

    /**
     * Remove a command made by the user.
     *
     * @param CommandDocument $makeCommand
     * @return static
     */
    public function removeMakeCommand(CommandDocument $makeCommand): static
    {
        if ($this->makeCommands->removeElement($makeCommand)) {
            if ($makeCommand->getUser() === $this) {
                $makeCommand->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Get lessons purchased by the user (MongoDB).
     *
     * @return Collection<int, LessonDocument>
     */
    public function getPurchasedLessonsMongo(): Collection
    {
        return $this->purchasedLessons;
    }

    /**
     * Add a purchased lesson (MongoDB).
     *
     * @param LessonDocument $lesson
     * @return static
     */
    public function addPurchasedLessonsMongo(LessonDocument $lesson): static
    {
        if (!$this->purchasedLessons->contains($lesson)) {
            $this->purchasedLessons->add($lesson);
        }
        return $this;
    }

    /**
     * Remove a purchased lesson (MongoDB).
     *
     * @param LessonDocument $lesson
     * @return static
     */
    public function removePurchasedLessonsMongo(LessonDocument $lesson): static
    {
        $this->purchasedLessons->removeElement($lesson);
        return $this;
    }

    /**
     * Get courses purchased by the user (MongoDB).
     *
     * @return Collection<int, CourseDocument>
     */
    public function getPurchasedCoursesMongo(): Collection
    {
        return $this->purchasedCourses;
    }

    /**
     * Add a purchased course (MongoDB).
     *
     * @param CourseDocument $course
     * @return static
     */
    public function addPurchasedCoursesMongo(CourseDocument $course): static
    {
        if (!$this->purchasedCourses->contains($course)) {
            $this->purchasedCourses->add($course);
        }
        return $this;
    }

    /**
     * Remove a purchased course (MongoDB).
     *
     * @param CourseDocument $course
     * @return static
     */
    public function removePurchasedCoursesMongo(CourseDocument $course): static
    {
        $this->purchasedCourses->removeElement($course);
        return $this;
    }

    /**
     * Serialize the user object.
     *
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->email,
            'roles' => $this->roles,
        ];
    }

    /**
     * Unserialize the user object.
     *
     * @param array $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->roles = $data['roles'];
    }

    /**
     * Check if this user is equal to another user.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self) {
            return false;
        }

        return $this->getUserIdentifier() === $user->getUserIdentifier();
    }
}