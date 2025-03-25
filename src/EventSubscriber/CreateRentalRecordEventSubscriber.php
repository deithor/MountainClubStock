<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\RentalRecord;
use App\Repository\RentalRecordRepository;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class CreateRentalRecordEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RentalRecordRepository $rentalRecordRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['checkAvailableQuantity'],
        ];
    }

    public function checkAvailableQuantity(BeforeEntityPersistedEvent $event): void
    {
        $rentalRecord = $event->getEntityInstance();

        if (!($rentalRecord instanceof RentalRecord)) {
            return;
        }

        $availableQuantity = $this->rentalRecordRepository->getItemAvailableQuantity($rentalRecord->getItem()->getId());

        if ($availableQuantity < $rentalRecord->getQuantity()) {
            throw new BadRequestHttpException(
                "Недостаточное количество предмета '{$rentalRecord->getItem()}', доступно {$availableQuantity} из {$rentalRecord->getQuantity()}"
            );
        }
    }
}
