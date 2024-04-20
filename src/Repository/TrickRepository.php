<?php

namespace App\Repository;

use App\DTO\TrickCardDTO;
use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trick>
 *
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    /**
     * @param int $offset
     * @param ?int $limit Page size.
     * 
     * @return TrickCardDTO[]
     */
    public function findTrickCards(int $offset = 0, ?int $limit = null): array
    {
        $builder = $this->createQueryBuilder('t')
            ->select(sprintf(
                'NEW %s(t.id, t.slug, t.name, p.filename, c.name)',
                TrickCardDTO::class
            ))
            ->leftJoin('t.mainPicture', 'p')
            ->leftJoin('t.category', 'c')
            ->orderBy('t.name', 'ASC');

        if ($limit) {
            $builder->setFirstResult($offset)->setMaxResults($limit);
        }

        return $builder->getQuery()->getResult();
    }
}
