@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Staff Details</h4>
        <div>
            <a class="btn btn-outline-secondary" href="{{ route('staff.edit', $staff) }}">Edit</a>
            <a class="btn btn-outline-secondary" href="{{ route('staff.index') }}">Back</a>
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
                    <strong>Serial No:</strong> {{ $staff->serial_no ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Employee ID:</strong> {{ $staff->employee_id ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>National ID:</strong> {{ $staff->national_id ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Full Name:</strong> {{ $staff->full_name }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 mb-3">
                    <strong>Gender:</strong> {{ $staff->gender ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Date of Birth:</strong> {{ $staff->date_of_birth ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Age:</strong> {{ $staff->age ?? '-' }}
                </div>
                <div class="col-md-2 mb-3">
                    <strong>Caste:</strong> {{ $staff->caste ?? '-' }}
                </div>
                <div class="col-md-2 mb-3">
                    <strong>Marital Status:</strong> {{ $staff->marital_status ?? '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Contact No:</strong> {{ $staff->contact_no ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Email:</strong> {{ $staff->email ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Professional Information</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Designation:</strong> {{ $staff->designation ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Date of Joining:</strong> {{ $staff->date_of_joining ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Qualification:</strong> {{ $staff->qualification ?? '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Professional Qualification:</strong> {{ $staff->professional_qualification ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Stream:</strong> {{ $staff->stream ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Honors Major In:</strong> {{ $staff->honors_major_in ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Personal Information</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Father Name:</strong> {{ $staff->father_name ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Mother Name:</strong> {{ $staff->mother_name ?? '-' }}
                </div>
                <div class="col-md-2 mb-3">
                    <strong>Blood Group:</strong> {{ $staff->blood_group ?? '-' }}
                </div>
                <div class="col-md-2 mb-3">
                    <strong>Height:</strong> {{ $staff->height ? $staff->height . ' Feet' : '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <strong>Weight:</strong> {{ $staff->weight ? $staff->weight . ' Kg' : '-' }}
                </div>
                <div class="col-md-9 mb-3">
                    <strong>Address:</strong> {{ $staff->address ?? ($staff->village ? implode(', ', array_filter([$staff->village, $staff->post_office, $staff->sub_div, $staff->panchayat, $staff->dist, $staff->state, $staff->pincode])) : '-') }}
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
                    <strong>Bank Name:</strong> {{ $staff->bank_name ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Account No:</strong> {{ $staff->account_no ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>IFSC Code:</strong> {{ $staff->ifsc_code ?? '-' }}
                </div>
                <div class="col-md-3 mb-3">
                    <strong>Component:</strong> {{ $staff->component ?? '-' }}
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Vendor Code:</strong> {{ $staff->vendor_code ?? '-' }}
                </div>
            </div>

            <div class="row mb-4 mt-4">
                <div class="col-md-12">
                    <h5>Additional Documents</h5>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <strong>Aadhaar No:</strong> {{ $staff->aadhaar_no ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>Voter ID:</strong> {{ $staff->voter_id ?? '-' }}
                </div>
                <div class="col-md-4 mb-3">
                    <strong>PAN No:</strong> {{ $staff->pan_no ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
