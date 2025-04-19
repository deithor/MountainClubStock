<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\BasketItem;
use App\Entity\RentalRecord;
use App\Repository\RentalRecordRepository;
use Attribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use UnexpectedValueException;

#[Attribute]
class ItemQuantityIsAvailableToGiveValidator extends ConstraintValidator
{
    public function __construct(private readonly RentalRecordRepository $rentalRecordRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ItemQuantityIsAvailableToGive) {
            throw new UnexpectedTypeException($constraint, ItemQuantityIsAvailableToGive::class);
        }

        if (!($value instanceof RentalRecord || $value instanceof BasketItem)) {
            throw new UnexpectedValueException($value);
        }

        $availableQuantity = $this->rentalRecordRepository->getItemAvailableQuantity($value->getItem()->getId());

        if ($availableQuantity < $value->getQuantity()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ item }}', $value->getItem()->getName())
                ->setParameter('{{ available }}', $availableQuantity)
                ->addViolation();
        }
    }
}
