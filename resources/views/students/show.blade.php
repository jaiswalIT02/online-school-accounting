@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Student Details</h4>
        <div>
            <a class="btn btn-outline-secondary" href="{{ route('students.edit', $student) }}">Edit</a>
            <a class="btn btn-outline-secondary" href="{{ route('students.index') }}">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h5>Basic Information</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>SL No:</strong> {{ $student->sl_no ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Admission Date:</strong> {{ $student->admission_date ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Admission No:</strong> {{ $student->admission_no ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Class:</strong> {{ $student->class ?? '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Student ID:</strong> {{ $student->student_id ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Student Name:</strong> {{ $student->student_name }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>PEN Number:</strong> {{ $student->pen_number ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Personal Information</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>Aapaar ID:</strong> {{ $student->aapaar_id ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Father Name:</strong> {{ $student->father_name ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Mother Name:</strong> {{ $student->mother_name ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Caste:</strong> {{ $student->caste ?? '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>DOB:</strong> {{ $student->dob ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Age:</strong> {{ $student->age ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Mobile No:</strong> {{ $student->mobile_no ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Blood Group:</strong> {{ $student->blood_group ?? '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>Height:</strong> {{ $student->height ? $student->height . ' Feet' : '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Weight:</strong> {{ $student->weight ? $student->weight . ' Kg' : '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Vendor Code:</strong> {{ $student->vendor_code ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Address Information</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <strong>Address:</strong> 
                    {{ $student->address ?? ($student->village ? implode(', ', array_filter([$student->village, $student->post_office, $student->subdiv, $student->panchayat, $student->dist, $student->state, $student->pincode])) : '-') }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Dropout Information</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Dropout School:</strong> {{ $student->dropout_school ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Dropout Date:</strong> {{ $student->dropout_date ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Dropout Reason:</strong> {{ $student->dropout_reason ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Bank Account Details</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>Bank Name:</strong> {{ $student->bank_name ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Branch Name:</strong> {{ $student->branch_name ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>IFSC:</strong> {{ $student->ifsc ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Account No:</strong> {{ $student->account_no ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Additional Documents</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>Aadhaar No:</strong> {{ $student->aadhar_number ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Father Aadhaar No:</strong> {{ $student->father_aadhar_number ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Father Voter ID No:</strong> {{ $student->father_voter_id_no ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Father PAN No:</strong> {{ $student->father_pan_no ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
