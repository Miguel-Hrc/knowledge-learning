<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OrmLoginTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    public function testOrmUserCanLogin(): void
    {
        $client = static::createClient();

        // Création de l'utilisateur en base
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('ormuser@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'ormpass'));
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();
        $em->clear();

        $crawler = $client->request('GET', '/login-sql');

        $this->assertGreaterThan(
            0,
            $crawler->filter('form')->count(),
            'Le formulaire de login doit être présent sur la page.'
        );

        $form = $crawler->filter('form')->first()->form();


        $csrfTokenInput = $crawler->filter('input[name="_csrf_token"]');
        if ($csrfTokenInput->count() > 0) {
            $form['_csrf_token'] = $csrfTokenInput->attr('value');
        }

        $form['_username'] = 'ormuser@example.com';
        $form['_password'] = 'ormpass';

        $client->submit($form);

        $this->assertResponseRedirects('/');

        $client->followRedirect();

        $this->assertStringContainsString('accueil', $client->getResponse()->getContent());
    }
}
