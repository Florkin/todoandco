<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    use FixturesTrait;
    
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
        $userRepository = self::$container->get(UserRepository::class);
        $this->user = $userRepository->findOneBy(['email' => 'user@demo.com']);
        $this->admin = $userRepository->findOneBy(['email' => 'admin@demo.com']);
        $this->loadFixtures([UserFixtures::class]);
    }

    public function testRedirectToLogin()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserAccessHome()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
    }

    public function testAdminHasAdminNavbar()
    {
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }
}