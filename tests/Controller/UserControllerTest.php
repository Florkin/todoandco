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

    public function testAddUser()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/register');
        $form = $crawler->filter('[name="user"]')->form([
            'user[username]' => 'testUsername',
            'user[email]' => 'test@test.com',
            'user[plainPassword][first]' => 'testpassword',
            'user[plainPassword][second]' => 'testpassword'
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/');
    }

    public function testLogout()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/logout');
        $crawler = $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testUserCantAccessList()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testUserList()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $client->loginUser($user);
        $client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }

    public function testUserEdit()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $user = $userRepo->findOneBy([
            'email' => 'user@demo.com',
        ]);
        $admin = $userRepo->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', 'users/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->submit($this->getUserForm($crawler));
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");

        // Check if Admin is granted too
        $client->restart();
        $client->loginUser($admin);
        $crawler = $client->request('GET', 'users/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $form = $this->getUserForm($crawler);
        $form['user[admin]']->tick();
        $client->submit($form);
        $user = $userRepo->find($user->getId());
        $this->assertTrue(in_array('ROLE_ADMIN', $user->getRoles()));
        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");

    }

    public function testUserEditByForbiddenUser()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $user = $userRepo->findOneBy([
            'email' => 'otheruser@demo.com',
        ]);
        $userToModify = $userRepo->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $client->request('GET', 'users/' . $userToModify->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testUserDelete()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $admin = $userRepo->findOneBy([
            'email' => 'admin@demo.com',
        ]);
        $client->loginUser($admin);
        $user = $userRepo->findOneBy([
            'email' => 'user@demo.com',
        ]);
        $id = $user->getId();
        $crawler = $client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $user->getId())->form([
            '_method' => 'DELETE'
        ]);
        $client->submit($form);
        $user = $userRepo->find($id);
        $this->assertNull($user);
        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");
    }

    public function testUserDeleteInvalidToken()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $admin = $userRepo->findOneBy([
            'email' => 'admin@demo.com',
        ]);
        $client->loginUser($admin);
        $user = $userRepo->findOneBy([
            'email' => 'user@demo.com',
        ]);
        $id = $user->getId();
        $crawler = $client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $user->getId())->form([
            '_method' => 'DELETE',
            '_token' => 'invalid-token'
        ]);
        $client->submit($form);
        $user = $userRepo->find($id);
        $this->assertNotNull($user);
        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testAuthenticatedUserDelete()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $userRepo = self::$container->get(UserRepository::class);
        $user = $userRepo->findOneBy([
            'email' => 'admin@demo.com',
        ]);
        $client->loginUser($user);
        $id = $user->getId();
        $crawler = $client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $user->getId())->form([
            '_method' => 'DELETE'
        ]);
        $client->submit($form);
        $user = $userRepo->find($id);
        $this->assertNull($user);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
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