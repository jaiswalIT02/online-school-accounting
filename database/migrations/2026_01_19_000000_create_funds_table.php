<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->string('fund_date', 10)->comment('Date in dd/mm/yyyy format');
            $table->string('component_name', 200)->nullable();
            $table->string('component_code', 100)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->index('fund_date');
            $table->index('component_name');
            $table->index('component_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
