<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepositoryMongo;

class RegistrationMongoTest extends WebTestCase
{   
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    public function testMongoUserRegistration(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $params = $container->get('parameter_bag');
        $dataSource = strtolower($params->get('app.data_source'));

        if (!in_array($dataSource, ['mongodb', 'both'], true)) {
            $this->markTestSkipped('MongoDB data source not enabled.');
        }

        $crawler = $client->request('GET', '/register');
        $formMongo = $crawler->filter('form[name="registration_form_mongo"]');
        $this->assertGreaterThan(0, $formMongo->count(), 'MongoDB registration form not found');

        $form = $formMongo->form([
            'registration_form_mongo[email]' => 'mongouser@example.com',
            'registration_form_mongo[plainPassword][first]' => 'MongoPassword123!',
            'registration_form_mongo[plainPassword][second]' => 'MongoPassword123!',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/register/check-email');

        $userRepositoryMongo = $container->get(UserRepositoryMongo::class);
        $userDoc = $userRepositoryMongo->findOneBy(['email' => 'mongouser@example.com']);
        $this->assertNotNull($userDoc);
        $this->assertFalse($userDoc->isVerified());

        $userRepositoryMongo->remove($userDoc, true);
    }
}