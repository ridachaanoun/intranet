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
        Schema::table('tasks', function (Blueprint $table) {
            // Remove the assigned_to column
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
            $table->unsignedBigInteger('classroom_id')->nullable()->after('assigned_to');
            $table->string('task_type')->default('Assignment')->after('status');
            $table->integer('points')->default(0)->after('task_type');
            $table->string('assignment_type')->default('class')->after('points'); // 'class' or 'students'

            $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropColumn(['classroom_id', 'task_type', 'points', 'assignment_type']);
        });
    }
};
