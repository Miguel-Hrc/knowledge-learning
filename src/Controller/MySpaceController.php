<?php

namespace App\Controller;

use App\Entity\User;
use App\Document\UserDocument;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Controller responsible for the "My Space" page of a client user.
 * Displays lessons and certifications depending on whether the system uses ORM (SQL) or MongoDB.
 */
class MySpaceController extends AbstractController
{
    /**
     * @var DocumentManager|null Doctrine ODM DocumentManager (nullable if ORM only).
     */
    private ?DocumentManager $documentManager;

    /**
     * @var EntityManagerInterface|null Doctrine ORM EntityManager (nullable if MongoDB only).
     */
    private ?EntityManagerInterface $entityManager;


    /**
     * @var string Defines the active data source ("orm", "mongodb", or "both").
     */
    private string $dataSource;

    /**
     * Constructor.
     *
     * @param DocumentManager|null $documentManager Doctrine ODM DocumentManager.
     * @param EntityManagerInterface|null $entityManager Doctrine ORM EntityManager.
     * @param ParameterBagInterface $params Provides configuration parameters (e.g., `app.data_source`).
     */
    public function __construct(
        ?DocumentManager $documentManager,
        ?EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
    ) {
        $this->documentManager = $documentManager;
        $this->entityManager = $entityManager;
        $this->dataSource = strtolower($params->get('app.data_source'));
    }

    /**
     * Route: /my-space
     *
     * Displays the user's purchased lessons and validated certifications.
     * Works with both ORM (SQL) and MongoDB user representations.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException If user email is missing.
     */
    #[Route('/my-space', name: 'app_my_space')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $user = $this->getUser();
        $userEmail = $user?->getEmail();

        if (!$userEmail) {
            throw $this->createAccessDeniedException('Unrecognized user');
        }

        $userOrm = null;
        $userMongo = null;


        // Fetch user from ORM if enabled
        if ($this->dataSource === 'orm' || $this->dataSource === 'both') {
            $userOrm = $user instanceof User
                ? $user
                : $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userEmail]);
        }

        // Fetch user from MongoDB if enabled
        if ($this->dataSource === 'mongodb' || $this->dataSource === 'both') {
            $userMongo = $user instanceof UserDocument
                ? $user
                : $this->documentManager->getRepository(UserDocument::class)->findOneBy(['email' => $userEmail]);
        }


        // Collect lessons purchased in ORM

        $lessonsSql = [];
        if ($userOrm) {
            $lessonsSql = $userOrm->getPurchasedLessons()->toArray();
            foreach ($userOrm->getPurchasedCourses() as $course) {
                foreach ($course->getLessons() as $lesson) {
                    if (!in_array($lesson, $lessonsSql, true)) {
                        $lessonsSql[] = $lesson;
                    }
                }
            }
        }

        // Collect lessons purchased in MongoDB
        $lessonsMongo = [];
        if ($userMongo) {
            $lessonsMongo = is_array($userMongo->getPurchasedLessonsMongo())
                ? $userMongo->getPurchasedLessonsMongo()
                : iterator_to_array($userMongo->getPurchasedLessonsMongo());

            foreach ($userMongo->getPurchasedCoursesMongo() ?? [] as $course) {
                foreach ($course->getLessons() ?? [] as $lesson) {
                    if (!in_array($lesson, $lessonsMongo, true)) {
                        $lessonsMongo[] = $lesson;
                    }
                }
            }
        }
        // Determine validated certifications in ORM
        $certificationsSqlValidees = [];
        if ($userOrm) {
            foreach ($userOrm->getCertifications() as $certification) {
                $theme = $certification->getTheme();
                if ($theme) {
                    $themeLessons = $theme->getLessons();
                    $isCompleted = true;
                    foreach ($themeLessons as $themeLesson) {
                        if (!in_array($themeLesson, $lessonsSql, true)) {
                            $isCompleted = false;
                            break;
                        }
                    }
                    if ($isCompleted) {
                        $certificationsSqlValidees[] = $certification;
                    }
                }
            }
        }

        // Determine validated certifications in MongoDB
        $certificationsMongoValidees = [];
        if ($userMongo) {
            foreach ($userMongo->getCertifications() ?? [] as $certification) {
                $theme = $certification->getTheme($this->documentManager);
                if ($theme) {
                    $themeLessons = $theme->getLessons();
                    $isCompleted = true;
                    foreach ($themeLessons as $themeLesson) {
                        $found = false;
                        foreach ($lessonsMongo as $lessonMongo) {
                            if ((string) $lessonMongo->getId() === (string) $themeLesson->getId()){
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $isCompleted = false;
                            break;
                        }
                    }
                    if ($isCompleted) {
                        $certificationsMongoValidees[] = $certification;
                    }
                }
            }
        }
        $themesMongo = [];

        foreach ($lessonsMongo as $lesson) {
            $theme = $lesson->getCourse()->getTheme();
            $themeId = (string) $theme->getId();
            $themeName = $theme->getName();
            $course = $lesson->getCourse();
            $courseId = (string) $course->getId();
            $courseTitle = $course->getTitle();

            if (!isset($themesMongo[$themeId])) {
                $themesMongo[$themeId] = [
                    'name' => $themeName,
                    'courses' => []
                ];
            }

            if (!isset($themesMongo[$themeId]['courses'][$courseId])) {
                $themesMongo[$themeId]['courses'][$courseId] = [
                    'title' => $courseTitle,
                    'lessons' => []
                ];
            }

            $themesMongo[$themeId]['courses'][$courseId]['lessons'][] = $lesson;
        }

        $themesSql = [];

        foreach ($lessonsSql as $lesson) {
            $course = $lesson->getCourse();
            $theme = $course->getTheme();

            $themeId = (string) $theme->getId();
            $themeName = $theme->getName();
            $courseId = (string) $course->getId();
            $courseTitle = $course->getTitle();

            if (!isset($themesSql[$themeId])) {
                $themesSql[$themeId] = [
                    'name' => $themeName,
                    'courses' => []
                ];
            }

            if (!isset($themesSql[$themeId]['courses'][$courseId])) {
                $themesSql[$themeId]['courses'][$courseId] = [
                    'title' => $courseTitle,
                    'lessons' => []
                ];
            }

            $themesSql[$themeId]['courses'][$courseId]['lessons'][] = $lesson;
        }

        // Render the My Space page with user data
        return $this->render('my_space/index.html.twig', [
        'dataSource' => $this->dataSource,
        'lessons' => ($this->dataSource !== 'mongodb') ? $lessonsSql : [],
        'lessonsMongo' => ($this->dataSource !== 'orm') ? $lessonsMongo : [],
        'certifications_sql' => ($this->dataSource !== 'mongodb') ? $certificationsSqlValidees : [],
        'certifications_mongo' => ($this->dataSource !== 'orm') ? $certificationsMongoValidees : [],
        'themesSql' => ($this->dataSource !== 'mongodb') ? $themesSql : [],
        'themesMongo' => ($this->dataSource !== 'orm') ? $themesMongo : [],
    ]);
    }
}