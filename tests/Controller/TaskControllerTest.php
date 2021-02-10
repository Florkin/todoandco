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

    public function testRedirectToLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
    }

    public function testUserTaskIndex()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
    }

    public function testUserCantAccessAdminTaskIndex()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', 'admin/tasks');
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAdminTaskIndex()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'admin@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', 'admin/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }

    public function testTaskNew()
    {
        $client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $crawler = $client->request('GET', '/tasks/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        //Check form submit
        $form = $crawler->filter('[name="task"]')->form([
            'task[title]' => 'Lorem Ipsum',
            'task[content]' => 'Lorem Ipsum dolor sit amet',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskEdit()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneBy([
            'user' => $user
        ]);
        $crawler = $client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        //Check form submit
        $form = $crawler->filter('[name="task"]')->form([
            'task[title]' => 'Lorem Ipsum',
            'task[content]' => 'Lorem Ipsum dolor sit amet',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskToggle()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);

        /** @var Task $task */
        $task = self::$container->get(TaskRepository::class)->findOneBy([
            'user' => $user
        ]);
        $done = $task->isDone();
        $crawler = $client->xmlHttpRequest('POST', '/tasks/' . $task->getId() . '/toggle');
        $this->assertNotEquals($task->isDone(), $done);

    }

    public function testForbiddenTaskEdit()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneByNot('user', $user);
        $crawler = $client->request('GET', '/tasks/' . $task[0]->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testForbiddenTaskDelete()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneByNot('user', $user);
        $crawler = $client->xmlHttpRequest('GET', '/tasks/' . $task[0]->getId() . '/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testTaskDelete()
    {
        $client = static::createClient();
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $user = self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);
        $client->loginUser($user);
        $task = self::$container->get(TaskRepository::class)->findOneBy(['user' => $user]);
        $crawler = $client->xmlHttpRequest('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertNull($task->getId());
    }
}