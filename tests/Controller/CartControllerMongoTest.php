<?php

namespace App\Tests\Controller;

use App\Controller\CartController;
use App\Document\UserDocument;
use App\Document\LessonDocument;
use App\Service\UserDataAccessValidator;
use App\Service\StripeService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class CartControllerMongoTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    private DocumentManager $dm;
    private Session $session;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->dm = self::getContainer()->get('doctrine_mongodb.odm.document_manager');
        $this->session = new Session(new MockArraySessionStorage());


        $this->dm->getDocumentCollection(UserDocument::class)->drop();
        $this->dm->getDocumentCollection(LessonDocument::class)->drop();

        $user = new UserDocument();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_CLIENT']);
        $this->dm->persist($user);

        $lessonMongo = new LessonDocument();
        $lessonMongo->setTitle('Leçon Mongo');
        $lessonMongo->setContent('Contenu Mongo');
        $lessonMongo->setPrice(42.0);
        $this->dm->persist($lessonMongo);

        $this->dm->flush();
    }

    public function testAddLessonMongoWithoutSecurityBundle(): void
    {
        $user = $this->dm->getRepository(UserDocument::class)->findOneBy(['email' => 'test@example.com']);
        $lessonMongo = $this->dm->getRepository(LessonDocument::class)->findOneBy(['title' => 'Leçon Mongo']);

        $paramsMock = $this->createMock(ParameterBagInterface::class);
        $paramsMock->method('get')->willReturn('mongodb');

        $validator = $this->createMock(UserDataAccessValidator::class);
        $validator->expects($this->once())
                  ->method('validateUserAccessToLesson')
                  ->with($user, $lessonMongo);

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
        $twigMock->method('render')->willReturn('<html>Panier Mongo</html>');

        $controller = new CartController(
            $paramsMock,
            null, 
            $this->dm,
            $validator,
            $tokenStorageMock,
            $authCheckerMock,
            $urlGeneratorMock,
            $twigMock
        );

        $controller->addLessonMongo($lessonMongo->getId(), $this->session);

        $this->assertSame(['lessons' => [$lessonMongo->getId() => true]], $this->session->get('cart_mongo'));
        $sessionId = $stripeService->createCheckoutSession([$lessonMongo->getId() => 1], $user);
        $this->assertSame('fake_stripe_session_id', $sessionId);
    }
}
