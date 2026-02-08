@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-edit text-primary me-2"></i>Edit Student</h4>
        <a class="btn btn-outline-secondary" href="{{ route('students.index') }}">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('students.update', $student) }}" id="studentForm">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="border-bottom pb-2 mb-4">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-hashtag text-muted me-1"></i>SL No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-list-ol text-muted"></i></span>
                            <input type="number" class="form-control bg-light @error('sl_no') is-invalid @enderror" 
                                   name="sl_no" id="sl_no" value="{{ old('sl_no', $student->sl_no) }}" readonly>
                        </div>
                        @error('sl_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt text-muted me-1"></i>Admission Date
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar text-muted"></i></span>
                            <input type="text" class="form-control @error('admission_date') is-invalid @enderror" 
                                   name="admission_date" id="admission_date" value="{{ old('admission_date', $student->admission_date ?? '') }}" 
                                   placeholder="dd-mm-yyyy" maxlength="10">
                        </div>
                        @error('admission_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-card text-muted me-1"></i>Admission No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control @error('admission_no') is-invalid @enderror" 
                                   name="admission_no" id="admission_no" value="{{ old('admission_no', $student->admission_no) }}" 
                                   placeholder="Enter Admission No" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('admission_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-graduation-cap text-muted me-1"></i>Class
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-school text-muted"></i></span>
                            <select class="form-select @error('class') is-invalid @enderror" name="class">
                                <option value="">Select Class</option>
                                <option value="Six" {{ old('class', $student->class) == 'Six' ? 'selected' : '' }}>Six</option>
                                <option value="Seven" {{ old('class', $student->class) == 'Seven' ? 'selected' : '' }}>Seven</option>
                                <option value="Eight" {{ old('class', $student->class) == 'Eight' ? 'selected' : '' }}>Eight</option>
                                <option value="Nine" {{ old('class', $student->class) == 'Nine' ? 'selected' : '' }}>Nine</option>
                                <option value="Ten" {{ old('class', $student->class) == 'Ten' ? 'selected' : '' }}>Ten</option>
                                <option value="Eleven" {{ old('class', $student->class) == 'Eleven' ? 'selected' : '' }}>Eleven</option>
                                <option value="Twelve" {{ old('class', $student->class) == 'Twelve' ? 'selected' : '' }}>Twelve</option>
                            </select>
                        </div>
                        @error('class')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-badge text-muted me-1"></i>Student ID
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-fingerprint text-muted"></i></span>
                            <input type="text" class="form-control @error('student_id') is-invalid @enderror" 
                                   name="student_id" id="student_id" value="{{ old('student_id', $student->student_id) }}" 
                                   placeholder="Enter Student ID" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('student_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user text-muted me-1"></i>Student Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-circle text-muted"></i></span>
                            <input type="text" class="form-control @error('student_name') is-invalid @enderror" 
                                   name="student_name" id="student_name" value="{{ old('student_name', $student->student_name) }}" 
                                   placeholder="Enter Student Name" required style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        @error('student_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-file-alt text-muted me-1"></i>PEN Number
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-file-invoice text-muted"></i></span>
                            <input type="text" class="form-control @error('pen_number') is-invalid @enderror" 
                                   name="pen_number" id="pen_number" value="{{ old('pen_number', $student->pen_number) }}" 
                                   placeholder="Enter PEN Number" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('pen_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="border-bottom pb-2 mb-4 mt-4">
                    <h5 class="mb-0">
                        <i class="fas fa-user-friends text-primary me-2"></i>Personal Information
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-card text-muted me-1"></i>Aapaar ID (12 digits)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-address-card text-muted"></i></span>
                            <input type="text" class="form-control @error('aapaar_id') is-invalid @enderror" 
                                   name="aapaar_id" id="aapaar_id" value="{{ old('aapaar_id', $student->aapaar_id) }}" 
                                   maxlength="12" placeholder="Enter 12 digit Aapaar ID" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('aapaar_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-male text-muted me-1"></i>Father Name
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-tie text-muted"></i></span>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror" 
                                   name="father_name" id="father_name" value="{{ old('father_name', $student->father_name) }}" 
                                   placeholder="Enter Father Name" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        @error('father_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-female text-muted me-1"></i>Mother Name
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-female text-muted"></i></span>
                            <input type="text" class="form-control @error('mother_name') is-invalid @enderror" 
                                   name="mother_name" id="mother_name" value="{{ old('mother_name', $student->mother_name) }}" 
                                   placeholder="Enter Mother Name" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        @error('mother_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-users text-muted me-1"></i>Caste
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-layer-group text-muted"></i></span>
                            <select class="form-select @error('caste') is-invalid @enderror" name="caste">
                                <option value="">Select Caste</option>
                                <option value="SC" {{ old('caste', $student->caste) == 'SC' ? 'selected' : '' }}>SC</option>
                                <option value="ST" {{ old('caste', $student->caste) == 'ST' ? 'selected' : '' }}>ST</option>
                                <option value="OBC" {{ old('caste', $student->caste) == 'OBC' ? 'selected' : '' }}>OBC</option>
                                <option value="MOBC" {{ old('caste', $student->caste) == 'MOBC' ? 'selected' : '' }}>MOBC</option>
                                <option value="GEN" {{ old('caste', $student->caste) == 'GEN' ? 'selected' : '' }}>GEN</option>
                            </select>
                        </div>
                        @error('caste')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-birthday-cake text-muted me-1"></i>Date of Birth
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-muted"></i></span>
                            <input type="text" class="form-control @error('dob') is-invalid @enderror" 
                                   name="dob" value="{{ old('dob', $student->dob ?? '') }}" 
                                   placeholder="dd-mm-yyyy" id="dob" maxlength="10">
                        </div>
                        @error('dob')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-clock text-muted me-1"></i>Age (Auto-calculated)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calculator text-muted"></i></span>
                            <input type="text" class="form-control bg-light" id="age" name="age" value="{{ old('age', $student->age) }}" readonly placeholder="Will be calculated from DOB">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-phone text-muted me-1"></i>Mobile No (10 digits)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-mobile-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('mobile_no') is-invalid @enderror" 
                                   name="mobile_no" id="mobile_no" value="{{ old('mobile_no', $student->mobile_no) }}" 
                                   maxlength="10" placeholder="Enter 10 digit mobile" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('mobile_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-ruler-vertical text-muted me-1"></i>Height (in Feet)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-arrows-alt-v text-muted"></i></span>
                            <input type="number" step="0.01" class="form-control @error('height') is-invalid @enderror" 
                                   name="height" id="height" value="{{ old('height', $student->height) }}" 
                                   min="0" max="10" placeholder="Enter height">
                        </div>
                        @error('height')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-weight text-muted me-1"></i>Weight (in Kg)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-balance-scale text-muted"></i></span>
                            <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                   name="weight" id="weight" value="{{ old('weight', $student->weight) }}" 
                                   min="0" max="500" placeholder="Enter weight">
                        </div>
                        @error('weight')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-tint text-muted me-1"></i>Blood Group
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-heartbeat text-muted"></i></span>
                            <select class="form-select @error('blood_group') is-invalid @enderror" name="blood_group">
                                <option value="">Select Blood Group</option>
                                <option value="A+" {{ old('blood_group', $student->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group', $student->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group', $student->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group', $student->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group', $student->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group', $student->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group', $student->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group', $student->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>
                        @error('blood_group')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-code text-muted me-1"></i>Vendor Code
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control @error('vendor_code') is-invalid @enderror" 
                                   name="vendor_code" id="vendor_code" value="{{ old('vendor_code', $student->vendor_code) }}" 
                                   placeholder="Enter Vendor Code" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^0-9A-Za-z]/g, '')">
                        </div>
                        @error('vendor_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Address Information -->
                <div class="border-bottom pb-2 mb-4 mt-4">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>Address Information
                    </h5>
                    <small class="text-muted">Format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode</small>
                </div>
                
                <!-- Combined Address Field -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-map text-muted me-1"></i>Complete Address (Auto-filled from fields below)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-location-arrow text-muted"></i></span>
                            <input type="text" class="form-control bg-light" 
                                   id="address" name="address" 
                                   value="{{ old('address', $student->address) }}" 
                                   placeholder="Village,PO,Sub Div,Panchayat,Dist,State,Pincode" 
                                   readonly style="text-transform: uppercase;">
                        </div>
                        <small class="text-muted">This field is automatically generated from the individual address fields below.</small>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-home text-muted me-1"></i>Village
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                            <input type="text" class="form-control @error('address_village') is-invalid @enderror" 
                                   name="address_village" id="address_village" value="{{ old('address_village', $student->address_village) }}" 
                                   placeholder="Enter Village" oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('address_village')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-envelope text-muted me-1"></i>Post Office
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-mail-bulk text-muted"></i></span>
                            <input type="text" class="form-control @error('post_office') is-invalid @enderror" 
                                   name="post_office" id="post_office" value="{{ old('post_office', $addressParts[1] ?? $student->post_office ?? '') }}" 
                                   placeholder="Enter Post Office" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('post_office')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-map text-muted me-1"></i>Subdiv
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-marked-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('subdiv') is-invalid @enderror" 
                                   name="subdiv" id="subdiv" value="{{ old('subdiv', $addressParts[2] ?? $student->subdiv ?? '') }}" 
                                   placeholder="Enter Subdiv" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('subdiv')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-landmark text-muted me-1"></i>Panchayat
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-university text-muted"></i></span>
                            <input type="text" class="form-control" 
                                   name="panchayat" id="panchayat" value="{{ old('panchayat', $addressParts[3] ?? $student->panchayat ?? '') }}" 
                                   placeholder="Enter Panchayat" style="text-transform: uppercase;"
                                   oninput="updateCombinedAddress();">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-city text-muted me-1"></i>District
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-pin text-muted"></i></span>
                            <input type="text" class="form-control @error('dist') is-invalid @enderror" 
                                   name="dist" id="dist" value="{{ old('dist', $addressParts[4] ?? $student->dist ?? '') }}" 
                                   placeholder="Enter District" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('dist')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-globe text-muted me-1"></i>State
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-flag text-muted"></i></span>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   name="state" id="state" value="{{ old('state', $addressParts[5] ?? $student->state ?? '') }}" 
                                   placeholder="State" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('state')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-mail-bulk text-muted me-1"></i>Pincode
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   name="pincode" id="pincode" value="{{ old('pincode', $addressParts[6] ?? $student->pincode ?? '') }}" 
                                   placeholder="Pincode" maxlength="6" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('pincode')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Dropout Information -->
                <div class="border-bottom pb-2 mb-4 mt-4">
                    <h5 class="mb-0">
                        <i class="fas fa-user-times text-primary me-2"></i>Dropout Information
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-school text-muted me-1"></i>Dropout School
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-university text-muted"></i></span>
                            <input type="text" class="form-control @error('dropout_school') is-invalid @enderror" 
                                   name="dropout_school" id="dropout_school" value="{{ old('dropout_school', $student->dropout_school) }}" 
                                   placeholder="Enter School Name" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                        </div>
                        @error('dropout_school')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-times text-muted me-1"></i>Dropout Date
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar text-muted"></i></span>
                            <input type="text" class="form-control @error('dropout_date') is-invalid @enderror" 
                                   name="dropout_date" id="dropout_date" value="{{ old('dropout_date', $student->dropout_date ?? '') }}" 
                                   placeholder="dd-mm-yyyy" maxlength="10">
                        </div>
                        @error('dropout_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-comment-alt text-muted me-1"></i>Dropout Reason
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light align-items-start pt-3"><i class="fas fa-file-alt text-muted"></i></span>
                            <textarea class="form-control @error('dropout_reason') is-invalid @enderror" 
                                      name="dropout_reason" rows="2" placeholder="Enter reason" style="text-transform: uppercase;">{{ old('dropout_reason', $student->dropout_reason) }}</textarea>
                        </div>
                        @error('dropout_reason')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Bank Account Details -->
                <div class="border-bottom pb-2 mb-4 mt-4">
                    <h5 class="mb-0">
                        <i class="fas fa-university text-primary me-2"></i>Bank Account Details
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-building text-muted me-1"></i>Bank Name
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-landmark text-muted"></i></span>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                   name="bank_name" id="bank_name" value="{{ old('bank_name', $student->bank_name) }}" 
                                   placeholder="Enter Bank Name" oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                        </div>
                        @error('bank_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-code-branch text-muted me-1"></i>Branch Name
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('branch_name') is-invalid @enderror" 
                                   name="branch_name" id="branch_name" value="{{ old('branch_name', $student->branch_name) }}" 
                                   placeholder="Enter Branch Name" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                        </div>
                        @error('branch_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key text-muted me-1"></i>IFSC
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control @error('ifsc') is-invalid @enderror" 
                                   name="ifsc" id="ifsc" value="{{ old('ifsc', $student->ifsc) }}" 
                                   maxlength="11" style="text-transform: uppercase;" placeholder="Enter IFSC"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                        </div>
                        @error('ifsc')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-wallet text-muted me-1"></i>Account No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-credit-card text-muted"></i></span>
                            <input type="text" class="form-control @error('account_no') is-invalid @enderror" 
                                   name="account_no" id="account_no" value="{{ old('account_no', $student->account_no) }}" 
                                   placeholder="Enter Account No" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('account_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Additional Documents -->
                <div class="border-bottom pb-2 mb-4 mt-4">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt text-primary me-2"></i>Additional Documents
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-card text-muted me-1"></i>Aadhaar No (12 digits)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-address-card text-muted"></i></span>
                            <input type="text" class="form-control @error('aadhar_number') is-invalid @enderror" 
                                   name="aadhar_number" id="aadhar_number" value="{{ old('aadhar_number', $student->aadhar_number) }}" 
                                   maxlength="12" placeholder="Enter 12 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('aadhar_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-badge text-muted me-1"></i>Father Aadhaar No (12 digits)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-tie text-muted"></i></span>
                            <input type="text" class="form-control @error('father_aadhar_number') is-invalid @enderror" 
                                   name="father_aadhar_number" id="father_aadhar_number" value="{{ old('father_aadhar_number', $student->father_aadhar_number) }}" 
                                   maxlength="12" placeholder="Enter 12 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('father_aadhar_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-vote-yea text-muted me-1"></i>Father Voter ID No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                            <input type="text" class="form-control @error('father_voter_id_no') is-invalid @enderror" 
                                   name="father_voter_id_no" id="father_voter_id_no" value="{{ old('father_voter_id_no', $student->father_voter_id_no) }}" 
                                   style="text-transform: uppercase;" placeholder="ABC1234567">
                        </div>
                        @error('father_voter_id_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-file-invoice text-muted me-1"></i>Father PAN No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-file-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('father_pan_no') is-invalid @enderror" 
                                   name="father_pan_no" id="father_pan_no" value="{{ old('father_pan_no', $student->father_pan_no) }}" 
                                   style="text-transform: uppercase;" placeholder="ABCDE1234F">
                        </div>
                        @error('father_pan_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button class="btn btn-primary btn-lg px-4" type="submit">
                        <i class="fas fa-save me-2"></i>Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    .input-group-text {
        min-width: 45px;
        justify-content: center;
    }
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .border-bottom {
        border-color: #dee2e6 !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .input-group-text {
        background-color: #f8f9fa !important;
        border-color: #ced4da;
    }
</style>

<script src="{{ asset('js/document-validation.js') }}"></script>
<script>
// Function to update combined address field (must be in global scope for inline oninput)
function updateCombinedAddress() {
    const village = document.getElementById('address_village')?.value.trim() || '';
    const po = document.getElementById('post_office')?.value.trim() || '';
    const subdiv = document.getElementById('subdiv')?.value.trim() || '';
    const panchayat = document.getElementById('panchayat')?.value.trim() || '';
    const dist = document.getElementById('dist')?.value.trim() || '';
    const state = document.getElementById('state')?.value.trim() || '';
    const pincode = document.getElementById('pincode')?.value.trim() || '';
    
    const addressParts = [village, po, subdiv, panchayat, dist, state, pincode].filter(part => part !== '');
    const combinedAddress = addressParts.join(', ');
    
    const addressField = document.getElementById('address');
    if (addressField) {
        addressField.value = combinedAddress;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Date formatting with auto dashes (dd-mm-yyyy)
    function formatDate(input) {
        let value = input.value.replace(/\D/g, ''); // Remove all non-digits
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        input.value = value;
    }
    
    // Apply date formatting to admission_date
    const admissionDateInput = document.getElementById('admission_date');
    if (admissionDateInput) {
        admissionDateInput.addEventListener('input', function() {
            formatDate(this);
        });
    }
    
    // Apply date formatting to dropout_date
    const dropoutDateInput = document.getElementById('dropout_date');
    if (dropoutDateInput) {
        dropoutDateInput.addEventListener('input', function() {
            formatDate(this);
        });
    }
    
    // DOB date formatting and age calculation
    const dobInput = document.getElementById('dob');
    if (dobInput) {
        dobInput.addEventListener('input', function() {
            formatDate(this);
        });
        
        function calculateAge() {
            const dobValue = dobInput.value;
            if (dobValue && dobValue.match(/^\d{2}-\d{2}-\d{4}$/)) {
                const parts = dobValue.split('-');
                const dob = new Date(parts[2], parts[1] - 1, parts[0]);
                const today = new Date();
                
                let years = today.getFullYear() - dob.getFullYear();
                let months = today.getMonth() - dob.getMonth();
                let days = today.getDate() - dob.getDate();
                
                if (days < 0) {
                    months--;
                    days += new Date(today.getFullYear(), today.getMonth(), 0).getDate();
                }
                if (months < 0) {
                    years--;
                    months += 12;
                }
                
                document.getElementById('age').value = years + ' Years, ' + months + ' Months, ' + days + ' Days';
            }
        }
        
        dobInput.addEventListener('change', calculateAge);
        dobInput.addEventListener('blur', calculateAge);
        
        // Calculate age on page load if DOB is already set
        if (dobInput.value) {
            calculateAge();
        }
    }
    
    // Initialize combined address on page load
    updateCombinedAddress();
});
</script>
@endsection
