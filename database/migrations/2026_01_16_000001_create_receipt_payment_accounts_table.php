<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Receipt & Payment Account');
            $table->string('header_title', 200)->nullable();
            $table->string('header_subtitle', 200)->nullable();
            $table->date('period_from');
            $table->date('period_to');
            $table->date('date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_payment_accounts');
    }
};
