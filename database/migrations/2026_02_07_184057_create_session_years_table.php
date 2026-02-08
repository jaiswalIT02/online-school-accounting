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
        // 1. Create session_years table
        Schema::create('session_years', function (Blueprint $table) {
            $table->id();
            $table->string('session_name');
            $table->string('slug');
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 2. Tables that will use session_year_id
        $tables = [
            'ledgers',
            'receipt_payment_accounts',
            'beneficiaries',
            'receipt_payment_entries',
            'cashbooks',
            'ledger_entries',
            'cashbook_entries',
            'funds',
            'students',
            'staff',
            'items',
            'stocks',
            'stock_ledgers',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('session_year_id')
                      ->nullable()
                      ->constrained('session_years')
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
            'receipt_payment_accounts',
            'beneficiaries',
            'receipt_payment_entries',
            'cashbooks',
            'ledger_entries',
            'cashbook_entries',
            'funds',
            'students',
            'staff',
            'items',
            'stocks',
            'stock_ledgers',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['session_year_id']);
                $table->dropColumn('session_year_id');
            });
        }

        Schema::dropIfExists('session_years');
    }
};
