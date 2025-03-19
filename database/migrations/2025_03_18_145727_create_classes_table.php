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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('level');
            $table->integer('level_value');
            $table->string('campus');
            $table->integer('campus_value');
            $table->integer('learners');
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('delegate_id')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('delegate_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
