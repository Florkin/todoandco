<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testDisplayLogin()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Connectez vous');
        $this->assertSelectorNotExists('.alert.alert-danger');

    }

    public function testLoginWithBadCredentials()
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $client->request('POST', '/login', [
            'email' => 'user@demo.com',
            'password' => 'badpassword',
            '_csrf_token' => $csrfToken
        ]);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessfullLogin()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $client->request('POST', '/login', [
            'email' => 'user@demo.com',
            'password' => 'demodemo',
            '_csrf_token' => $csrfToken
        ]);
        $this->assertResponseRedirects('/');
    }
}