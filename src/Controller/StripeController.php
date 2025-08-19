<?php

namespace App\Controller;

use App\Entity\Command;
use App\Entity\CommandItem;
use App\Entity\Course;
use App\Entity\Lesson;
use App\Entity\Payment;
use App\Entity\User;

use App\Document\CommandDocument;
use App\Document\CommandItemDocument;
use App\Document\CourseDocument;
use App\Document\LessonDocument;
use App\Document\PaymentDocument;
use App\Document\UserDocument;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Service\StripeService;
use App\Service\StripeServiceMongo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller responsible for handling Stripe checkout and payment success redirects.
 * Supports both SQL (Doctrine ORM) and MongoDB (Doctrine ODM) based carts.
 */
class StripeController extends AbstractController
{
    /**
     * Initiates Stripe checkout for a SQL-based shopping cart.
     *
     * @param Request $request The HTTP request containing the session.
     * @param StripeService $stripeService The service responsible for creating Stripe checkout sessions for SQL entities.
     * 
     * @return Response A redirect response to the Stripe checkout URL or back to the cart if empty.
     */
    #[Route('/checkout/sql', name: 'stripe_checkout_sql')]
    public function checkoutSql(Request $request, StripeService $stripeService): Response
    {
        $cart = $request->getSession()->get('cart', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Your SQL shopping cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $checkoutUrl = $stripeService->createCheckoutSession($cart);
        return $this->redirect($checkoutUrl);
    }

    /**
     * Initiates Stripe checkout for a MongoDB-based shopping cart.
     *
     * @param Request $request The HTTP request containing the session.
     * @param StripeServiceMongo $stripeServiceMongo The service responsible for creating Stripe checkout sessions for MongoDB documents.
     * 
     * @return Response A redirect response to the Stripe checkout URL or back to the cart if empty.
     */
    #[Route('/checkout/mongo', name: 'stripe_checkout_mongo')]
    public function checkoutMongo(Request $request, StripeServiceMongo $stripeServiceMongo): Response
    {
        $cart = $request->getSession()->get('cart_mongo', []);

        if (empty($cart)) {
            $this->addFlash('error', 'Your MongoDB shopping cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $checkoutUrl = $stripeServiceMongo->createCheckoutSession($cart);
        return $this->redirect($checkoutUrl);
    }

    /**
     * Handles the redirect after a successful Stripe payment.
     * Determines if the purchase was for a SQL or MongoDB cart and redirects accordingly.
     *
     * @param SessionInterface $session The current session containing the cart.
     * 
     * @return Response Redirect to the appropriate success route or home if no command is detected.
     */
    #[Route('/stripe/success', name: 'stripe_success')]
    public function stripeSuccessRedirect(SessionInterface $session): Response
    {
        if ($session->has('cart_mongo') && !empty($session->get('cart_mongo'))) {
            return $this->redirectToRoute('stripe_success_mongo');
        }

        if ($session->has('cart') && !empty($session->get('cart'))) {
            return $this->redirectToRoute('stripe_success_sql');
        }

        $this->addFlash('warning', 'No command detected.');
        return $this->redirectToRoute('app_home');
    }

    /**
     * Handles successful Stripe payment for SQL-based orders.
     *
     * This action is triggered after a user completes a Stripe checkout.
     * @param SessionInterface $session The session service to access the user's cart.
     * @param EntityManagerInterface $em The Doctrine entity manager for persistence.
     *
     * @return Response Redirects to the homepage after processing the purchase.
     *
     * @throws AccessDeniedException If the user does not have ROLE_USER.
     */
    #[Route('/stripe/success/sql', name: 'stripe_success_sql')]
    public function stripeSuccessSql(
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $session->get('cart', []);
        if (empty($cart)) {
            $this->addFlash('warning', 'No command detected.');
            return $this->redirectToRoute('app_home');
        }

        /** @var User $user */
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        $command = new Command();
        $command->setUser($user);
        $command->setCreatedAt($now);

        $total = 0;

        foreach ($cart['lessons'] ?? [] as $lessonId => $val) {
            $lesson = $em->getRepository(Lesson::class)->find($lessonId);
            if ($lesson) {
                $item = new CommandItem();
                $item->setCommand($command);
                $item->setLesson($lesson);
                $item->setPrice($lesson->getPrice());
                $em->persist($item);

                $total += $lesson->getPrice();
                $user->addPurchasedLessons($lesson);
            }
        }

        foreach ($cart['courses'] ?? [] as $courseId => $val) {
            $course = $em->getRepository(Course::class)->find($courseId);
            if ($course) {
                $item = new CommandItem();
                $item->setCommand($command);
                $item->setCourse($course);
                $item->setPrice($course->getPrice());
                $em->persist($item);

                $total += $course->getPrice();
                $user->addPurchasedCourses($course);
            }
        }

        $payment = new Payment();
        $payment->setDate($now);
        $payment->setCreatedAt($now);
        $payment->setUpdatedAt($now);
        $payment->setCreatedBy($user);
        $payment->setUpdatedBy($user);
        $payment->setCommand($command);
        $payment->setMeans('Stripe');
        $payment->setSum($total);

        $em->persist($user);
        $em->persist($command);
        $em->persist($payment);
        $em->flush();

        $session->remove('cart');

        $this->addFlash('success', 'Thank you for your purchase (SQL).');
        return $this->redirectToRoute('app_home');
    }

    /**
     * Handles successful Stripe payment for MongoDB-based orders.
     *
     * This action is triggered after a user completes a Stripe checkout for MongoDB.
     *
     * @param SessionInterface $session The session service to access the user's MongoDB cart.
     * @param DocumentManager $dm The Doctrine MongoDB DocumentManager for persistence.
     *
     * @return Response Redirects to the homepage after processing the purchase.
     *
     * @throws AccessDeniedException If the user does not have ROLE_USER.
    */
    #[Route('/stripe/success/mongo', name: 'stripe_success_mongo')]
    public function stripeSuccessMongo(
        SessionInterface $session,
        DocumentManager $dm
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $session->get('cart_mongo', []);
        if (empty($cart)) {
            $this->addFlash('warning', 'No command detected.');
            return $this->redirectToRoute('app_home');
        }

        /** @var User $user */
        $user = $this->getUser();
        $now = new \DateTimeImmutable();

        $userDocument = $dm->getRepository(UserDocument::class)->findOneBy(['email' => $user->getEmail()]);
        if (!$userDocument) {
            $userDocument = new UserDocument();
            $userDocument->setEmail($user->getEmail());
            $userDocument->setRoles($user->getRoles());
            if ($user->getCreatedAt()) {
                $userDocument->setCreatedAt(\DateTimeImmutable::createFromInterface($user->getCreatedAt()));
            }
            if ($user->getUpdatedAt()) {
                $userDocument->setUpdatedAt(\DateTimeImmutable::createFromInterface($user->getUpdatedAt()));
            }
            $dm->persist($userDocument);
        }

        $commandDocument = new CommandDocument();
        $commandDocument->setUserId((int)$user->getId());
        $commandDocument->setCreatedAt($now);
        $dm->persist($commandDocument);

        $total = 0;

        foreach ($cart['lessons'] ?? [] as $lessonId => $val) {
            $lesson = $dm->getRepository(LessonDocument::class)->find($lessonId);
            if ($lesson) {
                $item = new CommandItemDocument();
                $item->setCommand($commandDocument);
                $item->setLessonId($lesson->getId());
                $item->setPrice($lesson->getPrice());
                $dm->persist($item);

                $total += $lesson->getPrice();
                $userDocument->addPurchasedLessonsMongo($lesson);
            }
        }

        foreach ($cart['courses'] ?? [] as $courseId => $val) {
            $course = $dm->getRepository(CourseDocument::class)->find($courseId);
            if ($course) {
                $item = new CommandItemDocument();
                $item->setCommand($commandDocument);
                $item->setCourseId($course->getId());
                $item->setPrice($course->getPrice());
                $dm->persist($item);

                $total += $course->getPrice();
                $userDocument->addPurchasedCoursesMongo($course);
            }
        }

        $paymentDoc = new PaymentDocument();
        $paymentDoc->setDate($now);
        $paymentDoc->setCreatedAt($now);
        $paymentDoc->setUpdatedAt($now);
        $paymentDoc->setCreatedBy($userDocument);
        $paymentDoc->setUpdatedBy($userDocument);
        $paymentDoc->setCommand($commandDocument);
        $paymentDoc->setMeans('Stripe');
        $paymentDoc->setSum($total);
        $dm->persist($paymentDoc);

        $dm->persist($userDocument);
        $dm->flush();

        $session->remove('cart_mongo');

        $this->addFlash('success', 'Thank you for your purchase (MongoDB).');
        return $this->redirectToRoute('app_home');
    }
    /**
     * Renders the Stripe checkout cancellation page.
     *
     * This action is triggered when the user cancels a Stripe checkout.
     *
     * @return Response Renders the 'stripe/cancel.html.twig' template.
     */
    #[Route('/checkout/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        return $this->render('stripe/cancel.html.twig');
    }
}