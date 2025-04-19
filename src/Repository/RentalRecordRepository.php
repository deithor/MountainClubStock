<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RentalRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RentalRecord>
 *
 * @method RentalRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method RentalRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method RentalRecord[] findAll()
 * @method RentalRecord[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RentalRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentalRecord::class);
    }

    public function getItemAvailableQuantity(int $itemId, ?int $rentalRecordId = null): int
    {
        $queryBuilder = $this->createQueryBuilder('rr');

        $queryBuilder
            ->select('(COALESCE(i.quantity,0)) - COALESCE(SUM(rr.quantity),0)')
            ->leftJoin('rr.item', 'i')
            ->andWhere('i.id = :id')
            ->andWhere('rr.returnedAt IS NULL')
            ->setParameter('id', $itemId);

        if ($rentalRecordId) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->neq('rr.id', ':rentalRecordId'))
                ->setParameter('rentalRecordId', $rentalRecordId);
        }

        return (int)$queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
    }
}
