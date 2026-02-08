@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Import Staff</h4>
        <div>
            <a class="btn btn-secondary" href="{{ route('staff.index') }}">Back to Staff</a>
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
            <form method="POST" action="{{ route('staff.processImport') }}" enctype="multipart/form-data">
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
                                <li><code>name</code> or <code>full_name</code> (required)</li>
                                <li><code>roll_no</code> or <code>employee_id</code> (required, must be unique)</li>
                            </ul>
                        </li>
                        <li><strong>All other columns will be automatically mapped:</strong>
                            <ul>
                                <li>Columns matching database field names will be imported</li>
                                <li>Columns with variations (spaces, underscores, case) will be matched automatically</li>
                                <li>Unmatched columns will be ignored (no error)</li>
                                <li>Examples: <code>phone</code>/<code>contact_no</code>, <code>dob</code>/<code>date of birth</code>, <code>designation</code>, <code>date of joining</code>, etc.</li>
                            </ul>
                        </li>
                        <li><strong>Duplicate Handling:</strong> Rows with existing <code>roll_no</code> (employee_id) will be skipped</li>
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
                                    <li><code>full_name</code> (or: name, full name, staff name, employee name)</li>
                                    <li><code>employee_id</code> (or: roll_no, roll no, employee id, emp id, id)</li>
                                </ul>
                                
                                <h6 class="text-primary mt-3">Personal Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>serial_no</code> (or: serial no, serial number, sno)</li>
                                    <li><code>national_id</code> (or: national id)</li>
                                    <li><code>gender</code> (or: sex) - Values: Male, Female</li>
                                    <li><code>date_of_birth</code> (or: dob, date of birth, birth date, birthday)</li>
                                    <li><code>age</code></li>
                                    <li><code>caste</code> (or: category) - Values: General, SC, ST, OBC, MOBC</li>
                                    <li><code>marital_status</code> (or: marital status, marriage status) - Values: Married, Unmarried</li>
                                    <li><code>contact_no</code> (or: phone, phone number, mobile, mobile no, contact, contact no) - 10 digits</li>
                                    <li><code>email</code> (or: email address)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Professional Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>designation</code> (or: post, position, job title)</li>
                                    <li><code>date_of_joining</code> (or: date of joining, joining date, doj)</li>
                                    <li><code>qualification</code> (or: qual, education)</li>
                                    <li><code>professional_qualification</code> (or: professional qualification, prof qualification)</li>
                                    <li><code>stream</code> (or: subject) - Values: Arts, Science, Commerce</li>
                                    <li><code>honors_major_in</code> (or: honors major in, honors, major)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Family Information:</h6>
                                <ul class="list-unstyled">
                                    <li><code>father_name</code> (or: father name, father, fathers name)</li>
                                    <li><code>mother_name</code> (or: mother name, mother, mothers name)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Physical & Health:</h6>
                                <ul class="list-unstyled">
                                    <li><code>blood_group</code> (or: blood group, blood)</li>
                                    <li><code>height</code> (numeric, in feet)</li>
                                    <li><code>weight</code> (numeric, in kg)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Address Fields:</h6>
                                <ul class="list-unstyled">
                                    <li><code>address</code> (or: complete address, full address) - Combined format: "Village,PO,Sub Div,Panchayat,Dist,State,Pincode"</li>
                                    <li><strong>OR use individual fields:</strong></li>
                                    <li><code>village</code> (or: address village, address_village - legacy support)</li>
                                    <li><code>post_office</code> (or: po, post office, address po, address_po - legacy support)</li>
                                    <li><code>sub_div</code> (or: sub div, subdiv, sub division, address sub div, address_sub_div - legacy support)</li>
                                    <li><code>panchayat</code> (or: address panchayat, address_panchayat - legacy support)</li>
                                    <li><code>dist</code> (or: district, address district, address_dist - legacy support)</li>
                                    <li><code>state</code> (or: address state, address_state - legacy support)</li>
                                    <li><code>pincode</code> (or: pin code, pin, address pincode, address_pincode - legacy support)</li>
                                </ul>

                                <h6 class="text-primary mt-3">Bank Details:</h6>
                                <ul class="list-unstyled">
                                    <li><code>bank_name</code> (or: bank name, bank)</li>
                                    <li><code>account_no</code> (or: account no, account number, account)</li>
                                    <li><code>ifsc_code</code> (or: ifsc code, ifsc) - Format: AAAA0AAAA0A</li>
                                </ul>

                                <h6 class="text-primary mt-3">ID Documents:</h6>
                                <ul class="list-unstyled">
                                    <li><code>aadhaar_no</code> (or: aadhaar no, aadhaar number, aadhar no) - 12 digits</li>
                                    <li><code>voter_id</code> (or: voter id, voter id no) - Format: AAA0000000</li>
                                    <li><code>pan_no</code> (or: pan no, pan, pan number) - Format: AAAAA0000A</li>
                                </ul>

                                <h6 class="text-primary mt-3">Other:</h6>
                                <ul class="list-unstyled">
                                    <li><code>component_code</code> (or: component code, component)</li>
                                    <li><code>vendor_code</code> (or: vendor code, vendor)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><strong>Note:</strong> Field names are case-insensitive. You can use spaces, underscores, or no separators. The system will automatically match variations. For example: "Full Name", "full_name", "fullname" all work.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload and Import
                    </button>
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary">Cancel</a>
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
