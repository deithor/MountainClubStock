<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BasketItem;
use App\Entity\Item;
use App\Entity\User;
use App\Repository\BasketItemRepository;

readonly class BasketItemService
{
    public function __construct(private BasketItemRepository $basketItemRepository)
    {
    }

    public function createBasketItem(User $user, Item $item, int $quantity): void
    {
        $basketItem = $this->basketItemRepository->findOneBy(['user' => $user, 'item' => $item]);

        if ($basketItem) {
            $basketItem->setQuantity($basketItem->getQuantity() + $quantity);
        } else {
            $basketItem = new BasketItem();
            $basketItem
                ->setItem($item)
                ->setUser($user)
                ->setQuantity($quantity);
        }

        $this->basketItemRepository->save($basketItem, true);
    }
}
