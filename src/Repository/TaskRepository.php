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

    public function findByUserQuery($user, array $options = null)
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.user = ' . $user->getId())
            ->orderBy('t.createdAt', 'DESC');

        $this->addOptions($options, $query);
        return $query->getQuery();
    }

    public function findByQuery(array $options = null)
    {
        $query = $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC');

        $this->addOptions($options, $query);
        return $query->getQuery();
    }

    public function findAnonymousQuery(array $options = null)
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.user is NULL')
            ->orderBy('t.createdAt', 'DESC');

        $this->addOptions($options, $query);
        return $query->getQuery();
    }

    public function findOneByNot($field, $value)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->where($qb->expr()->not($qb->expr()->eq('a.' . $field, '?1')))
            ->setMaxResults(1)
            ->setParameter(1, $value);

        return $qb->getQuery()
            ->getResult();
    }

    private function addOptions($options, $query)
    {
        if (null === $options) {
            return;
        }
        foreach ($options as $key => $value) {
            if (null !== $value) {
                $query->andWhere('t.' . $key . ' = ' . $value);
            }
        }
    }

}
