<?php


namespace App\Tests\Entity;


use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    public function getEntity(): Task
    {
        return (new Task())
            ->setTitle('Test Title')
            ->setDone(true)
            ->setContent('lorem ipsum description')
            ->setCreatedAt(new \DateTime());
    }

    public function assertHasError(Task $task, int $number = 0)
    {
        self::bootKernel();
        $errors = (self::$container->get('validator')->validate($task));
        $this->assertCount($number, $errors);
    }

    public function testValidEntity()
    {
        $this->assertHasError($this->getEntity(), 0);
    }

    public function testInvalidEntity()
    {
        $task = $this->getEntity();
        $task->setTitle('te')->setContent('te');
        $this->assertHasError($task, 2);
    }

    public function testInvalidBlankEntity()
    {
        $task = $this->getEntity();
        $task->setTitle('')->setContent('');
        $this->assertHasError($task, 4);
    }
}