<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;


class UserControllerTest extends WebTestCase
{
    use FixturesTrait;

    private $client;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserInterface
     */
    private $user;
    /**
     * @var UserInterface
     */
    private $otherUser;
    /**
     * @var UserInterface
     */
    private $userNoTask;
    /**
     * @var UserInterface
     */
    private $admin;

    public function setUp()
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $this->userRepository = self::$container->get(UserRepository::class);
        $this->user = $this->userRepository->findOneBy(['email' => 'user@demo.com']);
        $this->otherUser = $this->userRepository->findOneBy(['email' => 'otheruser@demo.com']);
        $this->userNoTask = $this->userRepository->findOneBy(['email' => 'notaskuser@demo.com']);
        $this->admin = $this->userRepository->findOneBy(['email' => 'admin@demo.com']);
    }

    public function testAddUser()
    {
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/register');
        $this->client->submit($this->getUserForm($crawler));
        $this->assertResponseRedirects('/');
    }

    public function testLogout()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testUserCantAccessList()
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testUserList()
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }

    public function testUserEdit()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', 'users/' . $this->user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->submit($this->getUserForm($crawler));
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");

        // Check if Admin is granted too
        $this->client->restart();
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', 'users/' . $this->user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $form = $this->getUserForm($crawler);
        $form['user[admin]']->tick();
        $this->client->submit($form);
        $user = $this->userRepository->find($this->user->getId());
        $this->assertTrue(in_array('ROLE_ADMIN', $user->getRoles()));
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");

    }

    public function testUserEditByForbiddenUser()
    {
        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', 'users/' . $this->user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testUserDelete()
    {
        $this->client->loginUser($this->admin);
        $id = $this->user->getId();
        $crawler = $this->client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $this->user->getId())->form([
            '_method' => 'DELETE'
        ]);
        $this->client->submit($form);
        $user = $this->userRepository->find($id);
        $this->assertNull($user);
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");
    }

    public function testUserDeleteInvalidToken()
    {
        $this->client->loginUser($this->admin);
        $id = $this->user->getId();
        $crawler = $this->client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $this->user->getId())->form([
            '_method' => 'DELETE',
            '_token' => 'invalid-token'
        ]);
        $this->client->submit($form);
        $user = $this->userRepository->find($id);
        $this->assertNotNull($user);
        $this->assertResponseRedirects('/admin/users');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testAuthenticatedUserDelete()
    {
        $this->client->loginUser($this->admin);
        $id = $this->admin->getId();
        $crawler = $this->client->request('GET', 'admin/users');
        $form = $crawler->filter('#delete_form_user_' . $this->admin->getId())->form([
            '_method' => 'DELETE'
        ]);
        $this->client->submit($form);
        $user = $this->userRepository->find($id);
        $this->assertNull($user);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists(".alert.alert-success");
    }

    private function getUserForm(Crawler $crawler): Form
    {
        return $crawler->filter('[name="user"]')->form([
            'user[username]' => 'testUsernameMod',
            'user[email]' => 'test@test.com',
            'user[plainPassword][first]' => 'testpassword',
            'user[plainPassword][second]' => 'testpassword'
        ]);
    }
}