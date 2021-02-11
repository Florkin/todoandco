<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testAddUser()
    {
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->filter('[name="user"]')->form([
            'user[username]' => 'testUsername',
            'user[email]' => 'test@test.com',
            'user[plainPassword][first]' => 'testpassword',
            'user[plainPassword][second]' => 'testpassword'
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/');
    }

    public function testLogout()
    {
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testUserCantAccessList()
    {
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testUserList()
    {
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $this->client->loginUser($user);
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }

    public function testUserEdit()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $user = $userRepo->findOneBy([
            'email' => 'user@demo.com',
        ]);
        $admin = $userRepo->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', 'users/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->submit($this->getUserForm($crawler));
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");

        // Check if Admin is granted too
        $this->client->restart();
        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', 'users/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $form = $this->getUserForm($crawler);
        $form['user[admin]']->tick();
        $this->client->submit($form);
        $user = $userRepo->find($user->getId());
        $this->assertTrue(in_array('ROLE_ADMIN', $user->getRoles()));
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");

    }

    public function testUserEditByForbiddenUser()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $user = $userRepo->findOneBy([
            'email' => 'otheruser@demo.com',
        ]);
        $userToModify = $userRepo->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $this->client->request('GET', 'users/' . $userToModify->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testUserDelete()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $admin = $userRepo->findOneBy([
            'email' => 'admin@demo.com',
        ]);
        $this->client->loginUser($admin);
        $user = $userRepo->findOneBy([
            'email' => 'user@demo.com',
        ]);
        $id = $user->getId();
        $crawler = $this->client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $user->getId())->form([
            '_method' => 'DELETE'
        ]);
        $this->client->submit($form);
        $user = $userRepo->find($id);
        $this->assertNull($user);
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");
    }

    public function testUserDeleteInvalidToken()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $admin = $userRepo->findOneBy([
            'email' => 'admin@demo.com',
        ]);
        $this->client->loginUser($admin);
        $user = $userRepo->findOneBy([
            'email' => 'user@demo.com',
        ]);
        $id = $user->getId();
        $crawler = $this->client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $user->getId())->form([
            '_method' => 'DELETE',
            '_token' => 'invalid-token'
        ]);
        $this->client->submit($form);
        $user = $userRepo->find($id);
        $this->assertNotNull($user);
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testAuthenticatedUserDelete()
    {
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $user = $userRepo->findOneBy([
            'email' => 'admin@demo.com',
        ]);
        $this->client->loginUser($user);
        $id = $user->getId();
        $crawler = $this->client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $user->getId())->form([
            '_method' => 'DELETE'
        ]);
        $this->client->submit($form);
        $user = $userRepo->find($id);
        $this->assertNull($user);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");
    }

    private function getUserForm(Crawler $crawler)
    {
        $form = $crawler->filter('[name="user"]')->form([
            'user[username]' => 'testUsernameMod',
            'user[email]' => 'test@test.com',
            'user[plainPassword][first]' => 'testpassword',
            'user[plainPassword][second]' => 'testpassword'
        ]);

        return $form;
    }
}