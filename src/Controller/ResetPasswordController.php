<?php

namespace App\Controller;

use App\Entity\User;
use App\Document\UserDocument;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Controller responsible for handling password reset requests.
 * Supports both SQL (ORM) and MongoDB (ODM) data sources.
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private ResetPasswordHelperInterface $resetPasswordHelper;
    private ?EntityManagerInterface $entityManager;
    private ?DocumentManager $documentManager;
    private string $dataSource;

    /**
     * ResetPasswordController constructor.
     *
     * @param ResetPasswordHelperInterface $resetPasswordHelper Service to manage reset password tokens.
     * @param EntityManagerInterface|null $entityManager Doctrine ORM entity manager (nullable if only MongoDB is used).
     * @param DocumentManager|null $documentManager Doctrine ODM document manager (nullable if only SQL is used).
     * @param ParameterBagInterface $params Provides access to application parameters (e.g., app.data_source).
     */
    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        ?EntityManagerInterface $entityManager,
        ?DocumentManager $documentManager,
        ParameterBagInterface $params
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Display and handle the password reset request form.
     *
     * @param Request $request The current HTTP request.
     * @param MailerInterface $mailer The mailer service used to send reset emails.
     * @param TranslatorInterface $translator The translator service for error messages.
     * @param MessageBusInterface $bus Messenger bus (used if needed for async processing).
     * @return Response The rendered password reset request page or a redirect to email check.
     */
    #[Route('/reset-password', name: 'app_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator,
                $bus
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'dataSource' => $this->dataSource,
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page shown after requesting a password reset.
     *
     * @return Response The check email confirmation page.
     */
    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
     * Handle the password reset process using a valid token.
     *
     * @param Request $request The HTTP request containing the reset form data.
     * @param UserPasswordHasherInterface $userPasswordHasher Service used to hash the new password.
     * @param TranslatorInterface $translator Translator for error messages.
     * @param string|null $token The reset token (optional, handled via session if not provided).
     * @return Response The reset form page or a redirect after successful reset.
     */
    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $userPasswordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));
            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);
            $encodedPassword = $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            // Update SQL user password if ORM is enabled
            if (in_array($this->dataSource, ['orm', 'both'])) {
                $user->setPassword($encodedPassword);
                $this->entityManager->flush();
            }

            // Update MongoDB user password if ODM is enabled
            if (in_array($this->dataSource, ['mongodb', 'both'])) {
                $userDocument = $this->documentManager->getRepository(UserDocument::class)->findOneBy(['email' => $user->getEmail()]);
                if (!$userDocument) {
                    $userDocument = new UserDocument();
                    $userDocument->setEmail($user->getEmail());
                }
                $userDocument->setPassword($encodedPassword);
                $userDocument->setUpdatedAt(new \DateTimeImmutable());

                $this->documentManager->persist($userDocument);
                $this->documentManager->flush();
            }

            $this->cleanSessionAfterReset();
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * Internal helper to process sending a password reset email.
     *
     * @param string $emailFormData The email address entered by the user.
     * @param MailerInterface $mailer The mailer service to send the reset email.
     * @param TranslatorInterface $translator Translator service for messages.
     * @param MessageBusInterface $bus Messenger bus (can be used for async handling).
     * @return RedirectResponse Redirect to the check email confirmation page.
     */
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator, MessageBusInterface $bus): RedirectResponse
    {
        $user = null;

        if (in_array($this->dataSource, ['orm', 'both'])) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $emailFormData]);
        }

        if (!$user && in_array($this->dataSource, ['mongodb'])) {
            $user = $this->documentManager->getRepository(UserDocument::class)->findOneBy(['email' => $emailFormData]);
        }

        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('anonymikuw@gmail.com', 'Miguel'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);
        $mailer->send($email);

        $this->setTokenObjectInSession($resetToken);
        return $this->redirectToRoute('app_check_email');
    }
}