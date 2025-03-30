<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BasketItem;
use App\Entity\Item;
use App\Entity\RentalRecord;
use App\Entity\User;
use App\Repository\BasketItemRepository;
use App\Repository\RentalRecordRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class BasketItemService
{
    public function __construct(
        private BasketItemRepository $basketItemRepository,
        private RentalRecordRepository $rentalRecordRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    ) {
    }

    // todo: remove if it will be not needed
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

        $this->entityManager->persist($basketItem);
        $this->entityManager->flush();
    }

    public function giveItemsToUser(int $borrowerId): void
    {
        $basketOwner = $this->security->getUser();
        $basketItems = $this->basketItemRepository->getBasketItemsByUser($basketOwner);
        $borrower = $this->userRepository->findOneBy(['id' => $borrowerId]);

        // todo: add comment to rental record from UI
        /** @var BasketItem $basketItem */
        foreach ($basketItems as $basketItem) {
            if ($basketItem->getItem()->getDeletedAt()) {
                throw new BadRequestHttpException(
                    "Нельзя выдать удалённый предмет: {$basketItem->getItem()}"
                );
            }

            $availableQuantity = $this->rentalRecordRepository->getItemAvailableQuantity($basketItem->getItem()->getId());

            if ($availableQuantity < $basketItem->getQuantity()) {
                throw new BadRequestHttpException(
                    "Недостаточное количество предмета '{$basketItem->getItem()}', доступно {$availableQuantity} из {$basketItem->getQuantity()}"
                );
            }

            $this->transferBasketItemToRentalRecord($basketItem, $borrower, $basketOwner);
        }

        $this->entityManager->flush();
    }

    private function transferBasketItemToRentalRecord(BasketItem $basketItem, User $borrower, User $basketOwner): void
    {
        $rentalRecord = new RentalRecord();
        $rentalRecord
            ->setItem($basketItem->getItem())
            ->setQuantity($basketItem->getQuantity())
            ->setBorrowedAt(new DateTimeImmutable())
            ->setLender($basketOwner)
            ->setBorrower($borrower);

        $this->entityManager->persist($rentalRecord);
        $this->entityManager->remove($basketItem);
    }
}
