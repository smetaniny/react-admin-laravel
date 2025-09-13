<?php

declare(strict_types=1);

namespace Smetaniny\ReactAdminLaravel\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Smetaniny\ReactAdminCore\Contracts\UserInterface;
use Smetaniny\ReactAdminCore\Traits\HasPermissions;
use Smetaniny\ReactAdminCore\Traits\HasRoles;

/**
 * Laravel реализация пользователя для React Admin
 */
class LaravelUser extends Authenticatable implements UserInterface
{
    use HasRoles, HasPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'roles' => 'array',
        'permissions' => 'array',
    ];

    /**
     * Получить ID пользователя
     */
    public function getId(): int|string
    {
        return $this->getKey();
    }

    /**
     * Получить email пользователя
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Получить роли пользователя
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    /**
     * Получить разрешения пользователя
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Проверить, есть ли у пользователя роль
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * Проверить, есть ли у пользователя разрешение
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->getPermissions();
        
        // Проверяем на суперадмина
        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /**
     * Проверить, есть ли у пользователя любая из указанных ролей
     */
    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($this->getRoles(), $roles));
    }

    /**
     * Проверить, есть ли у пользователя все указанные роли
     */
    public function hasAllRoles(array $roles): bool
    {
        return empty(array_diff($roles, $this->getRoles()));
    }

    /**
     * Проверить, есть ли у пользователя любое из указанных разрешений
     */
    public function hasAnyPermission(array $permissions): bool
    {
        $userPermissions = $this->getPermissions();
        
        // Проверяем на суперадмина
        if (in_array('*', $userPermissions, true)) {
            return true;
        }

        return !empty(array_intersect($userPermissions, $permissions));
    }

    /**
     * Проверить, есть ли у пользователя все указанные разрешения
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissions = $this->getPermissions();
        
        // Проверяем на суперадмина
        if (in_array('*', $userPermissions, true)) {
            return true;
        }

        return empty(array_diff($permissions, $userPermissions));
    }

    /**
     * Установить роли пользователя
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Установить разрешения пользователя
     */
    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }
}
