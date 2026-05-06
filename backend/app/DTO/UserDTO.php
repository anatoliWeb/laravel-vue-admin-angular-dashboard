<?php

namespace App\DTO;

/**
 * Data Transfer Object for User.
 *
 * Defines the structure of user data
 * passed from service layer to UI/API.
 */
class UserDTO
{
    /**
     * User identifier.
     */
    public int $id;

    /**
     * User name.
     */
    public string $name;

    /**
     * User email.
     */
    public string $email;

    /**
     * User roles.
     *
     * @var array<int, string>
     */
    public array $roles;

    /**
     * Direct user permissions.
     *
     * @var array<int, string>
     */
    public array $permissions;

    /**
     * Explicitly denied permissions.
     *
     * @var array<int, string>
     */
    public array $denied_permissions;

    /**
     * Create new UserDTO instance.
     *
     * @param int $id
     * @param string $name
     * @param string $email
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     * @param array<int, string> $deniedPermissions
     */
    public function __construct(
        int $id,
        string $name,
        string $email,
        array $roles = [],
        array $permissions = [],
        array $deniedPermissions = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->roles = $roles;
        $this->permissions = $permissions;
        $this->denied_permissions = $deniedPermissions;
    }

    /**
     * Convert DTO to array.
     *
     * Used for JSON/API responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'denied_permissions' => $this->denied_permissions,
        ];
    }
}
