<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Document\LessonDocument;
use App\Document\UserDocument;
use App\Service\CertificationService;
use App\Service\CertificationServiceMongo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Controller responsible for managing Lessons (viewing and validation).
 * It supports both ORM (SQL) and MongoDB, depending on the configured data source.
 */
class LessonController extends AbstractController
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
     * @var CertificationService Service handling certification logic for ORM users.
     */
    private CertificationService $certificationService;

    /**
     * @var CertificationServiceMongo Service handling certification logic for MongoDB users.
     */
    private CertificationServiceMongo $certificationServiceMongo;

    /**
     * @var string Defines the active data source ("orm", "mongodb", or "both").
     */
    private string $dataSource;

    /**
     * Constructor.
     *
     * @param ParameterBagInterface $params Provides application parameters (including `app.data_source`).
     * @param EntityManagerInterface|null $entityManager Doctrine ORM EntityManager.
     * @param DocumentManager|null $documentManager Doctrine ODM DocumentManager.
     * @param CertificationService $certificationService Certification service for ORM users.
     * @param CertificationServiceMongo $certificationServiceMongo Certification service for MongoDB users.
     */
    public function __construct(
        ParameterBagInterface $params,
        ?EntityManagerInterface $entityManager,
        ?DocumentManager $documentManager,
        CertificationService $certificationService,
        CertificationServiceMongo $certificationServiceMongo
    ) {
        $this->entityManager = $entityManager;
        $this->documentManager = $documentManager;
        $this->certificationService = $certificationService;
        $this->certificationServiceMongo = $certificationServiceMongo;
        $this->dataSource = strtolower($params->get('app.data_source') ?? 'both');
    }

    /**
     * Displays the details of a Lesson by its ID.
     *
     * @param string|int $id Lesson identifier.
     *
     * @return Response
     */
    #[Route('/lesson/{id}', name: 'app_detail_lesson', methods: ['GET'])]
    public function index($id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        if ($this->dataSource === 'mongodb') {
            return $this->handleMongoLesson($id);
        }

        return $this->handleOrmLesson($id);
    }

    /**
     * Validates a Lesson for the current user (marks it as completed).
     *
     * @param string|int $id Lesson identifier.
     *
     * @return Response
     */
    #[Route('/lesson/{id}/validate', name: 'validate_lesson')]
    public function validateLesson($id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        if ($this->dataSource === 'mongodb') {
            return $this->handleMongoValidation($id);
        }

        return $this->handleOrmValidation($id);
    }

    /**
     * Handles displaying a lesson using Doctrine ORM (SQL).
     *
     * @param string|int $id Lesson identifier.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the lesson is not found.
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException If the user has no access.
     */
    private function handleOrmLesson($id): Response
    {
        $userSql = $this->getUser();
        $lesson = $this->entityManager->getRepository(Lesson::class)->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Lesson not found');
        }

        // Check if the lesson belongs to the user's purchased lessons or purchased courses
        $accessible = $userSql->getPurchasedLessons()->contains($lesson)
            || $userSql->getPurchasedCourses()->exists(fn($k, $c) => $c->getLessons()->contains($lesson));

        if (!$accessible) {
            throw $this->createAccessDeniedException('You do not have access to this lesson.');
        }

        return $this->render('detail_lesson/index.html.twig', [
            'dataSource' => $this->dataSource,
            'lesson' => $lesson,
            'source' => 'orm',
        ]);
    }

    /**
     * Handles displaying a lesson using Doctrine ODM (MongoDB).
     *
     * @param string|int $id Lesson identifier.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the lesson or user is not found.
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException If the user has no access.
     */
    private function handleMongoLesson($id): Response
    {
        $userSql = $this->getUser();
        $lesson = $this->documentManager->getRepository(LessonDocument::class)->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Lesson not found');
        }

        /** @var UserDocument|null $userMongo */
        $userMongo = $this->documentManager
            ->getRepository(UserDocument::class)
            ->findOneBy(['email' => $userSql->getEmail()]);

        if (!$userMongo) {
            throw $this->createNotFoundException('Mongo user not found');
        }

        // Check if the lesson belongs to the user's purchased lessons or purchased courses in MongoDB
        $accessible = $userMongo->getPurchasedLessonsMongo()->contains($lesson)
            || $userMongo->getPurchasedCoursesMongo()->exists(fn($k, $c) => $c->getLessons()->contains($lesson));

        if (!$accessible) {
            throw $this->createAccessDeniedException('You do not have access to this lesson.');
        }

        return $this->render('detail_lesson/index.html.twig', [
            'dataSource' => $this->dataSource,
            'lesson' => $lesson,
            'source' => 'mongodb',
        ]);
    }

    /**
     * Handles validation (completion) of a lesson for ORM (SQL).
     *
     * @param string|int $id Lesson identifier.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the lesson is not found.
     */
    private function handleOrmValidation($id): Response
    {
        $userSql = $this->getUser();
        $lesson = $this->entityManager->getRepository(Lesson::class)->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Lesson not found');
        }

        // Add lesson to purchased lessons if not already present
        if (!$userSql->getPurchasedLessons()->contains($lesson)) {
            $userSql->addPurchasedLessons($lesson);
            $this->entityManager->persist($userSql);
            $this->entityManager->flush();
        }

        // Trigger certification check for the related Theme
        $this->certificationService->verifierEtAttribuerCertification(
            $userSql,
            $lesson->getCourse()->getTheme()
        );

        $this->addFlash('success', 'Lesson successfully completed!');
        return $this->redirectToRoute('app_my_space');
    }

    /**
     * Handles validation (completion) of a lesson for MongoDB.
     *
     * @param string|int $id Lesson identifier.
     *
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException If the lesson is not found.
     */
    private function handleMongoValidation($id): Response
    {
        $userSql = $this->getUser();
        $lesson = $this->documentManager->getRepository(LessonDocument::class)->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Lesson not found');
        }

        /** @var UserDocument|null $userMongo */
        $userMongo = $this->documentManager
            ->getRepository(UserDocument::class)
            ->findOneBy(['email' => $userSql->getEmail()]);

        if (!$userMongo) {
            throw $this->createNotFoundException('Mongo user not found');
        }

        // Add lesson to purchased lessons if not already present
        if (!$userMongo->getPurchasedLessonsMongo()->contains($lesson)) {
            $userMongo->addPurchasedLessonsMongo($lesson);
            $this->documentManager->persist($userMongo);
            $this->documentManager->flush();
        }

        // Trigger certification check for the related Theme
        $this->certificationServiceMongo->verifierEtAttribuerCertification(
            $userMongo,
            $lesson->getCourse()->getTheme()
        );

        $this->addFlash('success', 'Lesson successfully completed!');
        return $this->redirectToRoute('app_my_space');
    }
}