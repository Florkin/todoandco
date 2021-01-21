<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TaskFixtures extends Fixture
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $task = new Task();
            $task->setDone($this->faker->boolean(20));
            $task->setTitle($this->faker->text(50));
            $task->setContent($this->faker->text(300));
            $manager->persist($task);
        }

        $manager->flush();
    }
}
