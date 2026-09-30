<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every publishable content type can have a translated row per locale: `locale` marks which
 * language it's written in, `translation_of_id` links a translation back to its original (so the
 * frontend can offer "Read in English / فارسی" and the API can fall back to the default locale
 * when a translation doesn't exist yet). Existing rows default to `en`, unaffected.
 */
return new class extends Migration
{
    private const TABLES = [
        'categories', 'posts', 'projects', 'services',
        'testimonials', 'process_steps', 'faqs',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->string('locale', 5)->default('en')->after('id')->index();
                $blueprint->foreignId('translation_of_id')->nullable()->after('locale')
                    ->constrained($table)->nullOnDelete();
            });
        }

        // Pages are looked up by `key` (e.g. "home"), which needs to exist once per locale.
        Schema::table('pages', function (Blueprint $blueprint) {
            $blueprint->dropUnique(['key']);
            $blueprint->string('locale', 5)->default('en')->after('id')->index();
            $blueprint->unique(['key', 'locale']);
        });

        // The few Settings fields that read as prose rather than raw data (name/contact details
        // stay as-is across locales); admin fills these in once a Persian site is wanted.
        Schema::table('settings', function (Blueprint $blueprint) {
            $blueprint->string('tagline_fa')->nullable()->after('tagline');
            $blueprint->string('headline_fa')->nullable()->after('headline');
            $blueprint->text('bio_short_fa')->nullable()->after('bio_short');
            $blueprint->string('footer_text_fa')->nullable()->after('footer_text');
            $blueprint->json('stats_fa')->nullable()->after('stats');
        });
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('translation_of_id');
                $blueprint->dropColumn('locale');
            });
        }

        Schema::table('pages', function (Blueprint $blueprint) {
            $blueprint->dropUnique(['key', 'locale']);
            $blueprint->dropColumn('locale');
            $blueprint->unique('key');
        });

        Schema::table('settings', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['tagline_fa', 'headline_fa', 'bio_short_fa', 'footer_text_fa', 'stats_fa']);
        });
    }
};
