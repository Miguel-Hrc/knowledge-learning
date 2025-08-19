<?php

namespace App\Tests\Controller;

use App\Controller\CartController;
use App\Entity\User;
use App\Entity\Lesson;
use App\Service\UserDataAccessValidator;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class CartControllerOrmTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    private EntityManagerInterface $em;
    private Session $session;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $this->session = new Session(new MockArraySessionStorage());

        $conn = $this->em->getConnection();
        $conn->executeStatement('DELETE FROM lesson');
        $conn->executeStatement('DELETE FROM user');

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_CLIENT']);
        $this->em->persist($user);

        $lesson = new Lesson();
        $lesson->setTitle('Leçon Test');
        $lesson->setContent('Contenu test');
        $lesson->setPrice(26.0);
        $this->em->persist($lesson);

        $this->em->flush();
    }

    public function testAddLessonWithoutSecurityBundle(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $lesson = $this->em->getRepository(Lesson::class)->findOneBy(['title' => 'Leçon Test']);

        $paramsMock = $this->createMock(ParameterBagInterface::class);
        $paramsMock->method('get')->willReturn('orm');

        $validator = $this->createMock(UserDataAccessValidator::class);
        $validator->expects($this->once())
                  ->method('validateUserAccessToLesson')
                  ->with($user, $lesson);

        $stripeService = $this->createMock(StripeService::class);
        $stripeService->method('createCheckoutSession')
                      ->willReturn('fake_stripe_session_id');

        $tokenStorageMock = $this->createMock(TokenStorageInterface::class);
        $tokenStorageMock->method('getToken')->willReturn(new UsernamePasswordToken($user, 'main', $user->getRoles()));

        $authCheckerMock = $this->createMock(AuthorizationCheckerInterface::class);
        $authCheckerMock->method('isGranted')->with('ROLE_CLIENT')->willReturn(true);

        $urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);
        $urlGeneratorMock->method('generate')->willReturn('/cart');

        $twigMock = $this->createMock(Environment::class);
        $twigMock->method('render')->willReturn('<html>Panier</html>');

        $controller = new CartController(
            $paramsMock,
            $this->em,
            null,
            $validator,
            $tokenStorageMock,
            $authCheckerMock,
            $urlGeneratorMock,
            $twigMock,
        );

        $controller->addLesson($lesson, $this->session);

        $this->assertSame(['lessons' => [$lesson->getId() => true]], $this->session->get('cart'));
        $sessionId = $stripeService->createCheckoutSession([$lesson->getId() => 1], $user);
        $this->assertSame('fake_stripe_session_id', $sessionId);
    }
}