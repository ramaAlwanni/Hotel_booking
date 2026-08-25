<?php

namespace Database\Seeders;

use App\Services\Api\PermissionSyncService;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionSyncService::class)->syncPermissions();
    }
}
