<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            MockDataSeeder::class,
            PageSeeder::class,
        ]);

        Artisan::call('seo:rescore');
    }
}
