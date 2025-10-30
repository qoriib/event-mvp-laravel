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
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('eventId');
            $table->enum('status', [
                'WAITING_PAYMENT',
                'WAITING_CONFIRMATION',
                'DONE',
                'REJECTED',
                'EXPIRED',
                'CANCELED',
            ])->default('WAITING_PAYMENT');
            $table->integer('totalBeforeIDR');
            $table->integer('pointsUsedIDR')->default(0);
            $table->string('promoCode')->nullable();
            $table->integer('promoDiscountIDR')->default(0);
            $table->integer('totalPayableIDR');
            $table->string('paymentProofUrl')->nullable();
            $table->timestamp('paymentProofAt')->nullable();
            $table->timestamp('expiresAt');
            $table->timestamp('decisionDueAt')->nullable();
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('eventId')
                ->references('id')
                ->on('events')
                ->cascadeOnDelete();

            $table->index('status');
            $table->index('expiresAt');
            $table->index('decisionDueAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
