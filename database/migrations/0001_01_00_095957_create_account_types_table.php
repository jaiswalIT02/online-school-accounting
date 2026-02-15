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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });


        $tables = [
            'ledgers',
            'ledger_entries',
            'receipt_payment_accounts',
            'receipt_payment_entries',
            'cashbooks',
            'cashbook_entries',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('account_type_id')
                    ->constrained('account_types')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ledgers',
            'ledger_entries',
            'receipt_payment_accounts',
            'receipt_payment_entries',
            'cashbooks',
            'cashbook_entries',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['account_type_id']);
                $table->dropColumn('account_type_id');
            });
        }

        Schema::dropIfExists('account_types');
    }
};
