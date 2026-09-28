<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SEO_TABLES = ['posts', 'projects', 'services', 'categories'];

    public function up(): void
    {
        foreach (self::SEO_TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('focus_keyword', 80)->nullable();
                $table->string('canonical_url', 2048)->nullable();
                $table->boolean('noindex')->default(false);
                $table->unsignedTinyInteger('seo_score')->nullable();
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->string('brand_name')->default('Ali');
            $table->string('tagline')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('ga_measurement_id', 32)->nullable();
            $table->string('gsc_verification')->nullable();
            $table->string('twitter_handle', 64)->nullable();
            $table->boolean('site_noindex')->default(false);
            $table->string('focus_keyword', 80)->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
        });

        // Rich-text fields now hold TipTap JSON documents, which outgrow TEXT.
        Schema::table('projects', function (Blueprint $table) {
            $table->longText('lead')->nullable()->change();
            $table->longText('challenge')->nullable()->change();
            $table->longText('solution')->nullable()->change();
            $table->longText('result')->nullable()->change();
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->longText('answer')->change();
        });
    }

    public function down(): void
    {
        foreach (self::SEO_TABLES as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['focus_keyword', 'canonical_url', 'noindex', 'seo_score']);
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['brand_name', 'tagline', 'footer_text', 'ga_measurement_id', 'gsc_verification', 'twitter_handle', 'site_noindex', 'focus_keyword', 'seo_score']);
        });
    }
};
