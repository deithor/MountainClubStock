<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\BasketItemRepository;
use App\Repository\ItemRepository;
use App\Repository\RentalRecordRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RentalHistoryService
{
    public function __construct(
        private BasketItemRepository $basketItemRepository,
        private RentalRecordRepository $rentalRecordRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private ItemRepository $itemRepository,
        private ValidatorInterface $validator,
    ) {
    }

    public function takeItemsFromUser(array $rentalRecordsIds): void
    {
        $rentalRecords = $this->rentalRecordRepository->findBy(['id' => $rentalRecordsIds]);

        foreach ($rentalRecords as $rentalRecord) {
            $rentalRecord->setReturnedAt(new DateTimeImmutable());
            $this->entityManager->persist($rentalRecord);
        }

        $this->entityManager->flush();
    }
}
