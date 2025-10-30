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
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('eventId');
            $table->unsignedBigInteger('transactionId');
            $table->unsignedBigInteger('ticketTypeId')->nullable();
            $table->unsignedBigInteger('ownerUserId')->nullable();
            $table->timestamp('checkedInAt')->nullable();
            $table->timestamps();

            $table->foreign('eventId')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();

            $table->foreign('transactionId')
                ->references('id')
                ->on('transactions')
                ->cascadeOnDelete();

            $table->foreign('ticketTypeId')
                ->references('id')
                ->on('ticket_types')
                ->nullOnDelete();

            $table->foreign('ownerUserId')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
