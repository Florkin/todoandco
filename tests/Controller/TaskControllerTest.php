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
use Symfony\Component\Security\Core\User\UserInterface;

class TaskControllerTest extends WebTestCase
{

    use FixturesTrait;

    private $client;
    /**
     * @var UserInterface
     */
    private $user;
    /**
     * @var UserInterface
     */
    private $admin;
    /**
     * @var UserInterface
     */
    private $userNoTask;
    /**
     * @var Task
     */
    private $userTask;
    /**
     * @var Task
     */
    private $notFromUserTask;
    /**
     * @var Task
     */
    private $nullUserTask;

    public function setUp()
    {
        $this->client = static::createClient();
        $userRepository = self::$container->get(UserRepository::class);
        $taskRepository = self::$container->get(TaskRepository::class);
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
        $this->user = $userRepository->findOneBy(['email' => 'user@demo.com']);
        $this->userNoTask = $userRepository->findOneBy(['email' => 'notaskuser@demo.com']);
        $this->admin = $userRepository->findOneBy(['email' => 'admin@demo.com']);
        $this->userTask = $taskRepository->findOneBy(['user' => $this->user]);
        $this->notFromUserTask = $taskRepository->findOneByNot('user', $this->user)[0];
        $this->nullUserTask = $taskRepository->findOneBy(['user' => null]);
    }

    public function testRedirectToLogin()
    {
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseRedirects('/login');
    }

    public function testUserTaskIndex()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
    }

    public function testUserTaskIndexNoTask()
    {
        $this->client->loginUser($this->userNoTask);
        $crawler = $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('#admin_navbar');
        $this->assertSelectorExists('.alert.alert-warning');
        $this->assertSelectorNotExists('.task-miniature');
    }

    public function testUserCantAccessAdminTaskIndex()
    {
        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', 'admin/tasks');
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAdminTaskIndex()
    {
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', 'admin/tasks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('#admin_navbar');
    }

    public function testTaskNew()
    {
        $this->client->loginUser($this->user);
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
        $this->client->loginUser($this->user);
        $task = $this->userTask;
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
        $this->client->loginUser($this->user);

        /** @var Task $task */
        $task = $this->userTask;
        $done = $task->isDone();
        $crawler = $this->client->xmlHttpRequest('POST', '/tasks/' . $task->getId() . '/toggle');
        $this->assertNotEquals($task->isDone(), $done);

    }

    public function testForbiddenTaskEdit()
    {
        $this->client->loginUser($this->user);
        $task = $this->notFromUserTask;
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testForbiddenTaskDelete()
    {
        $this->client->loginUser($this->user);
        $task = $this->notFromUserTask;
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testTaskDelete()
    {
        $this->client->loginUser($this->user);
        $task = $this->userTask;
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $task->getId() . '/delete');
        $this->assertNull($task->getId());
    }

    public function testTaskDeletePermissions()
    {
        $this->client->loginUser($this->userNoTask);
        $taskToDelete = $this->userTask;
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $taskToDelete->getId() . '/delete');
        $this->assertNotNull($taskToDelete->getId());
    }

    public function testAnonymousTaskDeletePermissions()
    {
        $this->client->loginUser($this->user);
        $taskToDelete = $this->nullUserTask;
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $taskToDelete->getId() . '/delete');
        $this->assertNotNull($taskToDelete->getId());
    }

    public function testAnonymousTaskDelete()
    {
        $this->client->loginUser($this->admin);
        $taskToDelete = $this->nullUserTask;
        $crawler = $this->client->xmlHttpRequest('GET', '/tasks/' . $taskToDelete->getId() . '/delete');
        $this->assertNull($taskToDelete->getId());
    }

    public function testForbiddenAnonymousTaskEdit()
    {
        $this->client->loginUser($this->user);
        $task = $this->nullUserTask;
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAnonymousTaskEdit()
    {
        $this->client->loginUser($this->admin);
        $task = $this->nullUserTask;
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