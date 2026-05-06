<?php

namespace App\DTO;

/**
 * Data Transfer Object for application statistics.
 *
 * Ensures consistent structure for dashboard data.
 */
class StatsDTO
{
    /**
     * Total users count.
     */
    public int $users;

    /**
     * Active users count.
     */
    public int $active;

    /**
     * Create new StatsDTO instance.
     *
     * @param int $users
     * @param int $active
     */
    public function __construct(int $users, int $active)
    {
        $this->users = $users;
        $this->active = $active;
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'active' => $this->active,
        ];
    }
}