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
        Schema::create('client_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->year('year');
            $table->unsignedTinyInteger('month'); // 1-12

            $table->integer('monthly_quota')->default(0);

            $table->integer('unit_used')->default(0);
            $table->integer('unit_remaining')->default(0);


            $table->integer('weekend_unit_used')->default(0);

            $table->integer('excess_unit_used')->default(0);

            $table->decimal('total_cost', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['client_id', 'year', 'month']);

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_monthly_summaries');
    }
};
