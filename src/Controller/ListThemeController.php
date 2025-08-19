<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Document\ThemeDocument;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Controller responsible for listing all Themes.
 * Supports both ORM (SQL) and MongoDB as data sources, depending on configuration.
 */
class ListThemeController extends AbstractController
{
    /**
     * @var EntityManagerInterface|null Doctrine ORM EntityManager (nullable if MongoDB only).
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * @var DocumentManager|null Doctrine ODM DocumentManager (nullable if ORM only).
     */
    private ?DocumentManager $documentManager;

    /**
     * @var string Defines the active data source ("orm", "mongodb", or "both").
     */
    private string $dataSource;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface|null $entityManager Doctrine ORM EntityManager.
     * @param DocumentManager|null $documentManager Doctrine ODM DocumentManager.
     * @param ParameterBagInterface $params Provides application parameters (e.g., `app.data_source`).
     */
    public function __construct(
        ?EntityManagerInterface $entityManager,
        ?DocumentManager $documentManager,
        ParameterBagInterface $params
    ) {
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Displays a list of all Themes, depending on the selected data source.
     *
     * @return Response
     */
    #[Route('/theme', name: 'app_list_theme', methods: ['GET'])]
    public function index(): Response
    {
        // Ensure that only authenticated users with ROLE_USER can access this route
        $this->denyAccessUnlessGranted("ROLE_USER");

        $themesOrm = [];
        $themesMongo = [];

        // If ORM or both data sources are enabled, fetch SQL Themes
        if ($this->dataSource === 'orm' || $this->dataSource === 'both') {
            $themesOrm = $this->entityManager->getRepository(Theme::class)->findAll();
        }

        // If MongoDB or both data sources are enabled, fetch MongoDB Themes
        if ($this->dataSource === 'mongodb' || $this->dataSource === 'both') {
            $themesMongo = $this->documentManager->getRepository(ThemeDocument::class)->findAll();
        }

        // Render the view with data from the chosen sources
        return $this->render('list_theme/index.html.twig', [
            'dataSource' => $this->dataSource,
            'themesOrm' => $themesOrm,
            'themesMongo' => $themesMongo,
        ]);
    }
}