<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * Represents an individual item within a command (order).
 *
 * @MongoDB\EmbeddedDocument
 */
#[MongoDB\EmbeddedDocument]
class CommandItemDocument
{
    /**
     * The command (order) this item belongs to.
     *
     * @var CommandDocument|null
     */
    #[MongoDB\ReferenceOne(targetDocument: CommandDocument::class, inversedBy: 'commandItems')]
    private ?CommandDocument $command = null;

    /**
     * The ID of the lesson included in this command item, if any.
     *
     * @var string|null
     */
    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $lessonId = null;

    /**
     * The ID of the course included in this command item, if any.
     *
     * @var string|null
     */
    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $courseId = null;
        
    /**
     * The price of this command item.
     *
     * @var float|null
     */
    #[MongoDB\Field(type: 'float')]
    private ?float $price = null;

    /**
     * Gets the lesson ID associated with this item.
     */
    public function getLessonId(): ?string
    {
        return $this->lessonId;
    }

    /**
     * Sets the lesson ID for this item.
     */
    public function setLessonId(?string $lessonId): static
    {
        $this->lessonId = $lessonId;
        return $this;
    }

    /**
     * Gets the course ID associated with this item.
     */
    public function getCourseId(): ?string
    {
        return $this->courseId;
    }

    /**
     * Sets the course ID for this item.
     */
    public function setCourseId(?string $courseId): static
    {
        $this->courseId = $courseId;
        return $this;
    }

    /**
     * Gets the price of this command item.
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Sets the price of this command item.
     */
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }
    
    /**
     * Gets the command (order) this item belongs to.
     */
    public function getCommand(): ?CommandDocument
    {
        return $this->command;
    }

    /**
     * Sets the command (order) this item belongs to.
     */
    public function setCommand(?CommandDocument $command): static
    {
        $this->command = $command;
        return $this;
    }
}