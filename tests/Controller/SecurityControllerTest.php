<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;
    
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testDisplayLoginWhenNotLoggedIn()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Connectez vous');
    }

    public function testLoginWithBadCredentials()
    {
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $this->client->request('POST', '/login', [
            'email' => 'user@demo.com',
            'password' => 'badpassword',
            '_csrf_token' => $csrfToken
        ]);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessfullLogin()
    {
        $this->loadFixtures([UserFixtures::class]);
        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $this->client->request('POST', '/login', [
            'email' => 'user@demo.com',
            'password' => 'demodemo',
            '_csrf_token' => $csrfToken
        ]);
        $this->assertResponseRedirects('/');
    }
}