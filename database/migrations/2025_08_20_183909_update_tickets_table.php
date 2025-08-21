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
            $table->boolean('is_weekend_unit')->default(false)->after('description');
            $table->boolean('is_excess_unit')->default(false)->after('is_weekend_unit');
            $table->integer('unit_value')->default(false)->after('is_excess_unit');
           });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('is_weekend_unit');
            $table->dropColumn('is_excess_unit');
            $table->dropColumn('unit_value');
        });
    }
};
