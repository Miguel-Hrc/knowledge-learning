<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Course;
use App\Document\LessonDocument;
use App\Document\CourseDocument;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\UserDataAccessValidator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Controller responsible for managing shopping cart operations.
 * Supports both MySQL (ORM) and MongoDB (ODM) data sources, depending on configuration.
 */
class CartController
{
    /** @var EntityManagerInterface|null Doctrine ORM entity manager */
    private ?EntityManagerInterface $em;

    /** @var DocumentManager|null Doctrine MongoDB document manager */
    private ?DocumentManager $dm;

    /** @var UserDataAccessValidator Service to validate user permissions for accessing lessons/courses */
    private UserDataAccessValidator $validator;

    /** @var string Current active data source (orm|mongodb|both) */
    private string $dataSource;

    /** @var TokenStorageInterface Security token storage to retrieve the authenticated user */
    private TokenStorageInterface $tokenStorage;

    /** @var AuthorizationCheckerInterface Used to check user roles and permissions */
    private AuthorizationCheckerInterface $authChecker;

    /** @var UrlGeneratorInterface Generates URLs for redirections */
    private UrlGeneratorInterface $urlGenerator;

    /** @var Environment Twig environment for rendering templates */
    private Environment $twig;

    /**
     * Constructor.
     *
     * @param ParameterBagInterface         $params       Application parameters
     * @param EntityManagerInterface|null   $em           Doctrine ORM entity manager (nullable)
     * @param DocumentManager|null          $dm           Doctrine MongoDB document manager (nullable)
     * @param UserDataAccessValidator       $validator    Service for validating user access to lessons/courses
     * @param TokenStorageInterface         $tokenStorage Token storage for retrieving the logged-in user
     * @param AuthorizationCheckerInterface $authChecker  Service to check user roles/permissions
     * @param UrlGeneratorInterface         $urlGenerator URL generator for redirects
     * @param Environment                   $twig         Twig environment
     */
    public function __construct(
        ParameterBagInterface $params,
        ?EntityManagerInterface $em,
        ?DocumentManager $dm,
        UserDataAccessValidator $validator,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authChecker,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Display the cart with all items (lessons/courses from MySQL or MongoDB).
     *
     * @param SessionInterface $session The current session storing cart data
     *
     * @return Response Rendered cart page
     */
    #[Route('/cart', name: 'app_cart')]
    public function show(SessionInterface $session): Response
    {
        $lessons = $courses = $lessonsMongo = $coursesMongo = [];
        $total = 0;

        // Handle ORM cart
        if (in_array($this->dataSource, ['orm', 'both'])) {
            $cart = $session->get('cart', []);
            $lessons = $this->getLessonsFromMysql($cart['lessons'] ?? []);
            $courses = $this->getCoursesFromMysql($cart['courses'] ?? []);
            foreach ($lessons as $lesson) { $total += $lesson->getPrice(); }
            foreach ($courses as $course) { $total += $course->getPrice(); }
        }

        // Handle MongoDB cart
        if (in_array($this->dataSource, ['mongodb', 'both'])) {
            $cartMongo = $session->get('cart_mongo', []);
            $lessonsMongo = $this->getLessonsFromMongo($cartMongo['lessons'] ?? []);
            $coursesMongo = $this->getCoursesFromMongo($cartMongo['courses'] ?? []);
            foreach ($lessonsMongo as $lessonMongo) { $total += $lessonMongo->getPrice(); }
            foreach ($coursesMongo as $courseMongo) { $total += $courseMongo->getPrice(); }
        }

        $html = $this->twig->render('cart/index.html.twig', [
            'dataSource' => $this->dataSource,
            'lessons' => $lessons,
            'courses' => $courses,
            'lessonsMongo' => $lessonsMongo,
            'coursesMongo' => $coursesMongo,
            'total' => $total,
        ]);

        return new Response($html);
    }

    /**
     * Add a lesson (MySQL/ORM) to the cart.
     *
     * @param Lesson           $lesson  Lesson entity
     * @param SessionInterface $session Current session
     *
     * @return Response Redirects to the cart page
     */
    #[Route('/cart/add/lesson/{id}', name: 'cart_add_lesson')]
    public function addLesson(Lesson $lesson, SessionInterface $session): Response
    {
        if ($this->dataSource === 'mongodb') {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $this->validator->validateUserAccessToLesson($this->getUser(), $lesson);
        $cart = $session->get('cart', []);
        $cart['lessons'][$lesson->getId()] = true;
        $session->set('cart', $cart);

        return new RedirectResponse($this->urlGenerator->generate('app_cart'));
    }

    /**
     * Add a course (MySQL/ORM) to the cart.
     *
     * @param Course           $course  Course entity
     * @param SessionInterface $session Current session
     *
     * @return Response Redirects to the cart page
     */
    #[Route('/cart/add/course/{id}', name: 'cart_add_course')]
    public function addCourse(Course $course, SessionInterface $session): Response
    {
        if ($this->dataSource === 'mongodb') {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $this->validator->validateUserAccessToCourse($this->getUser(), $course);
        $cart = $session->get('cart', []);
        $cart['courses'][$course->getId()] = true;
        $session->set('cart', $cart);

        return new RedirectResponse($this->urlGenerator->generate('app_cart'));
    }

    /**
     * Add a MongoDB lesson to the cart.
     *
     * @param string           $id      MongoDB lesson ID
     * @param SessionInterface $session Current session
     *
     * @return Response Redirects to the cart page
     */
    #[Route('/cart/add/mongo/lesson/{id}', name: 'cart_add_lesson_mongo')]
    public function addLessonMongo(string $id, SessionInterface $session): Response
    {
        if ($this->dataSource === 'orm') {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $lesson = $this->dm->getRepository(LessonDocument::class)->find($id);

        if (!$lesson) {
            throw new NotFoundHttpException('Mongo lesson not found');
        }

        $this->validator->validateUserAccessToLesson($this->getUser(), $lesson);
        $cartMongo = $session->get('cart_mongo', []);
        $cartMongo['lessons'][$id] = true;
        $session->set('cart_mongo', $cartMongo);

        return new RedirectResponse($this->urlGenerator->generate('app_cart'));
    }

    /**
     * Add a MongoDB course to the cart.
     *
     * @param string           $id      MongoDB course ID
     * @param SessionInterface $session Current session
     *
     * @return Response Redirects to the cart page
     */
    #[Route('/cart/add/mongo/course/{id}', name: 'cart_add_course_mongo')]
    public function addCourseMongo(string $id, SessionInterface $session): Response
    {
        if ($this->dataSource === 'orm') {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $course = $this->dm->getRepository(CourseDocument::class)->find($id);

        if (!$course) {
            throw new NotFoundHttpException('Mongo course not found');
        }

        $this->validator->validateUserAccessToCourse($this->getUser(), $course);
        $cartMongo = $session->get('cart_mongo', []);
        $cartMongo['courses'][$id] = true;
        $session->set('cart_mongo', $cartMongo);

        return new RedirectResponse($this->urlGenerator->generate('app_cart'));
    }

    /**
     * Fetch lessons from MySQL by IDs.
     *
     * @param array $ids Lesson IDs
     *
     * @return Lesson[]
     */
    private function getLessonsFromMysql(array $ids): array
    {
        if (empty($ids)) return [];
        return $this->em->getRepository(Lesson::class)->findBy(['id' => array_keys($ids)]);
    }

    /**
     * Fetch courses from MySQL by IDs.
     *
     * @param array $ids Course IDs
     *
     * @return Course[]
     */
    private function getCoursesFromMysql(array $ids): array
    {
        if (empty($ids)) return [];
        return $this->em->getRepository(Course::class)->findBy(['id' => array_keys($ids)]);
    }

    /**
     * Fetch lessons from MongoDB by IDs.
     *
     * @param array $ids Lesson IDs
     *
     * @return LessonDocument[]
     */
    private function getLessonsFromMongo(array $ids): array
    {
        if (empty($ids)) return [];
        return $this->dm->getRepository(LessonDocument::class)->findBy(['id' => ['$in' => array_keys($ids)]]);
    }

    /**
     * Fetch courses from MongoDB by IDs.
     *
     * @param array $ids Course IDs
     *
     * @return CourseDocument[]
     */
    private function getCoursesFromMongo(array $ids): array
    {
        if (empty($ids)) return [];
        return $this->dm->getRepository(CourseDocument::class)->findBy(['id' => ['$in' => array_keys($ids)]]);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return mixed|null The authenticated user or null if not authenticated
     */
    private function getUser()
    {
        return $this->tokenStorage->getToken()?->getUser();
    }

    /**
     * Ensure the user has a specific role, otherwise throw an exception.
     *
     * @param string $role Role to check
     *
     * @throws AccessDeniedException if the user does not have the required role
     */
    private function denyAccessUnlessGranted(string $role): void
    {
        if (!$this->authChecker->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }
}