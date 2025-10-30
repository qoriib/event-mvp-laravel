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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eventId');
            $table->string('name');
            $table->integer('priceIDR');
            $table->integer('quota')->nullable();
            $table->timestamps();

            $table->foreign('eventId')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
