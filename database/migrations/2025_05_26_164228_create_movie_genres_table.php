<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('movie_genre', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('movie_id')->nullable()
                ->constrained('movies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreignId('genre_id')->nullable()
                ->constrained('genres')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('movie_genres');
    }
};
