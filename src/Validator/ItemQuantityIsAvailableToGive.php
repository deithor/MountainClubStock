<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ItemQuantityIsAvailableToGive extends Constraint
{
    public string $message = 'Недостаточное количество предмета "{{ item }}", доступно {{ available }} из {{ requested }}';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
