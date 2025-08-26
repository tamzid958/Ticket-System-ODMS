<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Remove is_extra if it exists
            if (Schema::hasColumn('tickets', 'is_extra')) {
                $table->dropColumn('is_extra');
            }

            // Change unit_discount to integer
            if (Schema::hasColumn('tickets', 'unit_discount')) {
                // Use DB statement to alter the column type safely
                DB::statement('ALTER TABLE tickets ALTER COLUMN unit_discount TYPE integer USING floor(unit_discount);');
            } else {
                // If column does not exist, create it as integer
                $table->integer('unit_discount')->default(0)->after('timeslot_multiplier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Rollback: restore is_extra
            if (!Schema::hasColumn('tickets', 'is_extra')) {
                $table->boolean('is_extra')->default(false)->after('timeslot_multiplier');
            }

            // Rollback: change unit_discount back to decimal
            if (Schema::hasColumn('tickets', 'unit_discount')) {
                DB::statement('ALTER TABLE tickets ALTER COLUMN unit_discount TYPE decimal(10,2) USING unit_discount;');
            }
        });
    }
};
