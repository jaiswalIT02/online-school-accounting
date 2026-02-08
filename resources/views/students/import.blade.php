@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Import Students</h4>
        <div>
            <a class="btn btn-secondary" href="{{ route('students.index') }}">Back to Students</a>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Upload Excel/CSV File</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('students.processImport') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="file" class="form-label">Select File (.xlsx, .xls, or .csv)</label>
                    <input type="file" class="form-control @error('file') is-invalid @enderror" 
                           id="file" name="file" accept=".xlsx,.xls,.csv" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">File Format Requirements:</h6>
                    <ul class="mb-0">
                        <li><strong>Required columns:</strong>
                            <ul>
                                <li><code>name</code> or <code>student_name</code> (required)</li>
                                <li><code>roll_no</code> or <code>student_id</code> (required, must be unique)</li>
                            </ul>
                        </li>
                        <li><strong>All other columns will be automatically mapped:</strong>
                            <ul>
                                <li>Columns matching database field names will be imported</li>
                                <li>Columns with variations (spaces, underscores, case) will be matched automatically</li>
                                <li>Unmatched columns will be ignored (no error)</li>
                                <li>Examples: <code>phone</code>/<code>mobile_no</code>, <code>dob</code>/<code>date of birth</code>, <code>father name</code>/<code>father_name</code>, etc.</li>
                            </ul>
                        </li>
                        <li><strong>Duplicate Handling:</strong> Rows with existing <code>roll_no</code> (student_id) will be skipped</li>
                        <li><strong>First row should contain column headers</strong></li>
                        <li><strong>Note:</strong> You can include all columns from your Excel file - only matching columns will be imported, others will be safely ignored</li>
                    </ul>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Available Field Names for Excel Import</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Required Fields:</h6>
                                <ul class="list-unstyled">
                                    <li><code>student_name</code> (or: name, student name, full name)</li>
                                    <li><code>student_id</code> (or: roll_no, roll no, roll number, student id, id)</li>
                                </ul>
                                
                                <h6 class="text-primary mt-3">Admission Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>sl_no</code> (or: sl no, serial no, serial number, sno)</li>
                                    <li><code>admission_date</code> (or: admission date, admit date)</li>
                                    <li><code>admission_no</code> (or: admission no, admission number, admit no)</li>
                                    <li><code>class</code> (or: grade, standard)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Personal Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>dob</code> (or: date of birth, birth date, birthday)</li>
                                    <li><code>age</code></li>
                                    <li><code>caste</code> (or: category)</li>
                                    <li><code>mobile_no</code> (or: phone, phone number, mobile, mobile no, contact, contact no) - 10 digits</li>
                                </ul>

                                <h6 class="text-primary mt-3">Family Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>father_name</code> (or: father name, father, fathers name)</li>
                                    <li><code>mother_name</code> (or: mother name, mother, mothers name)</li>
                                    <li><code>father_aadhar_number</code> (or: father aadhar number, father aadhar no)</li>
                                    <li><code>father_voter_id_no</code> (or: father voter id no, father voter id)</li>
                                    <li><code>father_pan_no</code> (or: father pan no, father pan)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Physical & Health:</h6>
                                <ul class="list-unstyled">
                                    <li><code>blood_group</code> (or: blood group, blood)</li>
                                    <li><code>height</code> (numeric)</li>
                                    <li><code>weight</code> (numeric)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">ID Documents:</h6>
                                <ul class="list-unstyled">
                                    <li><code>pen_number</code> (or: pen number, pen no)</li>
                                    <li><code>aapaar_id</code> (or: aapaar id)</li>
                                    <li><code>aadhar_number</code> (or: aadhar number, aadhar no, aadhaar number, aadhaar no)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Address Fields:</h6>
                                <ul class="list-unstyled">
                                    <li><code>address</code> (or: complete address, full address) - Combined format: "Village,PO,Sub Div,Panchayat,Dist,State,Pincode"</li>
                                    <li><strong>OR use individual fields:</strong></li>
                                    <li><code>village</code> (or: address village, address_village - legacy support)</li>
                                    <li><code>post_office</code> (or: po, post office, address po, address_po - legacy support)</li>
                                    <li><code>subdiv</code> (or: sub div, sub division, address subdiv, address_subdiv - legacy support)</li>
                                    <li><code>panchayat</code> (or: address panchayat, address_panchayat - legacy support)</li>
                                    <li><code>dist</code> (or: district, address district, address_dist - legacy support)</li>
                                    <li><code>state</code> (or: address state, address_state - legacy support)</li>
                                    <li><code>pincode</code> (or: pin code, pin, address pincode, address_pincode - legacy support)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Dropout Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>dropout_school</code> (or: dropout school)</li>
                                    <li><code>dropout_date</code> (or: dropout date)</li>
                                    <li><code>dropout_reason</code> (or: dropout reason)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Bank Details:</h6>
                                <ul class="list-unstyled">
                                    <li><code>bank_name</code> (or: bank name, bank)</li>
                                    <li><code>branch_name</code> (or: branch name, branch)</li>
                                    <li><code>ifsc</code> (or: ifsc code, ifsc_code)</li>
                                    <li><code>account_no</code> (or: account no, account number, account)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Other:</h6>
                                <ul class="list-unstyled">
                                    <li><code>vendor_code</code> (or: vendor code, vendor)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><strong>Note:</strong> Field names are case-insensitive. You can use spaces, underscores, or no separators. The system will automatically match variations. For example: "Student Name", "student_name", "studentname" all work.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload and Import
                    </button>
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @if (session('import_result'))
        @php
            $result = session('import_result');
        @endphp
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Import Report</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $result['total_rows'] }}</h3>
                                <small>Total Rows</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $result['inserted'] }}</h3>
                                <small>Inserted</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $result['skipped'] }}</h3>
                                <small>Skipped</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if (!empty($result['skipped_rows']))
                    <h6>Skipped Rows:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Row #</th>
                                    <th>Reason</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['skipped_rows'] as $skipped)
                                    <tr>
                                        <td>{{ $skipped['row'] }}</td>
                                        <td class="text-danger">{{ $skipped['reason'] }}</td>
                                        <td>
                                            <small>
                                                @foreach ($skipped['data'] as $key => $value)
                                                    <strong>{{ $key }}:</strong> {{ $value }}<br>
                                                @endforeach
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
