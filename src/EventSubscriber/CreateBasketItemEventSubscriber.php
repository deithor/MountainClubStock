<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\BasketItem;
use App\Repository\BasketItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class CreateBasketItemEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BasketItemRepository $basketItemRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['removeOldBasketItem'],
        ];
    }

    public function removeOldBasketItem(BeforeEntityPersistedEvent $event): void
    {
        $basketItem = $event->getEntityInstance();

        if (!($basketItem instanceof BasketItem)) {
            return;
        }

        $oldBasketItem = $this->basketItemRepository->findOneBy(['user' => $basketItem->getUser(), 'item' => $basketItem->getItem()]);

        if ($oldBasketItem) {
            $basketItem->setQuantity($oldBasketItem->getQuantity() + $basketItem->getQuantity());
            $this->entityManager->remove($oldBasketItem);
        }
    }
}
