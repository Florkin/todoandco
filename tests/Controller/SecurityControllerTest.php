<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $client;
    /**
     * @var CsrfTokenManager
     */
    private $tokenManager;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $this->tokenManager = $this->client->getContainer()->get('security.csrf.token_manager');
    }

    public function testDisplayLoginWhenNotLoggedIn()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Connectez vous');
    }

    public function testLoginWithBadCredentials()
    {
        $this->client->request('POST', '/login', [
            'email' => 'user@demo.com',
            'password' => 'badpassword',
            '_csrf_token' => $this->tokenManager->getToken('authenticate')
        ]);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessfullLogin()
    {
        $this->client->request('POST', '/login', [
            'email' => 'user@demo.com',
            'password' => 'demodemo',
            '_csrf_token' => $this->tokenManager->getToken('authenticate')
        ]);
        $this->assertResponseRedirects('/');
    }
}