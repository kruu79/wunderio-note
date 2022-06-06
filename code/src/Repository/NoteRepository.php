<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }


    /**
     * @param Note $entity
     * @param bool $flush
     */
    public function add(Note $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    /**
     * @param Note $entity
     * @param bool $flush
     */
    public function remove(Note $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }


    /**
     * Returns an array with Notes.
     *
     * @param array $params ['search' => 'query string', 'orderByCreatedAt' => 'ASC'/'DESC', 'limit' => int]
     * @return Note[]
     */
    public function findAllWithParameters(Array $params)
    {

        $builder = $this->createQueryBuilder('n');

        if (isset($params['search']))
            $builder->andWhere('n.text LIKE :search')
                ->setParameter('search', '%'.$params['search'].'%');

        if (isset($params['orderByCreatedAt']))
            $builder->orderBy('n.created_at', $params['orderByCreatedAt']);

        if (isset($params['limit']))
            $builder->setMaxResults($params['limit']);

        return $builder->getQuery()
            ->getResult();
    }

}
