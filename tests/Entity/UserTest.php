<?php


namespace App\Tests\Entity;


use App\DataFixtures\TaskFixtures;
use App\DataFixtures\UserFixtures;
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
        $this->taskRepository = self::$container->get(TaskRepository::class);
        $this->loadFixtures([TaskFixtures::class, UserFixtures::class]);
    }

    public function getTask()
    {
        return $this->taskRepository->findOneBy([
            'done' => false
        ]);
    }

    public function getEntity(): User
    {
        return (new User())
            ->setUsername('TestUsername')
            ->setEmail('test@test.com')
            ->addTask($this->getTask())
            ->setPassword('testpassword')
            ;
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
}