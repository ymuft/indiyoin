<?php

declare(strict_types=1);

namespace App\Security;

final class PasswordPolicy
{
    public static function accepts(string $password): bool
    {
        $length = strlen($password);
        return $length >= 12 && $length <= 72;
    }
}
