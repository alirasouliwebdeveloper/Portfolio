<?php

use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Database\Migrations\Migration;

/** Live sites were seeded before /projects existed; give them the page so it can be edited in the admin. */
return new class extends Migration
{
    public function up(): void
    {
        Page::query()->firstOrCreate(['key' => 'projects', 'locale' => 'en'], PageSeeder::projectsPage());
    }

    public function down(): void
    {
        Page::query()->where('key', 'projects')->delete();
    }
};
