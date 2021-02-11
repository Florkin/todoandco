<?php


namespace App\Tests\Entity;


use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    private $tasks;

    use FixturesTrait;

    public function setUp()
    {
        self::bootKernel();
        $this->loadFixtures([TaskFixtures::class]);
        $this->taskRepository = self::$container->get(TaskRepository::class);
    }

    public function getTask()
    {
        return $this->taskRepository->findOneBy([
            'done' => false
        ]);
    }

    public function getTasks()
    {
        return $this->taskRepository->findBy([
            'user' => null
        ]);
    }

    public function getEntity(): User
    {
        return (new User())
            ->setUsername('TestUsername')
            ->setEmail('test@test.com')
            ->addTask($this->getTask())
            ->setPassword('testpassword');
    }

    public function assertHasError(User $user, int $number = 0)
    {
        self::bootKernel();
        $errors = (self::$container->get('validator')->validate($user));
        $this->assertCount($number, $errors);
    }

    public function testValidEntity()
    {
        $this->assertHasError($this->getEntity(), 0);
    }

    public function testInvalidEntity()
    {
        $user = $this->getEntity()->setEmail('invalidemail.com');
        $this->assertHasError($user, 1);
    }

    public function testInvalidBlankEntity()
    {
        $user = $this->getEntity();
        $user->setEmail('')->setUsername('');
        $this->assertHasError($user, 2);
    }

    public function testUniqueEmail()
    {
        $user = $this->getEntity();
        $user->setEmail('user@demo.com');
        $this->assertHasError($user, 1);
    }

    public function testUniqueUsername()
    {
        $user = $this->getEntity();
        $user->setUsername('UserDemo');
        $this->assertHasError($user, 1);
    }

    public function testSetRole()
    {
        $user = $this->getEntity();
        $user->addRole('ROLE_ADMIN');
        $this->assertTrue(in_array('ROLE_ADMIN', $user->getRoles()));
        $user->setRoles(['ROLE_TEST']);
        $this->assertTrue(in_array('ROLE_TEST', $user->getRoles()));
        $this->assertFalse(in_array('ROLE_ADMIN', $user->getRoles()));
    }

    public function testRemoveRole()
    {
        $user = $this->getEntity();
        $user->addRole('ROLE_ADMIN');
        $user->removeRole('ROLE_ADMIN');
        $this->assertFalse(in_array('ROLE_ADMIN', $user->getRoles()));
    }

    public function testGetTasks()
    {
        $user = $this->getEntity();
        $tasks = $this->getTasks();
        foreach ($tasks as $task) {
            $user->addTask($task);
        };
        $tasks = $user->getTasks();
        $testInstance = true;
        foreach ($tasks as $task) {
            if (!$task instanceof Task) {
                $testInstance = false;
                break;
            }
        };
        $this->assertTrue($testInstance);
    }

    public function testRemoveTask()
    {
        $tasks = $this->getTasks();
        $user = $this->getEntity();
        foreach ($tasks as $task) {
            $user->addTask($task);
        };

        $taskToRemove = $tasks[array_rand($tasks)];
        $user->removeTask($taskToRemove);
        $test = true;
        foreach ($user->getTasks() as $task) {
            if ($task->getId() === $taskToRemove->getId()) {
                $test = false;
                break;
            }
        };
        $this->assertTrue($test);
    }
}