<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends BaseFixtures implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < Self::NUMBER_OF_TASKS; $i++) {
            $isAnonymous = $this->faker->boolean(10);
            $task = new Task();
            $task->setDone($this->faker->boolean(20));
            $task->setTitle($this->faker->text(40));
            $task->setCreatedAt($this->faker->dateTimeBetween("-2 years", "now"));
            $task->setContent($this->faker->text(200));
            if (!$isAnonymous) {
                $task->setUser($this->getReference(User::class.'_'.$this->faker->numberBetween(1, Self::NUMBER_OF_USERS)));
            }
            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
