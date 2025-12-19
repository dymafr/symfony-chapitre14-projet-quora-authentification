<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Question>
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * @return Question[]
     */
    public function getLastQuestionsWithAuthors(): array
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.author', 'a')
            ->addSelect('a')
            ->orderBy('q.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Question|null
     */
    public function getQuestionsWithCommentsAndAuthors(int $id): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.id = :id')
            ->setParameter('id', $id)
            ->leftJoin('q.author', 'a')
            ->addSelect('a')
            ->leftJoin('q.comments', 'c')
            ->addSelect('c')
            ->leftJoin('c.author', 'ca')
            ->addSelect('ca')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
