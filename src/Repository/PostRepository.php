<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findByCriteria(string $criteria = 'date', string $order = 'desc', string $search = '')
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c');

        if ($search) {
            $qb->where('p.title LIKE :searchTerm OR p.content LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $search . '%');
        }

        switch ($criteria) {
            case 'category':
                $qb->orderBy('c.name', $order);
                break;
            case 'likes':
                $qb->leftJoin('p.postFeedback', 'f_l', Join::WITH, 'f_l.type = :like')
                    ->addSelect('COUNT(f_l.id) AS HIDDEN likes_count')
                    ->setParameter('like', 'like')
                    ->groupBy('p.id')
                    ->orderBy('likes_count', $order);
                break;
            case 'dislikes':
                $qb->leftJoin('p.postFeedback', 'f_d', Join::WITH, 'f_d.type = :dislike')
                    ->addSelect('COUNT(f_d.id) AS HIDDEN dislikes_count')
                    ->setParameter('dislike', 'dislike')
                    ->groupBy('p.id')
                    ->orderBy('dislikes_count', $order);
                break;
            default:
                $qb->orderBy('p.createdAt', $order);
                break;
        }

        return $qb->getQuery()->getResult();
    }

    public function findLikedPostsByUser($user)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.postFeedback', 'pf')
            ->andWhere('pf.user = :user')
            ->andWhere('pf.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', 'like')
            ->getQuery()
            ->getResult();
    }
}