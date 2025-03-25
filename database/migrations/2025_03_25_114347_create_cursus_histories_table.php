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
        Schema::create('cursus_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->date('date');
            $table->enum('event', ['Registration', 'Promotion', 'Class Change', 'SAS', '1ère Année', '2ème Année']);
            $table->enum('status', ['PASS', 'FAIL', 'IN PROGRESS']);
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('student_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('coach_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classrooms')->onDelete('set null');
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursus_histories');
    }
};
