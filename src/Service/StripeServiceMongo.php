<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\LessonDocument;
use App\Document\CourseDocument;

/**
 * Service responsible for handling Stripe Checkout sessions with MongoDB documents.
 *
 * This service generates a Stripe Checkout session for a user's cart
 * containing lessons and courses stored as MongoDB documents.
 */
class StripeServiceMongo
{
    private string $stripeSecretKey;
    private UrlGeneratorInterface $urlGenerator;
    private ?DocumentManager $documentManager;

    /**
     * Constructor.
     *
     * @param string $stripeSecretKey Your Stripe secret API key
     * @param UrlGeneratorInterface $urlGenerator Symfony URL generator
     * @param DocumentManager|null $documentManager Doctrine MongoDB document manager
     */
    public function __construct(
        string $stripeSecretKey,
        UrlGeneratorInterface $urlGenerator,
        ?DocumentManager $documentManager
    ) {
        $this->stripeSecretKey = $stripeSecretKey;
        $this->urlGenerator = $urlGenerator;
        $this->documentManager = $documentManager;
    }

    /**
     * Creates a Stripe Checkout session for a cart containing lessons and courses.
     *
     * Each lesson and course in the cart is converted into a line item with its
     * corresponding price and title. The session provides URLs for success and cancellation.
     *
     * @param array $cart The shopping cart with structure:
     *                    [
     *                      'lessons' => [lessonId => true, ...],
     *                      'courses' => [courseId => true, ...]
     *                    ]
     *
     * @return string The URL to redirect the user to Stripe Checkout
     */
    public function createCheckoutSession(array $cart): string
    {
        Stripe::setApiKey($this->stripeSecretKey);
        $lineItems = [];

        // Add lessons to line items
        foreach (array_keys($cart['lessons'] ?? []) as $lessonId) {
            $lesson = $this->documentManager->getRepository(LessonDocument::class)->find($lessonId);
            if (!$lesson) {
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Lesson : ' . $lesson->getTitle(),
                    ],
                    'unit_amount' => (int) ($lesson->getPrice() * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Add courses to line items
        foreach (array_keys($cart['courses'] ?? []) as $courseId) {
            $course = $this->documentManager->getRepository(CourseDocument::class)->find($courseId);
            if (!$course) {
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Course : ' . $course->getTitle(),
                    ],
                    'unit_amount' => (int) ($course->getPrice() * 100),
                ],
                'quantity' => 1,
            ];
        }

        // Create Stripe Checkout session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->urlGenerator->generate('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $session->url;
    }
}