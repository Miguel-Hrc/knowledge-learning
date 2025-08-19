<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Document\UserDocument;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MongoLoginTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    public function testMongoUserCanLogin(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $params = $container->get('parameter_bag');
        $dataSource = strtolower($params->get('app.data_source'));

        if (!in_array($dataSource, ['mongodb', 'both'], true)) {
            $this->markTestSkipped('MongoDB data source not enabled.');
        }

        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $dm = $container->get('doctrine_mongodb')->getManager();
        $user = new UserDocument();
        $user->setEmail('mongouser@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'MongoPassword123!'));
        $user->setRoles(['ROLE_USER']);

        $dm->persist($user);
        $dm->flush();

        $crawler = $client->request('GET', '/login-mongo');

        $this->assertGreaterThan(
            0,
            $crawler->filter('form')->count(),
            'Le formulaire de login MongoDB doit être présent sur la page.'
        );

        $form = $crawler->filter('form')->first()->form();
        $form['_username'] = 'mongouser@example.com';
        $form['_password'] = 'MongoPassword123!';

        $csrfInput = $crawler->filter('input[name="_csrf_token"]');
        if ($csrfInput->count() > 0) {
            $form['_csrf_token'] = $csrfInput->attr('value');
        }

        $client->submit($form);

        $this->assertResponseRedirects('/');
        $client->followRedirect();

        $this->assertStringContainsString('accueil', $client->getResponse()->getContent());
        $dm->remove($user);
        $dm->flush();
    }
}