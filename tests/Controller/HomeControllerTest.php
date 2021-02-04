<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testRedirectToLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserAccessHome()
    {
        $client = static::createClient();
        $users = $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
    }

    public function testAdminHasAdminNavbar()
    {
        $client = static::createClient();
        $users = $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }
}