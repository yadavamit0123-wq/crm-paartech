<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    public static function can(User $user, string $permission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->role?->hasPermission($permission) ?? false;
    }

    public static function canAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (static::can($user, $permission)) {
                return true;
            }
        }

        return false;
    }
}
