<?php

namespace App\Utils\BadgeChecker;

use App\Models\User;

interface BadgeCheckInterface
{
    public static function check(User $user, array $options = []): bool;
}
