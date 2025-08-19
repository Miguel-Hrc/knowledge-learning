<?php

namespace App\Service;

use App\Document\UserDocument;
use App\Document\ThemeDocument;
use App\Document\CertificationDocument;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Service responsible for managing certifications in MongoDB.
 *
 * This service checks whether a user has completed all lessons in a theme
 * and awards a certification if it hasn't already been granted.
 */
class CertificationServiceMongo
{
    private ?DocumentManager $dm;

    /**
     * Constructor.
     *
     * @param DocumentManager|null $dm The Doctrine MongoDB document manager
     */
    public function __construct(?DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Verifies if the user has completed all lessons in the theme
     * and awards a certification if not already obtained.
     *
     * @param UserDocument $user The user to evaluate
     * @param ThemeDocument $themeDocument The theme to check
     */
    public function verifierEtAttribuerCertification(UserDocument $user, ThemeDocument $themeDocument): void
    {
        if (!$user || !$themeDocument) {
            return;
        }

        $lessonsTheme = $themeDocument->getLessons();
        $purchasedLessons = $user->getPurchasedLessonsMongo();

        // Ensure all lessons in the theme are purchased
        foreach ($lessonsTheme as $lesson) {
            $found = false;
            foreach ($purchasedLessons as $purchased) {
                if ((string) $purchased->getId() === (string) $lesson->getId()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return; // At least one lesson not purchased
            }
        }

        // Check for existing certification
        $existingCert = $this->dm->getRepository(CertificationDocument::class)->findOneBy([
            'user' => $user,
            'theme' => $themeDocument,
        ]);

        if ($existingCert) {
            return; // Already certified
        }

        // Create and persist new certification
        $certification = new CertificationDocument();
        $certification->setUser($user);
        $certification->setTheme($themeDocument);
        $certification->setDateObtention(new \DateTimeImmutable());
        $certification->setIsObtained(true);
        $certification->setCreatedAt(new \DateTimeImmutable());
        $certification->setUpdatedAt(new \DateTimeImmutable());

        // Defensive check to avoid duplicate key error
        if ($user->getId() !== null && $themeDocument->getId() !== null) {
            $this->dm->persist($certification);
            $this->dm->flush();
        }
    }
}
