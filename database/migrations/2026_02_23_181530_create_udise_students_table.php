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
        Schema::create('udise_students', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('studentId')->nullable();
            $table->string('studentCodeNat')->nullable();
            $table->string('studentCodeState')->nullable();
            $table->unsignedBigInteger('schoolId')->nullable();

            $table->string('studentName')->nullable();
            $table->tinyInteger('gender')->nullable();
            $table->string('genderDesc')->nullable();

            $table->unsignedBigInteger('socCatId')->nullable();
            $table->string('socialCategoryDesc')->nullable();

            $table->unsignedBigInteger('minorityId')->nullable();
            $table->string('minorityDesc')->nullable();

            $table->string('uuid')->nullable();
            $table->string('uuidMasked')->nullable();
            $table->tinyInteger('isUuidAvailable')->default(0);
            $table->tinyInteger('isValidUuid')->default(0);
            $table->string('nameAsUuid')->nullable();
            $table->tinyInteger('uuidStatus')->nullable();
            $table->string('uuidStatusDesc')->nullable();
            $table->string('uuidValidateRemarks')->nullable();
            $table->date('uuidValidateDate')->nullable();

            $table->date('dob')->nullable();
            $table->string('guardianName')->nullable();
            $table->string('fatherName')->nullable();
            $table->string('motherName')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('pincode')->nullable();

            $table->string('primaryMobile')->nullable();
            $table->string('secondaryMobile')->nullable();
            $table->string('email')->nullable();

            $table->tinyInteger('isBplYN')->default(0);
            $table->tinyInteger('aayBplYN')->default(0);
            $table->tinyInteger('ewsYN')->default(0);
            $table->tinyInteger('cwsnYN')->default(0);
            $table->tinyInteger('natIndYN')->default(0);

            $table->unsignedBigInteger('motherTongue')->nullable();
            $table->string('motherTongueDesc')->nullable();

            $table->unsignedBigInteger('acYearId')->nullable();
            $table->unsignedBigInteger('lastYearId')->nullable();
            $table->string('lastYearIdDesc')->nullable();

            $table->unsignedBigInteger('classId')->nullable();
            $table->string('classDesc')->nullable();
            $table->unsignedBigInteger('classPyId')->nullable();
            $table->string('classPyDesc')->nullable();

            $table->unsignedBigInteger('sectionId')->nullable();
            $table->string('sectionDesc')->nullable();
            $table->string('sectionPyDesc')->nullable();

            $table->string('impairmentType')->nullable();
            $table->tinyInteger('disabilityCerti')->nullable();
            $table->decimal('impairmentPercent', 5, 2)->default(0.00);

            $table->tinyInteger('ooscYN')->default(0);
            $table->tinyInteger('ooscMainstreamedYN')->default(0);

            $table->string('profileStatus')->nullable();
            $table->tinyInteger('formStatus')->nullable();

            $table->tinyInteger('ageCheckSkipped')->default(0);
            $table->string('academicStreamDesc')->nullable();
            $table->string('admnNumber')->nullable();
            $table->tinyInteger('isRepeater')->default(0);

            $table->date('inactiveDate')->nullable();

            $table->unsignedBigInteger('statusId')->nullable();
            $table->string('statusDesc')->nullable();
            $table->unsignedBigInteger('statusL1Id')->nullable();
            $table->string('statusL1Desc')->nullable();
            $table->unsignedBigInteger('statusL2Id')->nullable();
            $table->string('statusL2Desc')->nullable();

            $table->tinyInteger('isNew')->default(0);

            $table->unsignedBigInteger('bloodGroup')->nullable();
            $table->string('bloodGroupDesc')->nullable();

            $table->unsignedBigInteger('deleteReason')->nullable();
            $table->string('deleteReasonDesc')->nullable();

            $table->string('schUdiseCode')->nullable();
            $table->string('schoolName')->nullable();
            $table->unsignedBigInteger('yearId')->nullable();

            $table->tinyInteger('studentMovType')->nullable();

            $table->dateTime('lastModifiedOn')->nullable();
            $table->string('lastModifiedBy')->nullable();

            $table->string('apaarIdStatus')->nullable();
            $table->string('apaarId')->nullable();
            $table->string('apaarIdStatusDesc')->nullable();

            $table->string('schoolPY')->nullable();
            $table->string('mbuStatusDesc')->nullable();
            $table->tinyInteger('examFormStatus')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('udise_students');
    }
};
