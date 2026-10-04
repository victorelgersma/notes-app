<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            // Sanitised HTML from the rich-text editor.
            $table->longText('body')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('note_tag', function (Blueprint $table) {
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['note_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('notes');
    }
};
