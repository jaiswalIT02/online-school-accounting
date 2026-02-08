<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('number', 12, 2)->default(0);
            $table->string('stock_type', 50);
            $table->decimal('stock_amount', 12, 2)->default(0);
            $table->string('stock_unit', 50)->nullable();
            $table->decimal('stock_balance', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
