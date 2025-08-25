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
        Schema::table('client_monthly_summaries', function (Blueprint $table) {
            $table->dropColumn(['unit_remaining', 'weekend_unit_used']);
            $table->decimal('excess_unit_price', 10)->nullable()->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_monthly_summaries', function (Blueprint $table) {
            // Remove the column that was added
            $table->dropColumn('excess_unit_price');

            // Add back the columns that were dropped
            $table->integer('unit_remaining')->default(0)->nullable(false);
            $table->integer('weekend_unit_used')->default(0)->nullable(false);
        });
    }
};
