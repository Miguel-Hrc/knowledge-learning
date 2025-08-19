<?php

namespace App\Service;

use App\Entity\User;
use App\Document\UserDocument;
use App\Entity\Lesson;
use App\Document\LessonDocument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service to validate that a user can only access lessons or courses
 * that correspond to their data storage type (SQL or MongoDB).
 *
 * This prevents SQL users from accessing MongoDB documents and vice versa.
 */
class UserDataAccessValidator
{
    /**
     * Validates that a user has access to a lesson.
     *
     * Throws an AccessDeniedException if:
     * - A SQL user tries to access a MongoDB lesson
     * - A MongoDB user tries to access a SQL lesson
     *
     * @param UserInterface $user The currently authenticated user (SQL or MongoDB)
     * @param object $lesson The lesson object to validate (Lesson or LessonDocument)
     *
     * @throws AccessDeniedException If the user tries to access a lesson from a different storage type
     */
    public function validateUserAccessToLesson(UserInterface $user, object $lesson): void
    {
        if ($user instanceof User && $lesson instanceof LessonDocument) {
            throw new AccessDeniedException('A SQL user cannot access a MongoDB lesson.');
        }

        if ($user instanceof UserDocument && $lesson instanceof Lesson) {
            throw new AccessDeniedException('A MongoDB user cannot access a SQL lesson.');
        }
    }

    /**
     * Validates that a user has access to a course.
     *
     * Throws an AccessDeniedException if:
     * - A SQL user tries to access a MongoDB course
     * - A MongoDB user tries to access a SQL course
     *
     * @param UserInterface $user The currently authenticated user (SQL or MongoDB)
     * @param object $course The course object to validate (Course or CourseDocument)
     *
     * @throws AccessDeniedException If the user tries to access a course from a different storage type
     */
    public function validateUserAccessToCourse(UserInterface $user, object $course): void
    {
        if ($user instanceof User && $course instanceof \App\Document\CourseDocument) {
            throw new AccessDeniedException('A SQL user cannot access a MongoDB class.');
        }

        if ($user instanceof UserDocument && $course instanceof \App\Entity\Course) {
            throw new AccessDeniedException('A MongoDB user cannot access a SQL class.');
        }
    }
}