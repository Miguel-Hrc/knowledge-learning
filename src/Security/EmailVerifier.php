<?php

namespace App\Security;

use App\Document\UserDocument;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Service responsible for sending and handling email verification for users.
 *
 * Supports both Doctrine ORM users (SQL) and MongoDB ODM users.
 */
class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private ?EntityManagerInterface $entityManager;
    private ?DocumentManager $documentManager;

    /**
     * Constructor.
     *
     * @param VerifyEmailHelperInterface $verifyEmailHelper Helper service to generate and validate signed email links
     * @param MailerInterface $mailer Mailer service used to send emails
     * @param EntityManagerInterface|null $entityManager ORM entity manager (nullable for Mongo-only setup)
     * @param DocumentManager|null $documentManager ODM document manager (nullable for SQL-only setup)
     */
    public function __construct(
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer,
        ?EntityManagerInterface $entityManager,
        ?DocumentManager $documentManager
    ) {
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
    }

    /**
     * Sends an email verification message to the user.
     *
     * @param string $verifyEmailRouteName The name of the route used to confirm email
     * @param UserInterface $user The user to verify
     * @param TemplatedEmail $email The email object to configure and send
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user, TemplatedEmail $email): void
    {
        $id = $user->getId();
        if ($id instanceof \MongoDB\BSON\ObjectId) {
            $id = (string) $id;
        }

        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $id,
            $user->getEmail(),
            ['id' => $id]
        );

        $email
            ->htmlTemplate('registration/confirmation_email.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Handles email confirmation by validating the signed URL and marking the user as verified.
     *
     * @param Request $request The HTTP request containing the signed URL
     * @param UserInterface $user The user to verify
     *
     * @throws VerifyEmailExceptionInterface If the email verification fails
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $id = $user->getId();
        if ($id instanceof \MongoDB\BSON\ObjectId) {
            $id = (string) $id;
        }

        $this->verifyEmailHelper->validateEmailConfirmation(
            $request->getUri(),
            $id,
            $user->getEmail()
        );

        $user->setIsVerified(true);

        if ($user instanceof UserDocument) {
            if ($this->documentManager !== null) {
                $this->documentManager->flush();
            }
        } elseif ($user instanceof User) {
            if ($this->entityManager !== null) {
                $this->entityManager->flush();
            }
        }
    }
}