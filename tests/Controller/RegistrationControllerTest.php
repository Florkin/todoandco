<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        $form = $crawler->filter('[name="user"]')->form([
            'user[username]' => 'testUsername',
            'user[email]' => 'test@test.com',
            'user[plainPassword][first]' => 'testpassword',
            'user[plainPassword][second]' => 'testpassword',
            'user[agreeTerms]' => 1
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/');
    }
}