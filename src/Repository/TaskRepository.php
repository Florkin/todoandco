<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByUserQuery($user, bool $done = null, bool $all = null, bool $anonymous = null)
    {
        $query = $this->createQueryBuilder('t');

        if (null !== $anonymous && $anonymous) {
            $query->andWhere('t.user is NULL');
            $all = true;
        } elseif (null !== $anonymous && !$anonymous) {
            $query->andWhere('t.user is NOT NULL');
        }

        if (null === $all || !$all) {
            $query->andWhere('t.user = ' . $user->getId());
        }

        if (null !== $done) {
            $query->andWhere('t.done = ' . (int)$done);
        }
        return $query->getQuery()->getResult();
    }
}
