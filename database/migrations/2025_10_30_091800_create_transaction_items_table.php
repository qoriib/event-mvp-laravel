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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('transactionId');
            $table->unsignedBigInteger('ticketTypeId')->nullable();
            $table->integer('qty');
            $table->integer('unitPriceIDR');
            $table->integer('lineTotalIDR');
            $table->timestamps();

            $table->foreign('transactionId')
                ->references('id')
                ->on('transactions')
                ->cascadeOnDelete();

            $table->foreign('ticketTypeId')
                ->references('id')
                ->on('ticket_types')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
