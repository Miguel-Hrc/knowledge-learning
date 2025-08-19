<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Document\ThemeDocument;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Controller for the Home page.
 * It loads and displays Themes either from ORM (SQL) or MongoDB, 
 * depending on the configured data source.
 */
class HomeController extends AbstractController
{
    /**
     * Defines the active data source ("orm", "mongodb", or "both").
     *
     * @var string
     */
    private string $dataSource;

    /**
     * Constructor.
     *
     * @param ParameterBagInterface $params Provides access to application parameters (e.g., `app.data_source`).
     */
    public function __construct(ParameterBagInterface $params)
    {
        // Read the application parameter "app.data_source" to choose between "orm" or "mongodb"
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Home page route.
     * Fetches and displays themes depending on the configured data source.
     *
     * @param EntityManagerInterface|null $entityManager Doctrine ORM EntityManager (nullable if MongoDB only).
     * @param DocumentManager|null $documentManager Doctrine ODM DocumentManager (nullable if ORM only).
     *
     * @return Response
     */
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(?EntityManagerInterface $entityManager, ?DocumentManager $documentManager): Response
    {
        // Handle MongoDB data source
        if ($this->dataSource === 'mongodb') {
            return $this->handleMongo($documentManager);
        }

        // Default to ORM
        return $this->handleOrm($entityManager);
    }

    /**
     * Handles fetching and rendering themes from ORM (SQL).
     *
     * @param EntityManagerInterface|null $entityManager Doctrine ORM EntityManager.
     *
     * @return Response
     */
    private function handleOrm(?EntityManagerInterface $entityManager): Response
    {
        $themesOrm = $entityManager->getRepository(Theme::class)->findAll();

        return $this->render('home/index.html.twig', [
            'source' => 'orm',
            'dataSource' => $this->dataSource,
            'themesOrm' => $themesOrm,
            'themesMongo' => [], // empty since Mongo is not used here
        ]);
    }

    /**
     * Handles fetching and rendering themes from MongoDB.
     *
     * @param DocumentManager|null $documentManager Doctrine ODM DocumentManager.
     *
     * @return Response
     */
    private function handleMongo(?DocumentManager $documentManager): Response
    {
        $themesMongo = $documentManager->getRepository(ThemeDocument::class)->findAll();

        return $this->render('home/index.html.twig', [
            'source' => 'mongodb',
            'dataSource' => $this->dataSource,
            'themesOrm' => [], // empty since ORM is not used here
            'themesMongo' => $themesMongo,
        ]);
    }
}