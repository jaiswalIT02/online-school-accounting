<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashbook_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('voucher_no', 50)->nullable();
            $table->string('particulars');
            $table->string('folio_no', 50)->nullable();
            $table->enum('type', ['receipt', 'payment']);
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->decimal('bank_amount', 12, 2)->default(0);
            $table->text('narration')->nullable();
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->enum('tax_for', ['tds', 'pTax'])->nullable();
            $table->text('tax_remark')->nullable();
            $table->foreignId('receipt_payment_entry_id')->nullable()->constrained('receipt_payment_entries')->onDelete('cascade');
            $table->timestamps();

            $table->index(['cashbook_id', 'entry_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbook_entries');
    }
};
