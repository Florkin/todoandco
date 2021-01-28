<?php


namespace App\Tests\Repository;


use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\Query;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    public function testCount()
    {
        self::bootKernel();
        $this->loadFixtures([UserFixtures::class]);
        $users = self::$container->get(UserRepository::class)->count([]);
        $this->assertEquals(20, $users);
    }

    public function testFindAllQueryIsQuery()
    {
        self::bootKernel();
        $query = self::$container->get(UserRepository::class)->findAllQuery();
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testFindAllQueryReturnUsers()
    {
        self::bootKernel();
        $users = self::$container->get(UserRepository::class)->findAllQuery()->getResult();
        $testUser = true;
        foreach ($users as $user) {
            if (!$user instanceof User) {
                $testUser = false;
                break;
            }
        }
        $this->assertTrue($testUser);
    }
}