<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Document\PaymentDocument;
use App\Entity\Command;
use App\Document\CommandDocument;
use App\Entity\Certification;
use App\Document\CertificationDocument;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Document\UserDocument;
use App\Form\UserFormType;
use App\Form\UserFormTypeMongo;
use App\Repository\ThemeRepository;
use App\Entity\Theme;
use App\Document\ThemeDocument;
use App\Form\ThemeFormType;
use App\Form\ThemeFormTypeMongo;
use App\Entity\Course;
use App\Document\CourseDocument;
use App\Form\CourseFormType;
use App\Form\CourseFormTypeMongo;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Entity\Lesson;
use App\Document\LessonDocument;
use App\Form\LessonFormType;
use App\Form\LessonFormTypeMongo;
use App\Repository\PaymentRepository;
use App\Repository\PaymentRepositoryMongo;
use App\Repository\CommandRepository;
use App\Repository\CommandRepositoryMongo;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class AdminController
 *
 * Controller responsible for administrative tasks such as managing users (ORM and MongoDB),
 * editing, adding, and deleting users, and handling form submissions in the admin dashboard.
 *
 * @package App\Controller
 */
class AdminController extends AbstractController
{
    /**
     * @var string Indicates which data source to use: 'orm', 'mongo', or 'both'
     */
    private string $dataSource;

    /**
     * AdminController constructor.
     *
     * @param ParameterBagInterface $params Service to access app parameters.
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Admin dashboard and user management page.
     *
     * Handles listing all users, adding new users, editing existing users, and deleting users.
     * Supports both Doctrine ORM (MySQL/PostgreSQL) and MongoDB ODM, including synchronization
     * between the two if both are used.
     *
     * @Route("/admin", name="app_admin", methods={"GET", "POST"})
     *
     * @param Request $request Symfony HTTP Request object containing POST/GET data.
     * @param ManagerRegistry|null $doctrine Doctrine service for ORM entity management.
     * @param DocumentManager|null $documentManager Doctrine MongoDB ODM document manager.
     * @param CourseRepository|null $courseRepository Repository for Course entities.
     * @param LessonRepository|null $lessonRepository Repository for Lesson entities.
     * @param UserRepository|null $userRepository Repository for User entities.
     * @param ThemeRepository|null $themeRepository Repository for Theme entities.
     * @param PaymentRepository|null $paymentRepository Repository for Payment entities.
     * @param CommandRepository|null $commandRepository Repository for Command entities.
     * @param UserPasswordHasherInterface $passwordHasher Service to hash user passwords.
     *
     * @return Response Symfony HTTP Response object with rendered admin dashboard.
     */
    #[Route('/admin', name: 'app_admin', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ?ManagerRegistry $doctrine,
        ?DocumentManager $documentManager,
        ?CourseRepository $courseRepository,
        ?LessonRepository $lessonRepository, 
        ?UserRepository $userRepository,
        ?ThemeRepository $themeRepository,
        ?PaymentRepository $paymentRepository,
        ?CommandRepository $commandRepository, 
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        // Ensure user has admin privileges
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    $storage = $this->dataSource;
    $em = $doctrine->getManager();
    $users = [];
    if ($storage === 'orm' || $storage === 'both') {
        // Fetch all ORM users
        $users = $userRepository->findAll();
    }

    $usersMongo = [];
    if (isset($documentManager) && $documentManager !== null) {
        // Fetch all MongoDB users
        $usersMongo = $documentManager->getRepository(UserDocument::class)->findAll();
    }

    $editForms = []; // Placeholder for edit form views

    if ($request->isMethod('POST')) {
        $postData = $request->request->all();
        $formType = $postData['user_form']['form_type'] ?? null;
        $editId = $postData['user_form']['edit_id'] ?? null;

        // Handle user deletion
        if ($request->request->has('delete_id')) {
            $deleteId = $request->request->get('delete_id');
            $storage = $request->request->get('db');
            $submittedToken = $request->request->get('_token');

            // Validate CSRF token
            if ($this->isCsrfTokenValid('delete' . $deleteId, $submittedToken)) {
                $deleted = false;

                /**
                 * Delete user from MongoDB and clean references.
                 */
                if (($storage === 'mongo' || $storage === 'both') && isset($documentManager)) {
                    $userToDeleteMongo = $documentManager->getRepository(UserDocument::class)->find($deleteId);

                    if ($userToDeleteMongo) {
                        // List of MongoDB documents and fields referencing the user
                        $documentsToClean = [
                            [LessonDocument::class, ['createdBy', 'updatedBy']],
                            [CourseDocument::class, ['createdBy', 'updatedBy']],
                            [ThemeDocument::class, ['createdBy', 'updatedBy']],
                            [CertificationDocument::class, ['createdBy', 'updatedBy', 'user']],
                            [CommandDocument::class, ['createdBy', 'updatedBy', 'user']],
                            [PaymentDocument::class, ['createdBy', 'updatedBy']],
                        ];

                        // Nullify references to the user in related documents
                        foreach ($documentsToClean as [$docClass, $fields]) {
                            foreach ($fields as $field) {
                                $criteria = [$field => $userToDeleteMongo];
                                $docs = $documentManager->getRepository($docClass)->findBy($criteria);

                                foreach ($docs as $doc) {
                                    $setter = 'set' . ucfirst($field);
                                    if (method_exists($doc, $setter)) {
                                        $doc->$setter(null);
                                        $documentManager->persist($doc);
                                    }
                                }
                            }
                        }

                        $documentManager->flush();
                        $documentManager->remove($userToDeleteMongo);
                        $documentManager->flush();
                        $deleted = true;
                    }
                }

                /**
                 * Delete user from ORM and clean references.
                 */
                if ($storage === 'orm' || $storage === 'both' || $storage === 'mongo') {
                    $userToDeleteOrm = null;

                    // Try to find ORM user by ID
                    if (($storage === 'orm' || $storage === 'both') && isset($userRepository) && isset($em)) {
                        $userToDeleteOrm = $userRepository->find((int) $deleteId);
                    }

                    // Fallback: match MongoDB user by email
                    if (!$userToDeleteOrm && isset($documentManager)) {
                        $userToDeleteMongo = $documentManager->getRepository(UserDocument::class)->find($deleteId);
                        if ($userToDeleteMongo) {
                            $userToDeleteOrm = $userRepository->findOneBy(['email' => $userToDeleteMongo->getEmail()]);
                        }
                    }

                    if ($userToDeleteOrm) {
                        // List of ORM entities and fields referencing the user
                        $entitiesToClean = [
                            [Lesson::class, ['createdBy', 'updatedBy']],
                            [Course::class, ['createdBy', 'updatedBy']],
                            [Theme::class, ['createdBy', 'updatedBy']],
                            [Certification::class, ['createdBy', 'updatedBy', 'user']],
                            [Command::class, ['createdBy', 'updatedBy', 'user']],
                            [Payment::class, ['createdBy', 'updatedBy']],
                        ];

                        // Nullify references to the user in related entities
                        foreach ($entitiesToClean as [$entityClass, $fields]) {
                            foreach ($fields as $field) {
                                $criteria = [$field => $userToDeleteOrm];
                                $items = $em->getRepository($entityClass)->findBy($criteria);

                                foreach ($items as $item) {
                                    $setter = 'set' . ucfirst($field);
                                    if (method_exists($item, $setter)) {
                                        $item->$setter(null);
                                        $em->persist($item);
                                    }
                                }
                            }
                        }

                        $em->flush();
                        $em->remove($userToDeleteOrm);
                        $em->flush();
                        $deleted = true;
                    }
                }

                // Flash message based on deletion result
                $this->addFlash(
                    $deleted ? 'success' : 'error',
                    $deleted ? 'User deleted from MongoDB and/or ORM.' : 'User not found.'
                );
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
            }

            // Redirect to admin dashboard after deletion
            return $this->redirectToRoute('app_admin');
        }
    /**
     * Handles user editing logic for ORM storage.
     *
     * Synchronizes changes with MONGO if a matching user is found by email.
     */
        if (
            $formType === 'edit' &&
            $request->request->get('storage') === 'orm' &&
            isset($userRepository) &&
            isset($em)
        ) {
            // Retrieve the ORM user to edit
            $userToEdit = $userRepository->find((int) $editId);

            if ($userToEdit) {
                // Create and handle the Symfony form for ORM user
                $form = $this->createForm(UserFormType::class, $userToEdit, [
                    'action' => $this->generateUrl('app_admin'),
                    'method' => 'POST',
                    'em' => $em,
                    'is_edit' => true,
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    // Hash and set the new password if provided
                    $plainPassword = $form->get('password')->getData();
                    if (!empty($plainPassword)) {
                        $hashedPassword = $passwordHasher->hashPassword($userToEdit, $plainPassword);
                        $userToEdit->setPassword($hashedPassword);
                    }

                    // Persist changes to ORM
                    $em->flush();

                    // Synchronize with MongoDB if matching user exists
                    if (isset($documentManager)) {
                        $userToEditMongo = $documentManager
                            ->getRepository(UserDocument::class)
                            ->findOneBy(['email' => $userToEdit->getEmail()]);

                        if ($userToEditMongo) {
                            $userToEditMongo->setEmail($userToEdit->getEmail());
                            $userToEditMongo->setIsVerified($userToEdit->isVerified());

                            if (!empty($plainPassword)) {
                                $userToEditMongo->setPassword($hashedPassword);
                            }

                            $documentManager->flush();
                        }
                    }

                    // Notify success and redirect
                    $this->addFlash('success', 'User edited (ORM + Mongo if matched)!');
                    return $this->redirectToRoute('app_admin');
                }

                // Store the form view for rendering
                $editForms['orm'][$editId] = $form->createView();
            }
        }

        /**
         * Handles user editing logic for MongoDB storage.
         *
         * Synchronizes changes with ORM if a matching user is found by email.
         */
        if (
            $formType === 'edit' &&
            $request->request->get('storage') === 'mongo' &&
            isset($documentManager)
        ) {
            // Retrieve the MongoDB user to edit
            $userToEdit = $documentManager->getRepository(UserDocument::class)->find($editId);

            if ($userToEdit) {
                // Create and handle the Symfony form for MongoDB user
                $form = $this->createForm(UserFormTypeMongo::class, $userToEdit, [
                    'action' => $this->generateUrl('app_admin'),
                    'method' => 'POST',
                    'is_edit' => true,
                ]);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    // Hash and set the new password if provided
                    $plainPassword = $form->get('password')->getData();
                    if (!empty($plainPassword)) {
                        $hashedPassword = $passwordHasher->hashPassword($userToEdit, $plainPassword);
                        $userToEdit->setPassword($hashedPassword);
                    }

                    // Persist changes to MongoDB
                    $documentManager->flush();

                    // Synchronize with ORM if matching user exists
                    if (($storage === 'orm' || $storage === 'both') && isset($userRepository) && isset($em)) {
                        $userToEditOrm = $userRepository->findOneBy(['email' => $userToEdit->getEmail()]);

                        if ($userToEditOrm) {
                            $userToEditOrm->setEmail($userToEdit->getEmail());
                            $userToEditOrm->setIsVerified($userToEdit->isVerified());

                            if (!empty($plainPassword)) {
                                $userToEditOrm->setPassword($hashedPassword);
                            }

                            $em->flush();
                        }
                    }

                    // Notify success and redirect
                    $this->addFlash('success', 'User edited (Mongo + ORM if matched)!');
                    return $this->redirectToRoute('app_admin');
                }

                // Store the form view for rendering
                $editForms['mongo'][$editId] = $form->createView();
            }
        }
        /**
         * Handles user creation and form rendering for both ORM and MongoDB storage.
         *
         */
            if (
            $formType === 'add' &&
            $request->request->get('storage') === 'orm' &&
            isset($em)
        ) {
            $newUser = new User();

            // Create and handle the Symfony form for new ORM user
            $form = $this->createForm(UserFormType::class, $newUser, [
                'action' => $this->generateUrl('app_admin'),
                'method' => 'POST',
                'em' => $em,
                'is_edit' => false,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $plainPassword = $form->get('password')->getData();
                if (!empty($plainPassword)) {
                    $hashedPassword = $passwordHasher->hashPassword($newUser, $plainPassword);
                    $newUser->setPassword($hashedPassword);
                }

                $em->persist($newUser);
                $em->flush();

                $this->addFlash('success', 'User added!');
                return $this->redirectToRoute('app_admin');
            }

            $editForms['add'] = $form->createView();
        }
    }
        // Render edit forms for all ORM users
        if ($storage === 'orm' || $storage === 'both') {
            foreach ($users as $user) {
                if (!isset($editForms['orm'][$user->getId()])) {
                    $form = $this->createForm(UserFormType::class, $user, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $editForms['orm'][$user->getId()] = $form->createView();
                }
            }
        }

        // Render edit forms for all MongoDB users
        if (isset($documentManager)) {
            foreach ($usersMongo as $user) {
                if (!isset($editForms['mongo'][$user->getId()])) {
                    $form = $this->createForm(UserFormTypeMongo::class, $user, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'is_edit' => true,
                    ]);
                    $editForms['mongo'][(string) $user->getId()] = $form->createView();
                }
            }
        }

        // Prepare add form for ORM user
        $addFormOrm = null;
        if ($storage === 'orm' || $storage === 'both') {
            $newUserOrm = new User();
            $addFormOrm = $this->createForm(UserFormType::class, $newUserOrm, ['em' => $em]);
            $addFormOrm->handleRequest($request);
        }

        // Prepare add form for MongoDB user
        $addFormMongo = null;
        if (isset($documentManager)) {
            $newUserMongo = new UserDocument();
            $addFormMongo = $this->createForm(UserFormTypeMongo::class, $newUserMongo);
        }

        // Handle form submission for MongoDB add form
        if ($addFormMongo) {
            $addFormMongo->handleRequest($request);
        }

        // Handle user creation via unified POST form (ORM or Mongo)
        if ($request->isMethod('POST') && $request->request->has('add_mode')) {
            $storage = $request->request->get('storage', 'orm');

            // Create ORM user
            if ($storage === 'orm' && $addFormOrm->isSubmitted() && $addFormOrm->isValid()) {
                $plainPassword = $addFormOrm->get('password')->getData();
                if (!empty($plainPassword)) {
                    $hashedPassword = $passwordHasher->hashPassword($newUserOrm, $plainPassword);
                    $newUserOrm->setPassword($hashedPassword);
                }

                $em->persist($newUserOrm);
                $em->flush();

                $this->addFlash('success', 'User ORM added !');
                return $this->redirectToRoute('app_admin');
            }

            // Create MongoDB user
            if ($storage === 'mongo' && $addFormMongo && $addFormMongo->isSubmitted() && $addFormMongo->isValid()) {
                $plainPassword = $addFormMongo->get('password')->getData();
                if (!empty($plainPassword)) {
                    $hashedPassword = $passwordHasher->hashPassword($newUserMongo, $plainPassword);
                    $newUserMongo->setPassword($hashedPassword);
                }

                $documentManager->persist($newUserMongo);
                $documentManager->flush();

                $this->addFlash('success', 'User Mongo added !');
                return $this->redirectToRoute('app_admin');
            }
        }
    
    $themes  = [];
    if ($storage === 'orm' || $storage === 'both') {
        // Fetch all ORM themes
        $themes  = $themeRepository->findAll();
    }

    $themesMongo= [];
    if (isset($documentManager) && $documentManager !== null) {
        // Fetch all MongoDB themes
        $themesMongo = $documentManager->getRepository(ThemeDocument::class)->findAll();
    }

    $editFormsTheme = []; // Placeholder for edit form views

    if ($request->isMethod('POST')) {
        $postData = $request->request->all();
        $formType = $postData['theme_form']['form_type'] ?? null;
        $editThemeId = $postData['theme_form']['edit_theme_id'] ?? null;
            
        // Handle theme deletion
        if ($request->request->has('delete_theme_id')) {
            $deleteThemeId = $request->request->get('delete_theme_id');
            $storage = $request->request->get('db');
            $submittedToken = $request->request->get('_token');
            
            // Validate CSRF token
            if ($this->isCsrfTokenValid('delete' . $deleteThemeId, $submittedToken)) {
                $deleted = false;

                /**
                 * Delete theme from MongoDB.
                 */
                if (($storage === 'mongo' || $storage === 'both')&& isset($documentManager) && $documentManager !== null) {
                    $themeToDeleteMongo = $documentManager->getRepository(ThemeDocument::class)->find($deleteThemeId);
                    
                    if ($themeToDeleteMongo) {
                        foreach ($themeToDeleteMongo->getCourses() as $course) {
                            foreach ($course->getLessons() as $lesson) {
                                $documentManager->remove($lesson);
                            }
                            $documentManager->remove($course);
                        }
                        $documentManager->remove($themeToDeleteMongo);
                        $documentManager->flush();
                        $deleted = true;
                    }
                }
                
                /**
                 * Delete theme from ORM.
                 */
                if ($storage === 'orm' || $storage === 'both' || $storage === 'mongo') {
                    $themeToDeleteOrm = null;
                    
                    // Try to find ORM theme by ID
                    if (($storage === 'orm' || $storage === 'both')&& isset($userRepository) && isset($em)) {
                        $themeToDeleteOrm = $themeRepository->find((int) $deleteThemeId);
                    }

                    // Fallback: match MongoDB theme by name
                    if (!$themeToDeleteOrm && isset($documentManager) && $documentManager !== null) {
                        $themeToDeleteMongo = $documentManager->getRepository(ThemeDocument::class)->find($deleteThemeId);
                        if ($themeToDeleteMongo) {
                            $themeToDeleteOrm = $themeRepository->findOneBy(['name' => $themeToDeleteMongo->getName()]);
                        }
                    }
                    
                    if ($themeToDeleteOrm) {
                        foreach ($themeToDeleteOrm->getCourses() as $course) {
                            foreach ($course->getLessons() as $lesson) {
                                $em->remove($lesson);
                            }
                            $em->remove($course);
                        }
                        $em->remove($themeToDeleteOrm);
                        $em->flush();
                        $deleted = true;
                    }
                }

                // Flash message based on deletion result
                if ($deleted) {
                    $this->addFlash('success', 'Theme supprimé dans MongoDB et/ou ORM.');
                } else {
                    $this->addFlash('error', 'Theme non trouvé.');
                }

            } else {
                $this->addFlash('error', 'Token CSRF invalide.');
            }

            // Redirect to admin dashboard after deletion
            return $this->redirectToRoute('app_admin');
        }
    
        /**
         * Handles theme editing logic for ORM storage.
         *
         * Synchronizes changes with MONGO if a matching theme is found by email.
         */

        if (
            $formType === 'edit' && 
            $request->request->get('storage') === 'orm' && 
            isset($userRepository) && 
            isset($em)
        ) {
                // Retrieve the ORM theme to edit
                $themeToEdit = $themeRepository->find((int) $editThemeId);

                if ($themeToEdit) {
                    // Create and handle the Symfony form for ORM theme
                    $formTheme = $this->createForm(ThemeFormType::class, $themeToEdit, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $formTheme->handleRequest($request);

                    if ($formTheme->isSubmitted() && $formTheme->isValid()) {
                        $themeToEdit->setUpdatedBy($user);

                        // Persist changes to ORM
                        $em->flush();

                        // Synchronize with MongoDB if matching theme exists
                        if (isset($documentManager) && $documentManager !== null) {
                            $themeToEditMongo = $documentManager->getRepository(ThemeDocument::class)->findOneBy(['name' => $themeToEdit->getName()]);
                            if ($themeToEditMongo) {
                            }
                        }

                        // Notify success and redirect
                        $this->addFlash('success', 'Theme edited (ORM + Mongo if matched)!');
                        return $this->redirectToRoute('app_admin');
                    }
                    
                    // Store the form view for rendering
                    $editFormsTheme['orm'][$editThemeId] = $formTheme->createView();
                }
            }
            /**
             * Handles theme editing logic for MongoDB storage.
             *
             * Synchronizes changes with ORM if a matching theme is found by email.
             */

            if (($formType === 'edit' &&
                $request->request->get('storage') === 'mongo') &&
                isset($documentManager) && 
                $documentManager !== null
                ) {

                // Retrieve the MongoDB theme to edit
                $themeToEdit = $documentManager->getRepository(ThemeDocument::class)->find($editThemeId);

                if ($themeToEdit) {
                    // Create and handle the Symfony form for MongoDB theme
                    $formTheme = $this->createForm(ThemeFormTypeMongo::class, $themeToEdit, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'is_edit' => true,
                    ]);
                    $formTheme->handleRequest($request);

                    if ($formTheme->isSubmitted() && $formTheme->isValid()) {
                        $themeToEdit->setUpdatedBy($user);

                        // Persist changes to MongoDB
                        $documentManager->flush();

                        // Synchronize with ORM if matching theme exists
                        if (($storage === 'orm' || $storage === 'both') && isset($userRepository) && isset($em)) {
                            $themeToEditOrm = $themeRepository->findOneBy(['name' => $themeToEdit->getName()]);
                            
                            if ($themeToEditOrm) {
                                $themeToEditOrm->setName($themeToEdit->getName());
                                $em->flush();
                            }
                        }

                        // Notify success and redirect
                        $this->addFlash('success', 'Theme edited (Mongo + ORM if matched)!');
                        return $this->redirectToRoute('app_admin');
                    }

                    // Store the form view for rendering
                    $editFormsTheme['mongo'][$editThemeId] = $formTheme->createView();
                }
            }
            /**
             * Handles theme creation and form rendering for both ORM and MongoDB storage.
             *
             */
                if (
                    $formType === 'add' && 
                    $request->request->get('storage') === 'orm' && 
                    isset($em)
                ) {
                    $newTheme = new Theme();

                    // Create and handle the Symfony form for new ORM theme
                    $formTheme = $this->createForm(ThemeFormType::class, $newTheme, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => false,
                    ]);
                    $formTheme->handleRequest($request);

                    if ($formTheme->isSubmitted() && $formTheme->isValid()) {
                        
                        $em->persist($newTheme);
                        $em->flush();

                        $this->addFlash('success', 'Theme added!');
                        return $this->redirectToRoute('app_admin');
                    }

                    $editFormsTheme['add'] = $formTheme->createView(); 
                }
    }   
        // Render edit forms for all ORM themes
        if ($storage === 'orm' || $storage === 'both') {
            foreach ($themes as $theme) {
                if (!isset($editFormsTheme['orm'][$theme->getId()])) {
                    $formTheme = $this->createForm(ThemeFormType::class, $theme, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $editFormsTheme['orm'][$theme->getId()] = $formTheme->createView();
                }
            }
        }

        // Render edit forms for all MongoDB themes
        if (isset($documentManager) && $documentManager !== null) {
            foreach ($themesMongo as $theme) {
                if (!isset($editFormsTheme['mongo'][$theme->getId()])) {
                    $formTheme = $this->createForm(ThemeFormTypeMongo::class, $theme, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'is_edit' => true,
                    ]);
                    $editFormsTheme['mongo'][(string) $theme->getId()] = $formTheme->createView();
                }
            }
        }
        
        // Prepare add form for ORM theme
        $themeAddFormOrm = null;
            if ($storage === 'orm' || $storage === 'both') {
                $newThemeOrm = new Theme();
                $themeAddFormOrm = $this->createForm(ThemeFormType::class, $newThemeOrm, ['em' => $em]);
                $themeAddFormOrm->handleRequest($request);
            }

        // Prepare add form for MongoDB theme
        $themeAddFormMongo = null;
        if (isset($documentManager) && $documentManager !== null) {
            $newThemeMongo = new ThemeDocument();
            $themeAddFormMongo = $this->createForm(ThemeFormTypeMongo::class, $newThemeMongo);
        }
            
        // Handle form submission for MongoDB add form
        if ($themeAddFormMongo) {
            $themeAddFormMongo->handleRequest($request);
        }

        // Handle theme creation via unified POST form (ORM or Mongo)
        if ($request->isMethod('POST') && $request->request->has('add_theme')) {
            $storage = $request->request->get('storage', 'orm');

            // Create ORM theme
            if ($storage === 'orm' && $themeAddFormOrm->isSubmitted() && $themeAddFormOrm->isValid()) {

                $user->addCreateTheme($newThemeOrm); 
                $em->persist($newThemeOrm);
                $em->flush();

                $this->addFlash('success', 'Theme ORM added !');
                return $this->redirectToRoute('app_admin');
            }
                
            // Create MongoDB theme
            elseif ($storage === 'mongo' && $themeAddFormMongo->isSubmitted() && $themeAddFormMongo->isValid()) {
                    
                $user->addCreateTheme($newThemeMongo);
                $documentManager->persist($newThemeMongo);
                $documentManager->flush();

                $this->addFlash('success', 'Theme Mongo added !');
                return $this->redirectToRoute('app_admin');
            }
        }
   
    $courses = [];
    if ($storage === 'orm' || $storage === 'both') {
        // Fetch all ORM courses
        $courses = $courseRepository->findAll();
    }
        
    $coursesMongo = [];
    if (isset($documentManager) && $documentManager !== null) {
        // Fetch all MongoDB courses
        $coursesMongo = $documentManager->getRepository(CourseDocument::class)->findAll();
    }

    $editFormsCourse = []; // Placeholder for edit form views

    if ($request->isMethod('POST')) {
        $postData = $request->request->all();
        $formType = $postData['course_form']['form_type'] ?? null;
        $editCourseId = $postData['course_form']['edit_course_id'] ?? null;

        // Handle course deletion
        if ($request->request->has('delete_course_id')) {
            $deleteCourseId = $request->request->get('delete_course_id');
            $storage = $request->request->get('db');
            $submittedToken = $request->request->get('_token');
            
            // Validate CSRF token
            if ($this->isCsrfTokenValid('delete' . $deleteCourseId, $submittedToken)) {
                $deleted = false;

                /**
                 * Delete course from MongoDB.
                 */
                if (($storage === 'mongo' || $storage === 'both')&& isset($documentManager) && $documentManager !== null) {
                    $courseToDeleteMongo = $documentManager->getRepository(CourseDocument::class)->find($deleteCourseId);
                    if ($courseToDeleteMongo) {
                            foreach ($courseToDeleteMongo->getLessons() as $lesson) {
                                $documentManager->remove($lesson);
                            }
                        $documentManager->remove($courseToDeleteMongo);
                        $documentManager->flush();
                        $deleted = true;
                    }
                }

                /**
                 * Delete course from ORM.
                 */
                if ($storage === 'orm' || $storage === 'both' || $storage === 'mongo') {
                    $courseToDeleteOrm = null;

                    // Try to find ORM course by ID
                    if (($storage === 'orm' || $storage === 'both')&& isset($userRepository) && isset($em)) {
                        $courseToDeleteOrm = $courseRepository->find((int) $deleteCourseId);
                    }

                    // Fallback: match MongoDB course by title
                    if (!$courseToDeleteOrm && isset($documentManager) && $documentManager !== null) {
                        $courseToDeleteMongo = $documentManager->getRepository(CourseDocument::class)->find($deleteCourseId);
                        if ($courseToDeleteMongo) {
                            $courseToDeleteOrm = $courseRepository->findOneBy(['title' => $courseToDeleteMongo->getTitle()]);
                        }
                    }

                    if ($courseToDeleteOrm) {
                        foreach ($courseToDeleteOrm->getLessons() as $lesson) {
                                $em->remove($lesson);
                            }
                        $em->remove($courseToDeleteOrm);
                        $em->flush();
                        $deleted = true;
                    }
                }

                // Flash message based on deletion result
                if ($deleted) {
                    $this->addFlash('success', 'Course supprimé dans MongoDB et/ou ORM.');
                } else {
                    $this->addFlash('error', 'Course non trouvé.');
                }

            } else {
                $this->addFlash('error', 'Token CSRF invalide.');
            }
            // Redirect to admin dashboard after deletion
            return $this->redirectToRoute('app_admin');
        }
    
        /**
         * Handles course editing logic for ORM storage.
         *
         * Synchronizes changes with MONGO if a matching course is found by email.
         */

        if (
            $formType === 'edit' && 
            $request->request->get('storage') === 'orm' && 
            isset($userRepository) && 
            isset($em)
        ) {
                // Retrieve the ORM course to edit
                $courseToEdit = $courseRepository->find((int) $editCourseId);

                if ($courseToEdit) {
                    // Create and handle the Symfony form for ORM course
                    $formCourse = $this->createForm(CourseFormType::class, $courseToEdit, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $formCourse->handleRequest($request);

                    if ($formCourse->isSubmitted() && $formCourse->isValid()) {
                        $courseToEdit->setUpdatedBy($user);
                        
                        // Persist changes to ORM
                        $em->flush();

                        // Synchronize with MongoDB if matching course exists
                        if (isset($documentManager) && $documentManager !== null) {
                            $courseToEditMongo = $documentManager->getRepository(CourseDocument::class)->findOneBy(['title' => $courseToEdit->getTitle()]);
                            if ($courseToEditMongo) {
                                $courseToEditMongo->setTitle($courseToEdit->getTitle());
                                $documentManager->flush();
                            }
                        }

                        // Notify success and redirect
                        $this->addFlash('success', 'Course edited (ORM + Mongo if matched)!');
                        return $this->redirectToRoute('app_admin');
                    }

                    // Store the form view for rendering
                    $editFormsCourse['orm'][$editCourseId] = $formCourse->createView();
                }
            }

            /**
             * Handles course editing logic for MongoDB storage.
             *
             * Synchronizes changes with ORM if a matching course is found by email.
             */

            if ($formType === 'edit' && 
            $request->request->get('storage') === 'mongo' && 
            isset($documentManager) && 
            $documentManager !== null
            ) {

                // Retrieve the MongoDB course to edit
                $courseToEdit = $documentManager->getRepository(CourseDocument::class)->find($editCourseId);

                if ($courseToEdit) {
                    // Create and handle the Symfony form for MongoDB course
                    $formCourse = $this->createForm(CourseFormTypeMongo::class, $courseToEdit, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'is_edit' => true,
                    ]);
                    $formCourse->handleRequest($request);

                    if ($formCourse->isSubmitted() && $formCourse->isValid()) {
                        $courseToEdit->setUpdatedBy($user);

                        // Persist changes to MongoDB
                        $documentManager->flush();

                        // Synchronize with ORM if matching course exists
                        if (($storage === 'orm' || $storage === 'both') && isset($userRepository) && isset($em)) {
                        $courseToEditOrm = $courseRepository->findOneBy(['title' => $courseToEdit->getTitle()]);
                            if ($courseToEditOrm) {
                                $courseToEditOrm->setTitle($courseToEdit->getTitle());
                                $em->flush();
                            }
                        }
                        // Notify success and redirect
                        $this->addFlash('success', 'Course edited (Mongo + ORM if matched)!');
                        return $this->redirectToRoute('app_admin');
                    }
                    // Store the form view for rendering
                    $editFormsCourse['mongo'][$editCourseId] = $formCourse->createView();
                }
            }
            /**
             * Handles course creation and form rendering for both ORM and MongoDB storage.
             *
             */
                if (
                    $formType === 'add' && 
                    $request->request->get('storage') === 'orm' && 
                    isset($em)
                ) {
                    $newCourse = new Course();

                    // Create and handle the Symfony form for new ORM course
                    $formCourse = $this->createForm(CourseFormType::class, $newCourse, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => false,
                    ]);
                    $formCourse->handleRequest($request);

                    if ($formCourse->isSubmitted() && $formCourse->isValid()) {
                        $em->persist($newCourse);
                        $em->flush();
                        $this->addFlash('success', 'Course added!');
                        return $this->redirectToRoute('app_admin');
                    }

                    $editFormsCourse['add'] = $formCourse->createView(); 
                }
    }            
        // Render edit forms for all ORM courses
        if ($storage === 'orm' || $storage === 'both') {
            foreach ($courses as $course) {
                if (!isset($editFormsCourse['orm'][$course->getId()])) {
                    $formCourse = $this->createForm(CourseFormType::class, $course, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $editFormsCourse['orm'][$course->getId()] = $formCourse->createView();
                }
            }
        }   

        // Render edit forms for all MongoDB courses
        if (isset($documentManager) && $documentManager !== null) {
            foreach ($coursesMongo as $course) {
                if (!isset($editFormsCourse['mongo'][$course->getId()])) {
                    $formCourse = $this->createForm(CourseFormTypeMongo::class, $course, [
                            'action' => $this->generateUrl('app_admin'),
                            'method' => 'POST',
                            'is_edit' => true,
                        ]);
                        $editFormsCourse['mongo'][(string) $course->getId()] = $formCourse->createView();
                    }
                }
            }

            // Prepare add form for ORM course
            $courseAddFormOrm = null;
            if ($storage === 'orm' || $storage === 'both') {
                $newCourseOrm = new Course();
                $courseAddFormOrm = $this->createForm(CourseFormType::class, $newCourseOrm, ['em' => $em]);
                $courseAddFormOrm->handleRequest($request);
            }

            // Prepare add form for MongoDB course
            $courseAddFormMongo  = null;
            if (isset($documentManager) && $documentManager !== null) {
                $newCourseMongo = new CourseDocument();
                $courseAddFormMongo  = $this->createForm(CourseFormTypeMongo::class, $newCourseMongo);
            }

            // Handle form submission for MongoDB add form
            if ($courseAddFormMongo) {
            $courseAddFormMongo->handleRequest($request);
            }

            // Handle course creation via unified POST form (ORM or Mongo)
            if ($request->isMethod('POST') && $request->request->has('add_course')) {
                $storage = $request->request->get('storage', 'orm');

                // Create ORM course
                if ($storage === 'orm' && $courseAddFormOrm->isSubmitted() && $courseAddFormOrm->isValid()) {

                    $user->addCreateCourse($newCourseOrm);
                    $em->persist($newCourseOrm);
                    $em->flush();

                    $this->addFlash('success', 'Course ORM added !');
                    return $this->redirectToRoute('app_admin');
                } 

                // Create MongoDB course
                elseif ($storage === 'mongo' && $courseAddFormMongo->isSubmitted() && $courseAddFormMongo->isValid()) {
                    
                    $user->addCreateCourse($newCourseMongo);
                    $documentManager->persist($newCourseMongo);
                    $documentManager->flush();

                    $this->addFlash('success', 'Course Mongo added !');
                    return $this->redirectToRoute('app_admin');
                }
            }
        

        $lessons = [];
        if ($storage === 'orm' || $storage === 'both') {
            // Fetch all ORM lessons
            $lessons = $lessonRepository->findAll();
        }

        $lessonsMongo = [];
        if (isset($documentManager) && $documentManager !== null) {
            // Fetch all MongoDB lessons
            $lessonsMongo = $documentManager->getRepository(LessonDocument::class)->findAll();
        }

        $editFormsLesson = []; // Placeholder for edit form views

    if ($request->isMethod('POST')) {
            $postData = $request->request->all();
            $formType = $postData['lesson_form']['form_type'] ?? null;
            $editLessonId = $postData['lesson_form']['edit_lesson_id'] ?? null;

            // Handle lesson deletion
            if ($request->request->has('delete_lesson_id')) {
                $deleteLessonId = $request->request->get('delete_lesson_id');
                $storage = $request->request->get('db');
                $submittedToken = $request->request->get('_token');

            // Validate CSRF token
            if ($this->isCsrfTokenValid('delete' . $deleteLessonId, $submittedToken)) {
                $deleted = false;

                /**
                 * Delete lesson from MongoDB.
                 */
                if (($storage === 'mongo' || $storage === 'both')&& isset($documentManager) && $documentManager !== null) {
                    $lessonToDeleteMongo = $documentManager->getRepository(LessonDocument::class)->find($deleteLessonId);
                    if ($lessonToDeleteMongo) {
                        $documentManager->remove($lessonToDeleteMongo);
                        $documentManager->flush();
                        $deleted = true;
                    }
                }

                /**
                 * Delete lesson from ORM.
                 */
                if ($storage === 'orm' || $storage === 'both' || $storage === 'mongo')  {
                    $lessonToDeleteOrm = null;

                    // Try to find ORM lesson by ID
                    if (($storage === 'orm' || $storage === 'both')&& isset($userRepository) && isset($em)) {
                        $lessonToDeleteOrm = $lessonRepository->find((int) $deleteLessonId);
                    }

                    // Fallback: match MongoDB lesson by title
                    if (!$lessonToDeleteOrm && isset($documentManager) && $documentManager !== null) {
                        $lessonToDeleteMongo = $documentManager->getRepository(LessonDocument::class)->find($deleteLessonId);
                        if ($lessonToDeleteMongo) {
                            $lessonToDeleteOrm = $lessonRepository->findOneBy(['title' => $lessonToDeleteMongo->getTitle()]);
                        }
                    }

                    if ($lessonToDeleteOrm) {
                        $em->remove($lessonToDeleteOrm);
                        $em->flush();
                        $deleted = true;
                    }
                }   

                // Flash message based on deletion result
                if ($deleted) {
                    $this->addFlash('success', 'Course supprimé dans MongoDB et/ou ORM.');
                } else {
                    $this->addFlash('error', 'Course non trouvé.');
                }

            } else {
                $this->addFlash('error', 'Token CSRF invalide.');
            }
            // Redirect to admin dashboard after deletion
            return $this->redirectToRoute('app_admin');
        }
    
        /**
         * Handles lesson editing logic for ORM storage.
         *
         * Synchronizes changes with MONGO if a matching lesson is found by email.
         */

        if (
            $formType === 'edit' && 
            $request->request->get('storage') === 'orm'&& 
            isset($userRepository) && 
            isset($em)
        ) {
                // Retrieve the ORM lesson to edit
                $lessonToEdit = $lessonRepository->find((int) $editLessonId);

                if ($lessonToEdit) {
                    // Create and handle the Symfony form for ORM lesson
                    $formLesson = $this->createForm(LessonFormType::class, $lessonToEdit, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $formLesson->handleRequest($request);

                    if ($formLesson->isSubmitted() && $formLesson->isValid()) {
                        $this->handleVideoUploadMixed($formLesson, $lessonToEdit, true);
                        $lessonToEdit->setUpdatedBy($user);
                        // Persist changes to ORM
                        $em->flush();

                        // Synchronize with MongoDB if matching lesson exists
                        if (isset($documentManager) && $documentManager !== null) {
                            $lessonToEditMongo = $documentManager->getRepository(LessonDocument::class)->findOneBy(['title' => $lessonToEdit->getTitle()]);
                            if ($lessonToEditMongo) {
                                $lessonToEditMongo->setTitle($lessonToEdit->getTitle());
                                $documentManager->flush();
                            }
                        }

                        // Notify success and redirect
                        $this->addFlash('success', 'Lesson edited (ORM + Mongo if matched)!');
                        return $this->redirectToRoute('app_admin');
                    }

                    // Store the form view for rendering
                    $editFormsLesson['orm'][$editLessonId] = $formLesson->createView();
                }
            }

            /**
             * Handles lesson editing logic for MongoDB storage.
             *
             * Synchronizes changes with ORM if a matching lesson is found by email.
             */

            if ($formType === 'edit' &&
            $request->request->get('storage') === 'mongo' && 
            isset($documentManager) && 
            $documentManager !== null
            ) {
                
                // Retrieve the MongoDB lesson to edit
                $lessonToEdit = $documentManager->getRepository(LessonDocument::class)->find($editLessonId);

                if ($lessonToEdit) {
                    // Create and handle the Symfony form for MongoDB lesson
                    $formLesson = $this->createForm(LessonFormTypeMongo::class, $lessonToEdit, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'is_edit' => true,
                    ]);
                    $formLesson->handleRequest($request);

                    if ($formLesson->isSubmitted() && $formLesson->isValid()) {
                        $this->handleVideoUploadMixed($formLesson, $lessonToEdit, false);
                        $lessonToEdit->setUpdatedBy($user);
                        // Persist changes to MongoDB
                        $documentManager->flush();
                        
                        // Synchronize with ORM if matching lesson exists
                        if (($storage === 'orm' || $storage === 'both') && isset($userRepository) && isset($em)) {
                            $lessonToEditOrm = $lessonRepository->findOneBy(['title' => $lessonToEdit->getTitle()]);
                            if ($lessonToEditOrm) {
                                $lessonToEditOrm->setTitle($lessonToEdit->getTitle());
                                $em->flush();
                            }
                        }
                            // Notify success and redirect
                            $this->addFlash('success', 'Lesson edited (Mongo + ORM if matched)!');
                            return $this->redirectToRoute('app_admin');
                    }
                    // Store the form view for rendering
                    $editFormsLesson['mongo'][$editLessonId] = $formLesson->createView();
                }
            }
            /**
             * Handles lesson creation and form rendering for both ORM and MongoDB storage.
             *
             */
                if (
                    $formType === 'add' && 
                    $request->request->get('storage') === 'orm' && 
                    isset($em)
                ) {
                    $newLesson = new Lesson();

                    // Create and handle the Symfony form for new ORM lesson
                    $formLesson = $this->createForm(LessonFormType::class, $newLesson, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => false,
                    ]);
                    $formLesson->handleRequest($request);

                    if ($formLesson->isSubmitted() && $formLesson->isValid()) {
                        $em->persist($newLesson);
                        $em->flush();
                        $this->addFlash('success', 'Lesson added!');
                        return $this->redirectToRoute('app_admin');
                    }

                    $editFormsLesson['add'] = $formLesson->createView(); 
                }
    }            
        // Render edit forms for all ORM lessons
        if ($storage === 'orm' || $storage === 'both') {
            foreach ($lessons as $lesson) {
                if (!isset($editFormsLesson['orm'][$lesson->getId()])) {
                    $formLesson = $this->createForm(LessonFormType::class, $lesson, [
                        'action' => $this->generateUrl('app_admin'),
                        'method' => 'POST',
                        'em' => $em,
                        'is_edit' => true,
                    ]);
                    $editFormsLesson['orm'][$lesson->getId()] = $formLesson->createView();
                }
            }
        }
            
        // Render edit forms for all MongoDB lessons
        if (isset($documentManager) && $documentManager !== null) {
            foreach ($lessonsMongo as $lesson) {
                if (!isset($editFormsLesson['mongo'][$lesson->getId()])) {
                    $formLesson = $this->createForm(LessonFormTypeMongo::class, $lesson, [
                            'action' => $this->generateUrl('app_admin'),
                            'method' => 'POST',
                            'is_edit' => true,
                    ]);
                    $editFormsLesson['mongo'][(string) $lesson->getId()] = $formLesson->createView();
                }
            }
        }
        
        // Prepare add form for ORM lesson
        $lessonAddFormOrm = null;
        if ($storage === 'orm' || $storage === 'both') {
            $newLessonOrm= new Lesson();
            $lessonAddFormOrm = $this->createForm(LessonFormType::class, $newLessonOrm, ['em' => $em]);
            $lessonAddFormOrm->handleRequest($request);
        }

            // Prepare add form for MongoDB lesson
            $lessonAddFormMongo = null;
            if (isset($documentManager) && $documentManager !== null) {
                $newLessonMongo  = new LessonDocument();
                $lessonAddFormMongo = $this->createForm(LessonFormTypeMongo::class, $newLessonMongo);
            }

            // Handle form submission for MongoDB add form
            if ($lessonAddFormMongo) {
                $lessonAddFormMongo->handleRequest($request);
            }

            // Handle lesson creation via unified POST form (ORM or Mongo)
            if ($request->isMethod('POST') && $request->request->has('add_lesson')) {
                $storage = $request->request->get('storage', 'orm');

                // Create ORM lesson
                if ($storage === 'orm' && $lessonAddFormOrm->isSubmitted() && $lessonAddFormOrm->isValid()) {
                    $this->handleVideoUploadMixed($lessonAddFormOrm, $newLessonOrm, true);

                    $user->addCreateLesson($newLessonOrm);
                    $em->persist($newLessonOrm);
                    $em->flush();

                    $this->addFlash('success', 'Lesson ORM added !');
                    return $this->redirectToRoute('app_admin');

                } 

                // Create MongoDB lesson
                elseif ($storage === 'mongo' && $lessonAddFormMongo->isSubmitted() && $lessonAddFormMongo->isValid()) {
                    
                    $this->handleVideoUploadMixed($lessonAddFormMongo, $newLessonMongo, false);
                    $user->addCreateLesson($newLessonMongo);
                    $documentManager->persist($newLessonMongo);
                    $documentManager->flush();

                    $this->addFlash('success', 'Lesson Mongo added !');
                    return $this->redirectToRoute('app_admin');
                }
            }
        
        // Render the admin dashboard with all collected data and forms
        return $this->render('admin/index.html.twig', [
            'storage' => $storage,
            'themes' => $themes,
            'themesMongo' => $themesMongo,
            'users' => $users,
            'usersMongo' => $usersMongo,
            'courses' => $courses,
            'coursesMongo' => $coursesMongo,
            'lessons' => $lessons,
            'lessonsMongo' => $lessonsMongo,
            'add_form' => $addFormOrm ? $addFormOrm->createView() : null,
            'add_form_mongo' => $addFormMongo ? $addFormMongo->createView() : null,
            'edit_forms' => $editForms,
            'add_course_form' => $courseAddFormOrm ? $courseAddFormOrm->createView() : null,
            'add_course_form_mongo' => $courseAddFormMongo ? $courseAddFormMongo->createView() : null,
            'edit_course_forms' => $editFormsCourse,
            'add_theme_form' => $themeAddFormOrm ? $themeAddFormOrm->createView() : null,
            'add_theme_form_mongo' => $themeAddFormMongo ? $themeAddFormMongo->createView() : null,
            'edit_theme_forms' => $editFormsTheme,
            'add_lesson_form' => $lessonAddFormOrm ? $lessonAddFormOrm->createView() : null,
            'add_lesson_form_mongo' => $lessonAddFormMongo ? $lessonAddFormMongo->createView() : null,
            'edit_lesson_forms' => $editFormsLesson,
        ]);
    }
    
    /**
     * Handles video file upload for a lesson (ORM or MongoDB).
     *
     */

    private function handleVideoUploadMixed($form, object $lesson, bool $useMongo = false): void
    {
        $videoFile = $form->get('videoFile')->getData();

        if ($videoFile) {
            $oldFilename = $lesson->getVideoName();

            // Remove old video file if it exists and is not default
            if ($oldFilename && $oldFilename !== 'default.png') {
                $oldFilePath = $this->getParameter('uploads_directory') . '/' . $oldFilename;
                if (file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }

            // Generate new filename
            $originalExtension = $videoFile->guessExtension() ?: 'mp4';
            $newFilename = uniqid('video_', true) . '.' . $originalExtension;

            try {
                // Move uploaded file to target directory
                $videoFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } 
            catch (\Exception $e) {
                throw new \RuntimeException('Error while uploading video: ' . $e->getMessage());
            }

            // Update lesson with new video filename
            $lesson->setVideoName($newFilename);
        }
    }
}