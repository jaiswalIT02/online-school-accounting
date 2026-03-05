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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique('name');
            $table->string('slug')->unique('slug');
            $table->date('registerred_date');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 2. Tables that will use school_id
        $tables = [
            'account_types',
            'articles',
            'beneficiaries',
            'cashbooks',
            'cashbook_entries',
            'funds',
            'items',
            'ledgers',
            'ledger_entries',
            'receipt_payment_accounts',
            'receipt_payment_entries',
            'session_years',
            'students',
            'staff',
            'stocks',
            'stock_ledgers',
            'users',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('school_id')
                    ->nullable()
                    ->constrained('schools')
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
            'account_types',
            'articles',
            'beneficiaries',
            'cashbooks',
            'cashbook_entries',
            'funds',
            'items',
            'ledgers',
            'ledger_entries',
            'receipt_payment_accounts',
            'receipt_payment_entries',
            'session_years',
            'students',
            'staff',
            'stocks',
            'stock_ledgers',
            'users',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }

        Schema::dropIfExists('schools');
    }
};
