<?php


namespace App\Tests\Repository;


use App\DataFixtures\TaskFixtures;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    public function testCount()
    {
        self::bootKernel();
        $this->loadFixtures([TaskFixtures::class]);
        $tasks = self::$container->get(TaskRepository::class)->count([]);
        $this->assertEquals(400, $tasks);
    }

    public function testFindByQueryIsQuery()
    {
        self::bootKernel();
        $query = self::$container->get(TaskRepository::class)->findByQuery();
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testFindByQueryReturnTasks()
    {
        self::bootKernel();
        $tasks = self::$container->get(TaskRepository::class)->findByQuery()->getResult();
        $testTask = true;
        foreach ($tasks as $task) {
            if (!$task instanceof Task) {
                $testTask = false;
                break;
            }
        }
        $this->assertTrue($testTask);
    }

    public function testFindByQueryWithOptions()
    {
        self::bootKernel();
        $tasks = self::$container->get(TaskRepository::class)->findByQuery(['done' => true])->getResult();
        $testTask = true;
        foreach ($tasks as $task) {
            if (!$task instanceof Task || !$task->isDone()) {
                $testTask = false;
                break;
            }
        }
        $this->assertTrue($testTask);

        $tasks = self::$container->get(TaskRepository::class)->findByQuery(['done' => 0])->getResult();
        $testTask = true;
        foreach ($tasks as $task) {
            if (!$task instanceof Task || $task->isDone()) {
                $testTask = false;
                break;
            }
        }
        $this->assertTrue($testTask);
    }

    public function testFindByUserQueryIsQuery()
    {
        self::bootKernel();
        $query = self::$container->get(TaskRepository::class)->findByUserQuery($this->getUser());
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testFindByUserQueryReturnUserTasks()
    {
        self::bootKernel();
        $user = $this->getUser();
        $tasks = self::$container->get(TaskRepository::class)->findByUserQuery($user)->getResult();
        $testTask = true;
        foreach ($tasks as $task) {
            if (!$task instanceof Task || $task->getUser()->getId() !== $user->getId()) {
                $testTask = false;
                break;
            }
        }
        $this->assertTrue($testTask);
    }

    public function testFindAnonymousQueryIsQuery()
    {
        self::bootKernel();
        $query = self::$container->get(TaskRepository::class)->findAnonymousQuery();
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testFindAnonymousQueryReturnAnonymousTasks()
    {
        self::bootKernel();
        $user = $this->getUser();
        $tasks = self::$container->get(TaskRepository::class)->findAnonymousQuery()->getResult();
        $testTask = true;
        foreach ($tasks as $task) {
            if (!$task instanceof Task || null !== $task->getUser()) {
                $testTask = false;
                break;
            }
        }
        $this->assertTrue($testTask);
    }

    private function getUser() : User
    {
        return self::$container->get(UserRepository::class)->findOneBy([
            'email' => 'user@demo.com'
        ]);

    }
}