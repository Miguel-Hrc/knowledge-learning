<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Theme;
use App\Entity\Certification;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service responsible for managing certifications.
 * 
 * This service checks if a user has completed all lessons in a theme
 * and assigns the corresponding certification if applicable.
 */
class CertificationService
{
    private ?EntityManagerInterface $em;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface|null $em The Doctrine entity manager
     */
    public function __construct(?EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Checks if the user has completed all lessons in a given theme
     * and assigns a certification if not already obtained.
     *
     * @param User $user The user to check and potentially award
     * @param Theme $theme The theme to verify
     *
     * @return void
     */
    public function verifierEtAttribuerCertification(User $user, Theme $theme): void
    {
        // Get all lessons associated with the theme
        $lessonsTheme = $theme->getLessons(); 

        // Get all lessons the user has purchased/completed
        $purchasedLessons = $user->getPurchasedLessons(); 

        $allValidated = true;

        // Check if all lessons in the theme are purchased by the user
        foreach ($lessonsTheme as $lesson) {
            if (!$purchasedLessons->contains($lesson)) {
                $allValidated = false;
                break;
            }
        }

        // If all lessons validated, check if certification already exists
        if ($allValidated) {
            foreach ($user->getCertifications() as $certification) {
                if ($certification->getTheme() === $theme) {
                    return; // Certification already exists, do nothing
                }
            }

            // Create a new certification for the user and theme
            $certification = new Certification();
            $certification->setUser($user);
            $certification->setTheme($theme);
            $certification->setDateObtention(new \DateTimeImmutable());
            $certification->setIsObtained(true);

            // Persist the new certification
            $this->em->persist($certification);
            $this->em->flush();
        }
    }
}