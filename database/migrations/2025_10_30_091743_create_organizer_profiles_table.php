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
        Schema::create('organizer_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('userId')->unique();
            $table->string('displayName');
            $table->text('bio')->nullable();
            $table->double('ratingsAvg')->default(0);
            $table->integer('ratingsCount')->default(0);
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_profiles');
    }
};
