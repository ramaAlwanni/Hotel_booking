<?php

namespace Database\Seeders;

use App\Services\Setting\PermissionSyncService;
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
