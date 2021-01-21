<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixtures
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        Parent::__construct();
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('AdminDemo');
        $user->setEmail('admin@demo.com');
        $user->setPassword($this->encoder->encodePassword($user, 'demodemo'));
        $user->setRoles(['ROLE_ADMIN']);
        $this->addReference(User::class.'_0', $user);
        $manager->persist($user);

        for ($i = 1; $i <= Self::NUMBER_OF_USERS; $i++) {
            $user = new User();
            $user->setUsername($this->faker->userName);
            $user->setEmail($this->faker->email);
            $user->setPassword($this->encoder->encodePassword($user, 'demodemo'));
            $this->addReference(User::class.'_'.$i, $user);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
