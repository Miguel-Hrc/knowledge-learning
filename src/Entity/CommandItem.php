<?php

namespace App\Entity;

use App\Repository\CommandItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a single item in a command (order), which can be either a lesson or a course.
 */
#[ORM\Entity(repositoryClass: CommandItemRepository::class)]
class CommandItem
{
    /**
     * The unique identifier of the command item.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The command (order) to which this item belongs.
     */
    #[ORM\ManyToOne(inversedBy: 'commandItems')]
    private ?Command $command = null;

    /**
     * The lesson associated with this command item, if applicable.
     */
    #[ORM\ManyToOne]
    private ?Lesson $lesson = null;

    /**
     * The course associated with this command item, if applicable.
     */
    #[ORM\ManyToOne]
    private ?Course $course = null;

    /**
     * The price of this command item.
     */
    #[ORM\Column]
    private ?float $price = null;

    /**
     * Get the unique identifier of the command item.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the command associated with this item.
     *
     * @return Command|null
     */
    public function getCommand(): ?Command
    {
        return $this->command;
    }

    /**
     * Set the command associated with this item.
     *
     * @param Command|null $command
     * @return $this
     */
    public function setCommand(?Command $command): static
    {
        $this->command = $command;
        return $this;
    }

    /**
     * Get the lesson associated with this item.
     *
     * @return Lesson|null
     */
    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    /**
     * Set the lesson associated with this item.
     *
     * @param Lesson|null $lesson
     * @return $this
     */
    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    /**
     * Get the course associated with this item.
     *
     * @return Course|null
     */
    public function getCourse(): ?Course
    {
        return $this->course;
    }

    /**
     * Set the course associated with this item.
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
     * Get the price of this command item.
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set the price of this command item.
     *
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }
}