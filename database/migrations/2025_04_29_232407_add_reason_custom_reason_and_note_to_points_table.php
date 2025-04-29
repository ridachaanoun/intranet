<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReasonCustomReasonAndNoteToPointsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Drop the existing reason column
            $table->dropColumn('reason');
        });

        Schema::table('points', function (Blueprint $table) {
            // Recreate the reason column as an enum
            $table->enum('reason', [
                'Participation',
                'Good Performance',
                'Helping Others',
                'Task Completion',
                'Extra Effort',
                'Other'
            ])->nullable()->after('points'); 

            // Add new columns
            $table->string('custom_reason')->nullable()->after('reason');
            $table->text('note')->nullable()->after('custom_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn(['reason', 'custom_reason', 'note']);

            // Recreate the old reason column as text
            $table->text('reason')->nullable()->after('points');
        });
    }
}