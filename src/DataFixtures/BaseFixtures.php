<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BaseFixtures extends Fixture
{
    const NUMBER_OF_USERS = 10;
    const NUMBER_OF_TASKS = 200;

    protected $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        // no action
    }
}
