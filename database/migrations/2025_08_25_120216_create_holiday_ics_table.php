<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('holiday_ics', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->text('ics_file'); // instead of binary
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_ics');
    }
};
