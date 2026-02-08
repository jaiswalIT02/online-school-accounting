<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Cash Book');
            $table->string('period_month', 20);
            $table->integer('period_year');
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('opening_bank', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbooks');
    }
};
