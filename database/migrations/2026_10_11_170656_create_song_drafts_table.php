<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('song_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('musical_key', 30)->nullable();
            $table->unsignedSmallInteger('bpm')->nullable();
            $table->unsignedTinyInteger('capo')->nullable();
            $table->string('meter', 10)->nullable();
            $table->text('source_url')->nullable();
            $table->text('original_source');
            $table->text('reviewed_source');
            $table->json('content_sections');
            $table->json('section_occurrences');
            $table->string('import_fingerprint', 64)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_drafts');
    }
};
