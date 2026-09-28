<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('body')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedSmallInteger('reading_time')->default(1);
            $table->string('cover_alt')->nullable();
            $table->foreignId('related_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('status', 16)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
