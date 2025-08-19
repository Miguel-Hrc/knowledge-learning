<?php

namespace App\Controller;

use App\Entity\User;
use App\Document\UserDocument;
use App\Form\RegistrationFormType;
use App\Form\RegistrationFormTypeMongo;
use App\Security\OrmAuthenticator;
use App\Security\MongoAuthenticator;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryMongo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * Controller responsible for user registration and email verification.
 * 
 * This controller manages both SQL (ORM) and MongoDB (ODM) user registrations,
 * handles email confirmation, and integrates with authenticators for automatic login.
 */
class RegistrationController extends AbstractController
{
    /**
     * @var EmailVerifier Service used for sending and verifying email confirmation links.
     */
    private EmailVerifier $emailVerifier;

    /**
     * @var DocumentManager|null Doctrine ODM DocumentManager for MongoDB persistence.
     */
    private ?DocumentManager $documentManager;

    /**
     * @var UserRepositoryMongo Repository for managing MongoDB UserDocument objects.
     */
    private ?UserRepositoryMongo $userRepositoryMongo;

    /**
     * @var string Defines the data source configuration (sql, mongo, or both).
     */
    
    private string $dataSource;

    /**
     * Constructor.
     *
     * @param EmailVerifier $emailVerifier Service for handling email verification.
     * @param DocumentManager|null $documentManager Doctrine ODM document manager (optional).
     * @param UserRepositoryMongo $userRepositoryMongo Repository for MongoDB users.
     * @param ParameterBagInterface $params Parameter bag for fetching app configuration.
     */
    public function __construct(
        EmailVerifier $emailVerifier,
        ?DocumentManager $documentManager,
        ?UserRepositoryMongo $userRepositoryMongo,
        ParameterBagInterface $params,
    ) {
        $this->emailVerifier = $emailVerifier;
        $this->documentManager = $documentManager;
        $this->userRepositoryMongo = $userRepositoryMongo;
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Handles user registration for both SQL (ORM) and MongoDB (ODM).
     *
     * This method displays and processes two separate forms:
     * - SQL users via Doctrine ORM and `RegistrationFormType`.
     * - MongoDB users via Doctrine ODM and `RegistrationFormTypeMongo`.
     *
     * After successful registration, an email confirmation is sent and the user
     * is optionally logged in using the configured authenticator.
     *
     * @param Request $request Current HTTP request.
     * @param UserPasswordHasherInterface $passwordHasher Service for hashing passwords.
     * @param UserAuthenticatorInterface $userAuthenticator Service for authenticating users.
     * @param OrmAuthenticator|null $ormAuthenticator Custom authenticator for ORM users (optional).
     * @param MongoAuthenticator|null $mongoAuthenticator Custom authenticator for MongoDB users (optional).
     * @param EntityManagerInterface|null $entityManager Doctrine ORM entity manager (optional).
     *
     * @return Response Renders registration form(s) or redirects after registration.
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        ?OrmAuthenticator $ormAuthenticator,
        ?MongoAuthenticator $mongoAuthenticator,
        ?EntityManagerInterface $entityManager
    ): Response {
        // ORM user and form
        $user = new User();
        $formSql = $this->createForm(RegistrationFormType::class, $user);
        $formSql->handleRequest($request);

        // MongoDB user and form
        $userDoc = new UserDocument();
        $formMongo = $this->createForm(RegistrationFormTypeMongo::class, $userDoc);
        $formMongo->handleRequest($request);

        // ORM registration flow
        if ($formSql->isSubmitted() && $formSql->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $formSql->get('plainPassword')->getData()));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('anonymikuw@outlook.fr', 'Inscription Anonymik'))
                    ->to($user->getEmail())
                    ->subject('Confirm your email')
            );


            return $this->redirectToRoute('app_registration_request');
        }

        // MongoDB registration flow
        if ($formMongo->isSubmitted() && $formMongo->isValid()) {
            $userDoc->setPassword($passwordHasher->hashPassword($userDoc, $formMongo->get('plainPassword')->getData()));
            $userDoc->setCreatedAt(new \DateTimeImmutable());
            $userDoc->setIsVerified(false);

            $this->documentManager->persist($userDoc);
            $this->documentManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email_mongo',
                $userDoc,
                (new TemplatedEmail())
                    ->from(new Address('anonymikuw@outlook.fr', 'Inscription Anonymik Mongo'))
                    ->to($userDoc->getEmail())
                    ->subject('Confirm your email (MongoDB)')
            );

            if ($mongoAuthenticator) {
                $userAuthenticator->authenticateUser($userDoc, $mongoAuthenticator, $request);
            }

            return $this->redirectToRoute('app_registration_request');
        }

        return $this->render('registration/register.html.twig', [
            'registrationFormSql' => $formSql->createView(),
            'registrationFormMongo' => $formMongo->createView(),
            'dataSource' => $this->dataSource,
        ]);
    }

    /**
     * Displays the confirmation request page after registration.
     *
     * This page instructs the user to check their email inbox
     * and click on the verification link sent by the system.
     *
     * @return Response Renders the confirmation request page.
     */
    #[Route('/register/check-email', name: 'app_registration_request')]
    public function request(): Response
    {
        return $this->render('registration/request.html.twig');
    }

    /**
     * Verifies the email for SQL (ORM) users.
     *
     * This method handles the confirmation link sent to ORM users,
     * updates their verification status, assigns the `ROLE_CLIENT` role if missing,
     * and automatically authenticates them.
     *
     * @param Request $request Current HTTP request containing verification link.
     * @param TranslatorInterface $translator Service for translating error messages.
     * @param UserAuthenticatorInterface $userAuthenticator Service for authenticating users.
     * @param OrmAuthenticator|null $ormAuthenticator Custom authenticator for ORM users.
     * @param UserRepository $userRepository Repository for fetching ORM users.
     * @param EntityManagerInterface $entityManager Doctrine ORM entity manager.
     *
     * @return Response Redirects user after successful or failed verification.
     */
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator,
        UserAuthenticatorInterface $userAuthenticator,
        ?OrmAuthenticator $ormAuthenticator,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ): Response {
        $id = $request->query->get('id');
        if (!$id) {
            $this->addFlash('verify_email_error', 'Invalid link.');
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('verify_email_error', 'User not found.');
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        if (!in_array('ROLE_CLIENT', $user->getRoles(), true)) {
            $user->setRoles([...$user->getRoles(), 'ROLE_CLIENT']);
        }

        $entityManager->flush();

        $tokenStorage->setToken(null); 
        $session->invalidate();

        $this->addFlash('success', 'Your email has been verified.');
        return $this->redirectToRoute('app_home');
    }
    /**
     * Verifies the email for MongoDB (ODM) users.
     *
     * This method handles the confirmation link sent to MongoDB users,
     * updates their verification status, assigns the `ROLE_CLIENT` role if missing,
     * and automatically authenticates them.
     *
     * @param Request $request Current HTTP request containing verification link.
     * @param TranslatorInterface $translator Service for translating error messages.
     * @param UserAuthenticatorInterface $userAuthenticator Service for authenticating users.
     * @param MongoAuthenticator|null $mongoAuthenticator Custom authenticator for MongoDB users.
     *
     * @return Response Redirects user after successful or failed verification.
     */
    #[Route('/verify/email-mongo', name: 'app_verify_email_mongo')]
    public function verifyEmailMongo(
        Request $request,
        TranslatorInterface $translator,
        UserAuthenticatorInterface $userAuthenticator,
        ?MongoAuthenticator $mongoAuthenticator,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session

    ): Response {
        $id = $request->query->get('id');
        if (!$id) {
            $this->addFlash('verify_email_error', 'Invalid or expired link.');
            return $this->redirectToRoute('app_register');
        }

        $userDoc = $this->userRepositoryMongo->find($id);
        if (!$userDoc) {
            $this->addFlash('verify_email_error', 'MongoDB user not found.');
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $userDoc);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        if (!in_array('ROLE_CLIENT', $userDoc->getRoles(), true)) {
            $userDoc->setRoles([...$userDoc->getRoles(), 'ROLE_CLIENT']);
        }

        $this->documentManager->flush();

        $tokenStorage->setToken(null); 
        $session->invalidate();

        if ($mongoAuthenticator) {
            $userAuthenticator->authenticateUser($userDoc, $mongoAuthenticator, $request);
        }

        $this->addFlash('success', 'Your MongoDB email has been verified.');
        return $this->redirectToRoute('app_home');
    }
}