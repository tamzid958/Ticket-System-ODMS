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
        Schema::table('packages', function (Blueprint $table) {
            // Add the new column
            $table->decimal('excess_unit_price', 8, 2)->default(0)->after('base_price');

            // If you still have excess_unit_multiplier and want to remove it:
            // $table->dropColumn('excess_unit_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Remove the new column if rolling back
            $table->dropColumn('excess_unit_price');

            // Optionally restore the old column
            // $table->integer('excess_unit_multiplier')->default(1)->after('base_price');
        });
    }
};
