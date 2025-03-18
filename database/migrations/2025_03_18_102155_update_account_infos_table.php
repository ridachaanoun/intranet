<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('account_infos', function (Blueprint $table) {
            $table->dropColumn('promotion'); // Remove old column
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('account_infos', function (Blueprint $table) {
            $table->string('promotion')->nullable(); // Restore old column
            $table->dropForeign(['promotion_id']);
            $table->dropColumn('promotion_id');
        });
    }
};

