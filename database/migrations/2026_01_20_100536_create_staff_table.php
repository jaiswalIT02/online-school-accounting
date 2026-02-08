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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->integer('serial_no')->nullable();
            $table->string('employee_id')->unique()->nullable();
            $table->string('national_id')->nullable();
            $table->string('full_name');
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('date_of_birth')->nullable()->comment('Format: dd-mm-yyyy');
            $table->string('age')->nullable()->comment('Format: Years, Months, Days');
            $table->enum('caste', ['General', 'SC', 'ST', 'OBC', 'MOBC'])->nullable();
            $table->enum('marital_status', ['Married', 'Unmarried'])->nullable();
            $table->string('contact_no', 10)->nullable();
            $table->string('email')->nullable();
            $table->enum('designation', [
                'Warden Cum Superintendent',
                'Head Teacher',
                'Full Time Assistant Teacher',
                'Part Time Assistant Teacher',
                'Account Assistant Cum Caretaker',
                'Peon Cum Matron',
                'Chowkidar Cum Mali',
                'Head Cook',
                'Assistant Cook Cum Helper'
            ])->nullable();
            $table->string('date_of_joining')->nullable()->comment('Format: dd-mm-yyyy');
            $table->string('bank_name')->nullable();
            $table->string('account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('component_code')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('aadhaar_no', 12)->nullable();
            $table->string('voter_id')->nullable();
            $table->string('pan_no')->nullable();
            $table->enum('qualification', [
                'Below HSLC',
                'HSLC',
                'HS',
                'BA',
                'Bsc',
                'B.Com',
                'MA',
                'Msc',
                'M.Com',
                'Manual Enter'
            ])->nullable();
            $table->enum('professional_qualification', [
                'D.El.Ed',
                'B.Ed',
                'TET',
                'CTET'
            ])->nullable();
            $table->enum('stream', ['Arts', 'Science', 'Commerce'])->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('blood_group')->nullable();
            $table->decimal('height', 5, 2)->nullable()->comment('Height in feet');
            $table->decimal('weight', 5, 2)->nullable()->comment('Weight in kg');
            $table->string('village')->nullable();
            $table->string('post_office')->nullable();
            $table->string('sub_div')->nullable();
            $table->string('panchayat')->nullable();
            $table->string('dist')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->text('address')->nullable()->comment('Combined address format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode');
            $table->string('honors_major_in')->nullable();
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
        Schema::dropIfExists('staff');
    }
};
