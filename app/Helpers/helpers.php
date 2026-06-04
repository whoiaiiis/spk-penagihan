<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('has_permission')) {
    /**
     * Check if the currently logged-in user has the specified permission.
     * Admins automatically have all permissions.
     *
     * @param string $permission The permission string (e.g. 'pelanggan:view')
     * @return bool
     */
    function has_permission(string $permission): bool
    {
        $role = Session::get('role');

        // Admin has full access to everything
        if ($role && strtolower($role) === 'admin') {
            return true;
        }

        $permissions = Session::get('permissions') ?? [];

        // Check if the permission is in the user's permissions array
        return in_array($permission, $permissions);
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Alias for has_permission.
     *
     * @param string $permission
     * @return bool
     */
    function hasPermission(string $permission): bool
    {
        return has_permission($permission);
    }
}
