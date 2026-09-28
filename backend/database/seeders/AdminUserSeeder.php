<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('portfolio.admin');

        if (blank($admin['password'])) {
            $this->command?->warn('ADMIN_PASSWORD is not set; skipping admin user.');

            return;
        }

        User::updateOrCreate(
            ['email' => $admin['email']],
            ['name' => $admin['name'], 'password' => $admin['password']],
        );
    }
}
