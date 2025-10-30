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
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organizerId');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('location');
            $table->timestamp('startAt');
            $table->timestamp('endAt');
            $table->boolean('isPaid')->default(false);
            $table->integer('capacity');
            $table->integer('seatsAvailable');
            $table->timestamps();

            $table->foreign('organizerId')
                ->references('id')
                ->on('organizer_profiles')
                ->cascadeOnDelete();

            $table->index('startAt');
            $table->index('category');
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
