<?php

declare(strict_types=1);

namespace App\Enum;

readonly class UserRole
{
    public const ADMIN = 'ROLE_ADMIN';

    public const STOREKEEPER = 'ROLE_STOREKEEPER';

    public const PAYMASTER = 'ROLE_PAYMASTER';

    public const USER = 'ROLE_USER';

    public static function values(): array
    {
        return [
            self::ADMIN,
            self::STOREKEEPER,
            self::PAYMASTER,
        ];
    }
}
