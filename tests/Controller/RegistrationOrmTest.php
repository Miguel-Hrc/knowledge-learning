<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationOrmTest extends WebTestCase
{   
    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }
    public function testOrmUserRegistration(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register'); // ton route registration

        $form = $crawler->filter('form[name="registration_form_sql"]');
        $this->assertGreaterThan(0, $form->count(), 'registration form not found');

        $form = $form->form([
            'registration_form_sql[email]' => 'user_test@example.com',
            'registration_form_sql[plainPassword][first]' => 'password123',
            'registration_form_sql[plainPassword][second]' => 'password123',
        ]);


        $client->submit($form);

        $this->assertResponseRedirects();
        $client->followRedirect();

$this->assertSelectorTextContains('body', 'Vérifier votre email');    }
}