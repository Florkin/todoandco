<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{


    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testRegister()
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->filter('[name="user"]')->form([
            'user[username]' => 'testUsername',
            'user[email]' => 'test@test.com',
            'user[plainPassword][first]' => 'testpassword',
            'user[plainPassword][second]' => 'testpassword',
            'user[agreeTerms]' => 1
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');
    }
}