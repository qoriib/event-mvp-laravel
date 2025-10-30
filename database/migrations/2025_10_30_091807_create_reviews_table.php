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
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eventId');
            $table->unsignedBigInteger('userId');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('eventId')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();

            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['eventId', 'userId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
