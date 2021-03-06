<?php

namespace App\DataFixtures;

use App\Builder\UserBuilder;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixtures
{
    /**
     * @var UserBuilder
     */
    private $builder;

    /**
     * UserFixtures constructor.
     * @param UserBuilder $builder
     */
    public function __construct(UserBuilder $builder)
    {
        Parent::__construct();
        $this->builder = $builder;
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->builder->build('UserDemo', 'user@demo.com', 'demodemo');
        $this->addReference(User::class . '_1', $user);

        $user = $this->builder->build('OtherUserDemo', 'otheruser@demo.com', 'demodemo');
        $this->addReference(User::class . '_2', $user);

        $user = $this->builder->build('AdminDemo', 'admin@demo.com', 'demodemo', ['ROLE_ADMIN']);
        $this->addReference(User::class . '_3', $user);

        $user = $this->builder->build('NoTaskUserDemo', 'notaskuser@demo.com', 'demodemo');

        for ($i = 4; $i <= Self::NUMBER_OF_USERS; $i++) {
            $user = $this->builder->build(
                $this->faker->userName,
                $this->faker->email,
                'demodemo'
            );
            $this->addReference(User::class . '_' . $i, $user);
        }
        $manager->flush();
    }
}
