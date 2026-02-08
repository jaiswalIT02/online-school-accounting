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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->integer('sl_no')->nullable();
            $table->string('admission_date')->nullable()->comment('Format: dd-mm-yyyy');
            $table->string('admission_no')->nullable();
            $table->enum('class', ['Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve'])->nullable();
            $table->string('student_id')->unique()->nullable();
            $table->string('student_name');
            $table->string('pen_number')->nullable();
            $table->string('aapaar_id', 12)->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->enum('caste', ['SC', 'ST', 'OBC', 'MOBC', 'GEN'])->nullable();
            $table->date('dob')->nullable();
            $table->string('age')->nullable()->comment('Format: Years, Months, Days');
            $table->string('mobile_no', 10)->nullable();
            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('subdiv')->nullable();
            $table->string('panchayat')->nullable();
            $table->string('dist')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->text('address')->nullable()->comment('Combined address format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode');
            $table->string('dropout_school')->nullable();
            $table->string('dropout_date')->nullable()->comment('Format: dd-mm-yyyy');
            $table->text('dropout_reason')->nullable();
            $table->decimal('height', 5, 2)->nullable()->comment('Height in feet');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->string('blood_group')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('ifsc')->nullable();
            $table->string('account_no')->nullable();
            $table->string('aadhar_number', 12)->nullable();
            $table->string('father_aadhar_number', 12)->nullable();
            $table->string('father_voter_id_no')->nullable();
            $table->string('father_pan_no')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=Active, 1=Deleted');
            $table->text('reason')->nullable()->comment('Reason for deletion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
