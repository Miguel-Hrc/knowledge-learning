<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Document\ThemeDocument;
use App\Document\CourseDocument;
use App\Document\LessonDocument;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Controller responsible for displaying a Theme and its related Courses and Lessons.
 * It supports both ORM (SQL) and MongoDB as data sources, depending on the configuration.
 */
class DetailThemeController extends AbstractController
{
    /**
     * Defines the data source used ("orm", "mongodb", or "both").
     *
     * @var string
     */
    private string $dataSource;

    /**
     * Constructor.
     *
     * @param ParameterBagInterface $params Provides access to application parameters (including `app.data_source`).
     */
    public function __construct(ParameterBagInterface $params)
    {
        // The parameter app.data_source controls which data source to use ("orm" or "mongodb").
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Main route for displaying theme details by its ID.
     *
     * @param string $id The Theme identifier.
     * @param EntityManagerInterface|null $em Doctrine ORM EntityManager (nullable if not using SQL).
     * @param DocumentManager|null $dm Doctrine ODM DocumentManager (nullable if not using MongoDB).
     *
     * @return Response
     */
    #[Route('/theme/{id}', name: 'app_detail_theme', methods: ['GET'])]
    public function index(string $id, ?EntityManagerInterface $em, ?DocumentManager $dm): Response
    {
        // Ensure only logged-in users with ROLE_USER can access this route.
        $this->denyAccessUnlessGranted("ROLE_USER");

        // Handle MongoDB datasource
        if ($this->dataSource === 'mongodb') {
            return $this->handleMongo($id, $dm);
        }

        // Default to ORM datasource
        return $this->handleOrm($id, $em);
    }

    /**
     * Handles fetching and rendering a theme using Doctrine ORM (SQL database).
     *
     * @param string $id The Theme identifier.
     * @param EntityManagerInterface|null $em Doctrine ORM EntityManager.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the Theme is not found.
     */
    private function handleOrm(string $id, ?EntityManagerInterface $em): Response
    {
        $theme = $em->getRepository(Theme::class)->find($id);
        if (!$theme) {
            throw $this->createNotFoundException("Theme (ORM) not found");
        }

        // Fetch courses linked to this theme
        $courses = $em->getRepository(Course::class)->findBy(['theme' => $theme]);

        // Fetch lessons linked to this theme through their course relation
        $lessons = $em->getRepository(Lesson::class)->createQueryBuilder('l')
            ->join('l.course', 'c')
            ->where('c.theme = :theme')
            ->setParameter('theme', $theme)
            ->getQuery()
            ->getResult();

        return $this->render('detail_theme/index.html.twig', [
            'source' => 'orm',
            'theme' => $theme,
            'courses' => $courses,
            'lessons' => $lessons,
        ]);
    }

    /**
     * Handles fetching and rendering a theme using Doctrine ODM (MongoDB).
     *
     * @param string $id The Theme identifier.
     * @param DocumentManager|null $dm Doctrine ODM DocumentManager.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the Theme is not found in MongoDB.
     */
    private function handleMongo(string $id, ?DocumentManager $dm): Response
    {
        $themeMongo = $dm->getRepository(ThemeDocument::class)->find($id);
        if (!$themeMongo) {
            throw $this->createNotFoundException("Theme (MongoDB) not found");
        }

        // Fetch all courses linked to this theme
        $coursesMongo = $dm->getRepository(CourseDocument::class)->findBy(['theme' => $themeMongo]);

        // Fetch lessons for each course
        $lessonsMongo = [];
        foreach ($coursesMongo as $course) {
            $lessonsMongo[$course->getId()] = $dm->getRepository(LessonDocument::class)
                ->findBy(['course' => $course]);
        }

        return $this->render('detail_theme/index.html.twig', [
            'source' => 'mongodb',
            'themeMongo' => $themeMongo,
            'coursesMongo' => $coursesMongo,
            'lessonsMongo' => $lessonsMongo,
        ]);
    }
}