<?php

namespace App\Tests\Controller;

use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{

    use FixturesTrait;

    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testRedirectToLogin()
    {
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
    }

    public function testUserTaskIndex()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
    }

    public function testUserTaskIndexNoTask()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'notaskuser@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
        $this->assertSelectorExists('.alert.alert-warning');
        $this->assertSelectorNotExists('.task-miniature');
    }

    public function testUserCantAccessAdminTaskIndex()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', 'admin/tasks');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAdminTaskIndex()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', 'admin/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }

    public function testTaskNew()
    {
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/tasks/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        //Check form submit
        $form = $crawler->filter('[name="task"]')->form([
            'task[title]' => 'Lorem Ipsum',
            'task[content]' => 'Lorem Ipsum dolor sit amet',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskEdit()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneBy([
            'user' => $user
        ]);
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        //Check form submit
        $form = $crawler->filter('[name="task"]')->form([
            'task[title]' => 'Lorem Ipsum',
            'task[content]' => 'Lorem Ipsum dolor sit amet',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskToggle()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);

        /** @var Task $task */
        $task = self::$container->get(TaskRepository::class)->findOneBy([
            'user' => $user
        ]);
        $done = $task->isDone();
        $crawler = $this->client->xmlHttpRequest('POST', '/tasks/' . $task->getId() . '/toggle');
        $this->assertNotEquals($task->isDone(), $done);

    }

    public function testForbiddenTaskEdit()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneByNot('user', $user);
        $crawler = $this->client->request('GET', '/tasks/' . $task[0]->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testForbiddenTaskDelete()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneByNot('user', $user);
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $task[0]->getId() . '/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testTaskDelete()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneBy(['user' => $user]);
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertNull($task->getId());
    }

    public function testTaskDeletePermissions()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'notaskuser@demo.com'
        ]);
        $taskUser = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $taskToDelete = self::$container->get(TaskRepository::class)->findOneBy(['user' => $taskUser]);
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $taskToDelete->getId() . '/delete');
        $this->assertNotNull($taskToDelete->getId());
    }

    public function testAnonymousTaskDeletePermissions()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $taskToDelete = self::$container->get(TaskRepository::class)->findOneBy(['user' => null]);
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $taskToDelete->getId() . '/delete');
        $this->assertNotNull($taskToDelete->getId());
    }

    public function testAnonymousTaskDelete()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $admin = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $this->client->loginUser($admin);
        $taskToDelete = self::$container->get(TaskRepository::class)->findOneBy(['user' => null]);
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $taskToDelete->getId() . '/delete');
        $this->assertNull($taskToDelete->getId());
    }

    public function testForbiddenAnonymousTaskEdit()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $this->client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneBy(['user' => null]);
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAnonymousTaskEdit()
    {
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $this->client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneBy([
            'user' => null
        ]);
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        //Check form submit
        $form = $crawler->filter('[name="task"]')->form([
            'task[title]' => 'Lorem Ipsum',
            'task[content]' => 'Lorem Ipsum dolor sit amet',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/tasks');
    }
}