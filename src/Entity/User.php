<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use App\Entity\Lesson;

/**
 * Represents a User entity in the system.
 *
 * Implements Symfony's UserInterface, PasswordAuthenticatedUserInterface, 
 * and EquatableInterface for authentication and security.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    /**
     * The unique identifier of the user.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The email address of the user.
     */
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * The hashed password of the user.
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * Roles assigned to the user.
     *
     * @var array<int, string>
     */
    #[ORM\Column(type: 'json')]
    private array $roles = ["ROLE_USER"];

    /**
     * Indicates whether the user's email is verified.
     */
    #[ORM\Column]
    private ?bool $isVerified = false;

    /**
     * Timestamp when the user was created.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Timestamp when the user was last updated.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Certifications linked to the user.
     *
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'user', cascade: ['persist'])]
    private Collection $certifications;

    /**
     * Commands created by the user.
     *
     * @var Collection<int, Command>
     */
    #[ORM\OneToMany(targetEntity: Command::class, mappedBy: 'createdBy', cascade: ['persist'])]
    private Collection $commands;

    /**
     * Payments created by the user.
     *
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'createdBy', cascade: ['persist'])]
    private Collection $createPayments;

    /**
     * Command updated by the user.
     *
     * @var Collection<int, Command>
     */
    #[ORM\OneToMany(targetEntity: Command::class, mappedBy: 'updatedBy')]
    private Collection $updateCommands;

    /**
     * Payments updated by the user.
     *
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'updatedBy')]
    private Collection $updatePayments;

    /**
     * Themes created by the user.
     *
     * @var Collection<int, Theme>
     */
    #[ORM\OneToMany(targetEntity: Theme::class, mappedBy: 'createdBy')]
    private Collection $createThemes;

    /**
     * Themes updated by the user.
     *
     * @var Collection<int, Theme>
     */
    #[ORM\OneToMany(targetEntity: Theme::class, mappedBy: 'updatedBy')]
    private Collection $updateThemes;

    /**
     * Courses created by the user.
     *
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'createdBy')]
    private Collection $createCourses;

    /**
     * Courses updated by the user.
     *
     * @var Collection<int, Course>
     */
    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'updatedBy')]
    private Collection $updateCourses;

    /**
     * Lessons created by the user.
     *
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'createdBy')]
    private Collection $createLessons;

    /**
     * Lessons updated by the user.
     *
     * @var Collection<int, Lesson>
     */
    #[ORM\OneToMany(targetEntity: Lesson::class, mappedBy: 'updatedBy')]
    private Collection $updateLessons;

    /**
     * Certifications created by the user.
     *
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'createdBy')]
    private Collection $createCertifications;

    /**
     * Certifications updated by the user.
     *
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'updatedBy')]
    private Collection $updateCertifications;

    /**
     * Commands created by the user.
     *
     * @var Collection<int, Command>
     */
    #[ORM\OneToMany(targetEntity: Command::class, mappedBy: 'user')]
    private Collection $makeCommands;

    /**
     * Lessons purchased by the user.
     *
     * @var Collection<int, Lesson>
     */
    #[ORM\ManyToMany(targetEntity: Lesson::class)]
    private Collection $purchasedLessons;

    /**
     * Courses purchased by the user.
     *
     * @var Collection<int, Course>
     */
    #[ORM\ManyToMany(targetEntity: Course::class)]
    private Collection $purchasedCourses;

    /**
     * User constructor.
     *
     * Initializes collections and sets default timestamps.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->certifications = new ArrayCollection();
        $this->commands = new ArrayCollection();
        $this->createPayments = new ArrayCollection();
        $this->updateCommands = new ArrayCollection();
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
    }

    /**
     * Gets the unique identifier of the user.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the email address of the user.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the email address of the user.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Returns the identifier used for authentication (email).
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Gets the hashed password.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Sets the hashed password.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Removes sensitive data from the user object.
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * Returns the roles granted to the user.
     *
     * Ensures ROLE_USER is always included.
     *
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    /**
     * Sets the roles of the user.
     *
     * @param array<int, string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Checks if the user's email is verified.
     */
    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    /**
     * Sets whether the user's email is verified.
     */
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }


    /**
     * Get the creation date of the entity.
     *
     * @return \DateTimeImmutable|null Returns the createdAt date.
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date of the entity.
     *
     * @param \DateTimeImmutable $createdAt The creation date.
     * @return static
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get the last update date of the entity.
     *
     * @return \DateTimeImmutable|null Returns the updatedAt date.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update date of the entity.
     *
     * @param \DateTimeImmutable $updatedAt The update date.
     * @return static
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get all certifications created by this user.
     *
     * @return Collection<int, Certification> Returns a collection of Certification entities.
     */
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    /**
     * Add a certification to this user.
     *
     * @param Certification $certification The certification to add.
     * @return static
     */
    public function addCertification(Certification $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setUtilisateur($this);
        }

        return $this;
    }

    /**
     * Remove a certification from this user.
     *
     * @param Certification $certification The certification to remove.
     * @return static
     */
    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            if ($certification->getUtilisateur() === $this) {
                $certification->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * Get all commands created by this user.
     *
     * @return Collection<int, Commande> Returns a collection of Command entities.
     */
    public function getCommands(): Collection
    {
        return $this->commands;
    }

    /**
     * Add a command to this user.
     *
     * @param Command $command The command to add.
     * @return static
     */
    public function addCommand(Command $command): static
    {
        if (!$this->commands->contains($command)) {
            $this->commands->add($command);
            if ($command->getCreatedBy() !== $this) {
                $command->setCreatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a command from this user.
     *
     * @param Command $command The command to remove.
     * @return static
     */
    public function removeCommand(Command $command): static
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
    public function addCreatePayment(Payment $createPayment): static
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
     * @param Payment $createPayment The payment to remove.
     * @return static
     */
    public function removeCreatePayment(Payment $createPayment): static
    {
        if ($this->createPayments->removeElement($createPayment)) {
            if ($createPayment->getCreatedBy() === $this) {
                $createPayment->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all commands updated by this user.
     *
     * @return Collection<int, Commande> Returns a collection of Command entities.
     */
    public function getUpdateCommands(): Collection
    {
        return $this->updateCommands;
    }

    /**
     * Add a command updated by this user.
     *
     * @param Command $updateCommand The command to add.
     * @return static
     */
    public function addUpdateCommand(Command $updateCommand): static
    {
        if (!$this->updateCommands->contains($updateCommand)) {
            $this->updateCommands->add($updateCommand);
            $updateCommand->setUpdatedBy($this);
        }

        return $this;
    }

    /**
     * Remove a command updated by this user.
     *
     * @param Command $updateCommand The command to remove.
     * @return static
     */
    public function removeUpdateCommand(Command $updateCommand): static
    {
        if ($this->updateCommands->removeElement($updateCommand)) {
            if ($updateCommand->getUpdatedBy() === $this) {
                $updateCommand->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all payments updated by this user.
     *
     * @return Collection<int, Payment> Returns a collection of Payment entities.
     */
    public function getUpdatePayments(): Collection
    {
        return $this->updatepayments;
    }

    /**
     * Add a payment updated by this user.
     *
     * @param Payment $updatePayment The payment to add.
     * @return static
     */
    public function addUpdatePayment(Payment $updatePayment): static
    {
        if (!$this->updatePayments->contains($updatePayment)) {
            $this->updatePayments->add($updatePayment);
            if ($updatePayment->getUpdatedBy() !== $this) {
                $updatePayment->setUpdatedBy($this);
            }
        }

        return $this;
    }

    /**
     * Remove a payment updated by this user.
     *
     * @param Payment $updatePayment The payment to remove.
     * @return static
     */
    public function removeUpdatePayment(Payment $updatePayment): static
    {
        if ($this->updatepayments->removeElement($updatePayment)) {
            if ($updatePayment->getUpdatedBy() === $this) {
                $updatePayment->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all themes created by this user.
     *
     * @return Collection<int, Theme> Returns a collection of Theme entities.
     */
    public function getCreateThemes(): Collection
    {
        return $this->createThemes;
    }

    /**
     * Add a theme created by this user.
     *
     * @param Theme $createTheme The theme to add.
     * @return static
     */
    public function addCreateTheme(Theme $createTheme): static
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
     * Remove a theme created by this user.
     *
     * @param Theme $createTheme The theme to remove.
     * @return static
     */
    public function removeCreateTheme(Theme $createTheme): static
    {
        if ($this->createThemes->removeElement($createTheme)) {
            if ($createTheme->getCreatedBy() === $this) {
                $createTheme->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all themes updated by this user.
     *
     * @return Collection<int, Theme> Returns a collection of Theme entities.
     */
    public function getUpdateThemes(): Collection
    {
        return $this->updateThemes;
    }

    /**
     * Add a theme updated by this user.
     *
     * @param Theme $updateTheme The theme to add.
     * @return static
     */
    public function addUpdateTheme(Theme $updateTheme): static
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
     * Remove a theme updated by this user.
     *
     * @param Theme $updateTheme The theme to remove.
     * @return static
     */
    public function removeUpdateTheme(Theme $updateTheme): static
    {
        if ($this->updateThemes->removeElement($updateTheme)) {
            if ($updateTheme->getUpdatedBy() === $this) {
                $updateTheme->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get all courses created by this user.
     *
     * @return Collection<int, Course> Returns a collection of Course entities.
     */
    public function getCreateCourses(): Collection
    {
        return $this->createCourses;
    }

    /**
     * Add a course created by this user.
     *
     * @param Course $createCourse The course to add.
     * @return static
     */
    public function addCreateCourse(Course $createCourse): static
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
     * Remove a course created by this user.
     *
     * @param Cursus $createCourse The course to remove.
     * @return static
     */
    public function removeCreateCourse(Cursus $createCourse): static
    {
        if ($this->createCourses->removeElement($createCourse)) {
            if ($createCourse->getCreatedBy() === $this) {
                $createCourse->setCreatedBy(null);
            }
        }

        return $this;
    }


    /**
     * Get the collection of courses updated by the user.
     *
     * @return Collection<int, Course>
     */
    public function getUpdateCourses(): Collection
    {
        return $this->updateCourses;
    }

    /**
     * Add a course to the updated courses collection.
     *
     * @param Course $updateCourse
     * @return static
     */
    public function addUpdateCourse(Course $updateCourse): static
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
     * Remove a course from the updated courses collection.
     *
     * @param Course $updateCourse
     * @return static
     */
    public function removeUpdateCourse(Course $updateCourse): static
    {
        if ($this->updateCourses->removeElement($updateCourse)) {
            if ($updateCourse->getUpdatedBy() === $this) {
                $updateCourse->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get the collection of lessons created by the user.
     *
     * @return Collection<int, Lesson>
     */
    public function getCreateLessons(): Collection
    {
        return $this->createLessons;
    }

    /**
     * Add a lesson to the created lessons collection.
     *
     * @param Lesson $createLesson
     * @return static
     */
    public function addCreateLesson(Lesson $createLesson): static
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
     * Remove a lesson from the created lessons collection.
     *
     * @param Lesson $createLesson
     * @return static
     */
    public function removeCreateLesson(Lesson $createLesson): static
    {
        if ($this->createLessons->removeElement($createLesson)) {
            if ($createLesson->getCreatedBy() === $this) {
                $createLesson->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get the collection of lessons updated by the user.
     *
     * @return Collection<int, Lesson>
     */
    public function getUpdateLessons(): Collection
    {
        return $this->updateLessons;
    }

    /**
     * Add a lesson to the updated lessons collection.
     *
     * @param Lesson $updateLesson
     * @return static
     */
    public function addUpdateLesson(Lesson $updateLesson): static
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
     * Remove a lesson from the updated lessons collection.
     *
     * @param Lesson $updateLesson
     * @return static
     */
    public function removeUpdateLesson(Lesson $updateLesson): static
    {
        if ($this->updateLessons->removeElement($updateLesson)) {
            if ($updateLesson->getUpdatedBy() === $this) {
                $updateLesson->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get the collection of certifications created by the user.
     *
     * @return Collection<int, Certification>
     */
    public function getCreateCertifications(): Collection
    {
        return $this->createCertifications;
    }

    /**
     * Add a certification to the created certifications collection.
     *
     * @param Certification $createCertification
     * @return static
     */
    public function addCreateCertification(Certification $createCertification): static
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
     * Remove a certification from the created certifications collection.
     *
     * @param Certification $createCertification
     * @return static
     */
    public function removeCreateCertification(Certification $createCertification): static
    {
        if ($this->createCertifications->removeElement($createCertification)) {
            if ($createCertification->getCreatedBy() === $this) {
                $createCertification->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get the collection of certifications updated by the user.
     *
     * @return Collection<int, Certification>
     */
    public function getUpdateCertifications(): Collection
    {
        return $this->updateCertifications;
    }

    /**
     * Add a certification to the updated certifications collection.
     *
     * @param Certification $updateCertification
     * @return static
     */
    public function addUpdateCertification(Certification $updateCertification): static
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
     * Remove a certification from the updated certifications collection.
     *
     * @param Certification $updateCertification
     * @return static
     */
    public function removeUpdateCertification(Certification $updateCertification): static
    {
        if ($this->updateCertifications->removeElement($updateCertification)) {
            if ($updateCertification->getUpdatedBy() === $this) {
                $updateCertification->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Get the collection of commands made by the user.
     *
     * @return Collection<int, Command>
     */
    public function getMakeCommands(): Collection
    {
        return $this->makeCommands;
    }

    /**
     * Add a command to the user's commands.
     *
     * @param Command $makeCommand
     * @return static
     */
    public function addMakeCommand(Command $makeCommand): static
    {
        if (!$this->makeCommands->contains($makeCommand)) {
            $this->makeCommands->add($makeCommand);
            $makeCommand->setUser($this);
        }

        return $this;
    }

    /**
     * Remove a command from the user's commands.
     *
     * @param Command $makeCommand
     * @return static
     */
    public function removeMakeCommand(Command $makeCommand): static
    {
        if ($this->makeCommands->removeElement($makeCommand)) {
            if ($makeCommand->getUser() === $this) {
                $makeCommand->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Get the collection of purchased lessons.
     *
     * @return Collection<int, Lesson>
     */
    public function getPurchasedLessons(): Collection
    {
        return $this->purchasedLessons;
    }

    /**
     * Add a lesson to the purchased lessons collection.
     *
     * @param Lesson $lesson
     * @return static
     */
    public function addPurchasedLessons(Lesson $lesson): static
    {
        if (!$this->purchasedLessons->contains($lesson)) {
            $this->purchasedLessons->add($lesson);
        }

        return $this;
    }

    /**
     * Remove a lesson from the purchased lessons collection.
     *
     * @param Lesson $lesson
     * @return static
     */
    public function removePurchasedLessons(Lesson $lesson): static
    {
        $this->purchasedLessons->removeElement($lesson);

        return $this;
    }

    /**
     * Get the collection of purchased courses.
     *
     * @return Collection<int, Course>
     */
    public function getPurchasedCourses(): Collection
    {
        return $this->purchasedCourses;
    }

    /**
     * Add a course to the purchased courses collection.
     *
     * @param Course $course
     * @return static
     */
    public function addPurchasedCourses(Course $course): static
    {
        if (!$this->purchasedCourses->contains($course)) {
            $this->purchasedCourses->add($course);
        }

        return $this;
    }

    /**
     * Serialize the user object for storage.
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
     * Unserialize the user object from stored data.
     *
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->roles = $data['roles'];
    }

    /**
     * Check if the current user is equal to another user.
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