<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('ledger_name');
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->enum('opening_type', ['Dr', 'Cr'])->default('Dr');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
