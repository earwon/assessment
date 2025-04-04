<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Ensure user exists
            $table->foreignId('album_id')->constrained()->onDelete('cascade'); // Ensure album exists
            $table->enum('vote', ['upvote', 'downvote']); // Upvote or Downvote
            $table->timestamps();
            
            $table->unique(['user_id', 'album_id']); // Ensure one vote per user per album
        });
    }

    public function down() {
        Schema::dropIfExists('votes');
    }
};