<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_payment_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_payment_account_id')
                ->constrained('receipt_payment_accounts')
                ->cascadeOnDelete();
            $table->enum('type', ['receipt', 'payment']);
            $table->string('particular_name')->nullable();
            $table->string('acode', 50)->nullable();
            $table->foreignId('article_id')->nullable()->constrained('articles')->onDelete('set null');
            $table->foreignId('beneficiary_id')->nullable()->constrained('beneficiaries')->onDelete('set null');
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->string('date')->nullable()->comment('PPA Date in dd/mm/yyyy format');
            $table->decimal('tax_amount', 12, 2)->nullable();
            $table->enum('tax_for', ['tds', 'pTax'])->nullable();
            $table->enum('tax_type', ['dr', 'cr'])->nullable();
            $table->text('tax_remark')->nullable();
            $table->unsignedBigInteger('pair_id')->nullable();
            $table->timestamps();

            $table->index(['receipt_payment_account_id', 'type']);
            $table->index('pair_id');
            // pair_id stores transaction ID, not a foreign key reference
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_payment_entries');
    }
};
