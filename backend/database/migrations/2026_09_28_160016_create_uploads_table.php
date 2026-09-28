<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('upload_session', 64)->nullable()->index();
            $table->string('original_name');
            $table->string('mime', 127);
            $table->unsignedInteger('size');
            $table->string('path');
            $table->foreignId('contact_message_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
