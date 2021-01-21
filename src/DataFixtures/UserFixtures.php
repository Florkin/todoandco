<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->faker = Factory::create();
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('AdminDemo');
        $user->setEmail('admin@demo.com');
        $user->setPassword($this->encoder->encodePassword($user, 'demodemo'));
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setUsername($this->faker->userName);
            $user->setEmail($this->faker->email);
            $user->setPassword($this->encoder->encodePassword($user, 'demodemo'));
            $manager->persist($user);
        }
        $manager->flush();
    }
}
