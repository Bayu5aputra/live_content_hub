<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('loop')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('playlist_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['playlist_id', 'content_id']);
            $table->index(['playlist_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_content');
        Schema::dropIfExists('playlists');
    }
};
