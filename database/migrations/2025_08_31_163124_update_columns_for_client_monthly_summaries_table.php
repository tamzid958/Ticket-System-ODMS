<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('client_monthly_summaries', function (Blueprint $table) {
            // Add package_id column if not already added
            if (!Schema::hasColumn('client_monthly_summaries', 'package_id')) {
                $table->unsignedBigInteger('package_id')->after('client_id')->nullable();
            }

            // Add foreign key constraint
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('set null');

            // Add new columns
            $table->decimal('excess_per_unit_price', 10, 2)->default(0)->after('excess_unit_used');
            $table->decimal('package_price', 10, 2)->default(0)->after('excess_per_unit_price');
            $table->decimal('package_discount', 10, 2)->default(0)->after('package_price');
            $table->integer('unit_discount')->default(0)->after('excess_unit_used');
            $table->decimal('excess_unit_cost', 10, 2)->default(0)->after('total_cost');
        });
    }

    public function down(): void
    {
        Schema::table('client_monthly_summaries', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['package_id']);

            // Drop column if you want to rollback
            $table->dropColumn('package_id');

            $table->dropColumn([
                'excess_per_unit_price',
                'package_price',
                'package_discount',
                'unit_discount',
                'excess_unit_cost',
            ]);
        });
    }
};
