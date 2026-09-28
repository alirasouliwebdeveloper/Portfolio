<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nav_label');
            $table->string('title');
            $table->string('h1');
            $table->text('lead');
            $table->string('icon', 32);
            $table->string('hero_image_alt')->nullable();
            $table->json('floating_metric')->nullable();
            $table->json('pains')->nullable();
            $table->json('offers')->nullable();
            $table->json('why')->nullable();
            $table->json('stack')->nullable();
            $table->json('tiers')->nullable();
            $table->json('faq')->nullable();
            $table->unsignedBigInteger('related_project_id')->nullable();
            $table->foreignId('related_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 16)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
