<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BasketItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BasketItem>
 *
 * @method BasketItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method BasketItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method BasketItem[] findAll()
 * @method BasketItem[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BasketItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BasketItem::class);
    }

    public function getBasketItemsByUser(User $user): array
    {
        $queryBuilder = $this->createQueryBuilder('bi')
            ->select('bi', 'i')
            ->leftJoin('bi.item', 'i')
            ->andWhere('bi.user = :user')
            ->setParameter('user', $user);

        return $queryBuilder->getQuery()->getResult();
    }
}
