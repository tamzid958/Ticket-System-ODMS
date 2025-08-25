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
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('ticket_weight')->default(1)->nullable(false);
            $table->integer('holiday_multiplier')->default(1)->nullable(false);
            $table->integer('timeslot_multiplier')->default(1)->nullable(false);
            $table->boolean('is_extra')->default(false)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'ticket_weight',
                'holiday_multiplier',
                'timeslot_multiplier',
                'is_extra'
            ]);
        });
    }
};
