@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-edit text-primary me-2"></i>Edit Staff</h4>
        <a class="btn btn-outline-secondary" href="{{ route('staff.index') }}">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('staff.update', $staff) }}" id="staffForm">
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
                            <i class="fas fa-hashtag text-muted me-1"></i>Serial No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-list-ol text-muted"></i></span>
                            <input type="number" class="form-control bg-light @error('serial_no') is-invalid @enderror" 
                                   name="serial_no" id="serial_no" value="{{ old('serial_no', $staff->serial_no) }}" readonly>
                        </div>
                        @error('serial_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-badge text-muted me-1"></i>Employee ID
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-fingerprint text-muted"></i></span>
                            <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                   name="employee_id" id="employee_id" value="{{ old('employee_id', $staff->employee_id) }}" 
                                   placeholder="Enter Employee ID" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('employee_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-card text-muted me-1"></i>National ID
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-address-card text-muted"></i></span>
                            <input type="text" class="form-control @error('national_id') is-invalid @enderror" 
                                   name="national_id" id="national_id" value="{{ old('national_id', $staff->national_id) }}" 
                                   placeholder="Enter National ID" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^0-9A-Za-z]/g, '')">
                        </div>
                        @error('national_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user text-muted me-1"></i>Full Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-circle text-muted"></i></span>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   name="full_name" id="full_name" value="{{ old('full_name', $staff->full_name) }}" 
                                   placeholder="Enter Full Name" required style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        @error('full_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-venus-mars text-muted me-1"></i>Gender
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-friends text-muted"></i></span>
                            <select class="form-select @error('gender') is-invalid @enderror" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $staff->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $staff->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        @error('gender')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-birthday-cake text-muted me-1"></i>Date of Birth
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-muted"></i></span>
                            <input type="text" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   name="date_of_birth" value="{{ old('date_of_birth', $staff->date_of_birth ?? '') }}" 
                                   placeholder="dd-mm-yyyy" id="date_of_birth" maxlength="10">
                        </div>
                        @error('date_of_birth')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-clock text-muted me-1"></i>Age (Auto-calculated)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calculator text-muted"></i></span>
                            <input type="text" class="form-control bg-light" id="age" name="age" value="{{ old('age', $staff->age) }}" readonly placeholder="Will be calculated from DOB">
                        </div>
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
                                <option value="General" {{ old('caste', $staff->caste) == 'General' ? 'selected' : '' }}>General</option>
                                <option value="SC" {{ old('caste', $staff->caste) == 'SC' ? 'selected' : '' }}>SC</option>
                                <option value="ST" {{ old('caste', $staff->caste) == 'ST' ? 'selected' : '' }}>ST</option>
                                <option value="OBC" {{ old('caste', $staff->caste) == 'OBC' ? 'selected' : '' }}>OBC</option>
                                <option value="MOBC" {{ old('caste', $staff->caste) == 'MOBC' ? 'selected' : '' }}>MOBC</option>
                            </select>
                        </div>
                        @error('caste')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-heart text-muted me-1"></i>Marital Status
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-check text-muted"></i></span>
                            <select class="form-select @error('marital_status') is-invalid @enderror" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="Married" {{ old('marital_status', $staff->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Unmarried" {{ old('marital_status', $staff->marital_status) == 'Unmarried' ? 'selected' : '' }}>Unmarried</option>
                            </select>
                        </div>
                        @error('marital_status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-phone text-muted me-1"></i>Contact No (10 digits)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-mobile-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('contact_no') is-invalid @enderror" 
                                   name="contact_no" id="contact_no" value="{{ old('contact_no', $staff->contact_no) }}" 
                                   maxlength="10" placeholder="Enter 10 digit contact" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('contact_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-envelope text-muted me-1"></i>Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-at text-muted"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   name="email" id="email" value="{{ old('email', $staff->email) }}" 
                                   placeholder="Enter Email" style="text-transform: uppercase;">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="border-bottom pb-2 mb-4 mt-4">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase text-primary me-2"></i>Professional Information
                    </h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user-tie text-muted me-1"></i>Designation
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-briefcase text-muted"></i></span>
                            <select class="form-select @error('designation') is-invalid @enderror" name="designation">
                                <option value="">Select Designation</option>
                                <option value="Warden Cum Superintendent" {{ old('designation', $staff->designation) == 'Warden Cum Superintendent' ? 'selected' : '' }}>Warden Cum Superintendent</option>
                                <option value="Head Teacher" {{ old('designation', $staff->designation) == 'Head Teacher' ? 'selected' : '' }}>Head Teacher</option>
                                <option value="Full Time Assistant Teacher" {{ old('designation', $staff->designation) == 'Full Time Assistant Teacher' ? 'selected' : '' }}>Full Time Assistant Teacher</option>
                                <option value="Part Time Assistant Teacher" {{ old('designation', $staff->designation) == 'Part Time Assistant Teacher' ? 'selected' : '' }}>Part Time Assistant Teacher</option>
                                <option value="Account Assistant Cum Caretaker" {{ old('designation', $staff->designation) == 'Account Assistant Cum Caretaker' ? 'selected' : '' }}>Account Assistant Cum Caretaker</option>
                                <option value="Peon Cum Matron" {{ old('designation', $staff->designation) == 'Peon Cum Matron' ? 'selected' : '' }}>Peon Cum Matron</option>
                                <option value="Chowkidar Cum Mali" {{ old('designation', $staff->designation) == 'Chowkidar Cum Mali' ? 'selected' : '' }}>Chowkidar Cum Mali</option>
                                <option value="Head Cook" {{ old('designation', $staff->designation) == 'Head Cook' ? 'selected' : '' }}>Head Cook</option>
                                <option value="Assistant Cook Cum Helper" {{ old('designation', $staff->designation) == 'Assistant Cook Cum Helper' ? 'selected' : '' }}>Assistant Cook Cum Helper</option>
                            </select>
                        </div>
                        @error('designation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-calendar-alt text-muted me-1"></i>Date of Joining
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-calendar text-muted"></i></span>
                            <input type="text" class="form-control @error('date_of_joining') is-invalid @enderror" 
                                   name="date_of_joining" id="date_of_joining" value="{{ old('date_of_joining', $staff->date_of_joining ?? '') }}" 
                                   placeholder="dd-mm-yyyy" maxlength="10">
                        </div>
                        @error('date_of_joining')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-graduation-cap text-muted me-1"></i>Qualification
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-certificate text-muted"></i></span>
                            <select class="form-select @error('qualification') is-invalid @enderror" name="qualification">
                                <option value="">Select Qualification</option>
                                <option value="Below HSLC" {{ old('qualification', $staff->qualification) == 'Below HSLC' ? 'selected' : '' }}>Below HSLC</option>
                                <option value="HSLC" {{ old('qualification', $staff->qualification) == 'HSLC' ? 'selected' : '' }}>HSLC</option>
                                <option value="HS" {{ old('qualification', $staff->qualification) == 'HS' ? 'selected' : '' }}>HS</option>
                                <option value="BA" {{ old('qualification', $staff->qualification) == 'BA' ? 'selected' : '' }}>BA</option>
                                <option value="Bsc" {{ old('qualification', $staff->qualification) == 'Bsc' ? 'selected' : '' }}>Bsc</option>
                                <option value="B.Com" {{ old('qualification', $staff->qualification) == 'B.Com' ? 'selected' : '' }}>B.Com</option>
                                <option value="MA" {{ old('qualification', $staff->qualification) == 'MA' ? 'selected' : '' }}>MA</option>
                                <option value="Msc" {{ old('qualification', $staff->qualification) == 'Msc' ? 'selected' : '' }}>Msc</option>
                                <option value="M.Com" {{ old('qualification', $staff->qualification) == 'M.Com' ? 'selected' : '' }}>M.Com</option>
                                <option value="Manual Enter" {{ old('qualification', $staff->qualification) == 'Manual Enter' ? 'selected' : '' }}>Manual Enter</option>
                            </select>
                        </div>
                        @error('qualification')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-award text-muted me-1"></i>Professional Qualification
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-medal text-muted"></i></span>
                            <select class="form-select @error('professional_qualification') is-invalid @enderror" name="professional_qualification">
                                <option value="">Select Professional Qualification</option>
                                <option value="D.El.Ed" {{ old('professional_qualification', $staff->professional_qualification) == 'D.El.Ed' ? 'selected' : '' }}>D.El.Ed</option>
                                <option value="B.Ed" {{ old('professional_qualification', $staff->professional_qualification) == 'B.Ed' ? 'selected' : '' }}>B.Ed</option>
                                <option value="TET" {{ old('professional_qualification', $staff->professional_qualification) == 'TET' ? 'selected' : '' }}>TET</option>
                                <option value="CTET" {{ old('professional_qualification', $staff->professional_qualification) == 'CTET' ? 'selected' : '' }}>CTET</option>
                            </select>
                        </div>
                        @error('professional_qualification')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-book text-muted me-1"></i>Stream
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-stream text-muted"></i></span>
                            <select class="form-select @error('stream') is-invalid @enderror" name="stream">
                                <option value="">Select Stream</option>
                                <option value="Arts" {{ old('stream', $staff->stream) == 'Arts' ? 'selected' : '' }}>Arts</option>
                                <option value="Science" {{ old('stream', $staff->stream) == 'Science' ? 'selected' : '' }}>Science</option>
                                <option value="Commerce" {{ old('stream', $staff->stream) == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                            </select>
                        </div>
                        @error('stream')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-star text-muted me-1"></i>Honors Major In
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-trophy text-muted"></i></span>
                            <input type="text" class="form-control @error('honors_major_in') is-invalid @enderror" 
                                   name="honors_major_in" id="honors_major_in" value="{{ old('honors_major_in', $staff->honors_major_in) }}" 
                                   placeholder="Enter Honors Major" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                        </div>
                        @error('honors_major_in')
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
                            <i class="fas fa-male text-muted me-1"></i>Father Name
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user-tie text-muted"></i></span>
                            <input type="text" class="form-control @error('father_name') is-invalid @enderror" 
                                   name="father_name" id="father_name" value="{{ old('father_name', $staff->father_name) }}" 
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
                                   name="mother_name" id="mother_name" value="{{ old('mother_name', $staff->mother_name) }}" 
                                   placeholder="Enter Mother Name" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        @error('mother_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-tint text-muted me-1"></i>Blood Group
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-heartbeat text-muted"></i></span>
                            <select class="form-select @error('blood_group') is-invalid @enderror" name="blood_group">
                                <option value="">Select Blood Group</option>
                                <option value="A+" {{ old('blood_group', $staff->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group', $staff->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group', $staff->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group', $staff->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group', $staff->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group', $staff->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group', $staff->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group', $staff->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>
                        @error('blood_group')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-ruler-vertical text-muted me-1"></i>Height (in Feet)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-arrows-alt-v text-muted"></i></span>
                            <input type="number" step="0.01" class="form-control @error('height') is-invalid @enderror" 
                                   name="height" id="height" value="{{ old('height', $staff->height) }}" 
                                   min="0" max="10" placeholder="Enter height">
                        </div>
                        @error('height')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-weight text-muted me-1"></i>Weight (in Kg)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-balance-scale text-muted"></i></span>
                            <input type="number" step="0.01" class="form-control @error('weight') is-invalid @enderror" 
                                   name="weight" id="weight" value="{{ old('weight', $staff->weight) }}" 
                                   min="0" max="500" placeholder="Enter weight">
                        </div>
                        @error('weight')
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
                                   value="{{ old('address', $staff->address) }}" 
                                   placeholder="Village,PO,Sub Div,Panchayat,Dist,State,Pincode" 
                                   readonly style="text-transform: uppercase;">
                        </div>
                        <small class="text-muted">This field is automatically generated from the individual address fields below.</small>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-home text-muted me-1"></i>Village
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                            <input type="text" class="form-control @error('village') is-invalid @enderror" 
                                   name="village" id="village" value="{{ old('village', $addressParts[0] ?? $staff->village ?? '') }}" 
                                   placeholder="Enter Village" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('village')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-envelope text-muted me-1"></i>Post Office
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-mail-bulk text-muted"></i></span>
                            <input type="text" class="form-control @error('post_office') is-invalid @enderror" 
                                   name="post_office" id="post_office" value="{{ old('post_office', $addressParts[1] ?? $staff->post_office ?? '') }}" 
                                   placeholder="Enter Post Office" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('post_office')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-map text-muted me-1"></i>Sub Div
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-marked-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('sub_div') is-invalid @enderror" 
                                   name="sub_div" id="sub_div" value="{{ old('sub_div', $addressParts[2] ?? $staff->sub_div ?? '') }}" 
                                   placeholder="Enter Sub Div" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('sub_div')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-landmark text-muted me-1"></i>Panchayat
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-university text-muted"></i></span>
                            <input type="text" class="form-control" 
                                   name="address_panchayat" id="address_panchayat" value="{{ old('address_panchayat', $addressParts[3] ?? $staff->address_panchayat ?? '') }}" 
                                   placeholder="Enter Panchayat" style="text-transform: uppercase;"
                                   oninput="updateCombinedAddress();">
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-city text-muted me-1"></i>Dist
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-map-pin text-muted"></i></span>
                            <input type="text" class="form-control @error('dist') is-invalid @enderror" 
                                   name="dist" id="dist" value="{{ old('dist', $addressParts[4] ?? $staff->dist ?? '') }}" 
                                   placeholder="Enter District" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('dist')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-globe text-muted me-1"></i>State
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-flag text-muted"></i></span>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   name="state" id="state" value="{{ old('state', $addressParts[5] ?? $staff->state ?? '') }}" 
                                   placeholder="Enter State" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('state')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-mail-bulk text-muted me-1"></i>Pincode
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   name="pincode" id="pincode" value="{{ old('pincode', $addressParts[6] ?? $staff->pincode ?? '') }}" 
                                   placeholder="Enter Pincode" maxlength="6" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); updateCombinedAddress();">
                        </div>
                        @error('pincode')
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
                                   name="bank_name" id="bank_name" value="{{ old('bank_name', $staff->bank_name) }}" 
                                   placeholder="Enter Bank Name" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, '')">
                        </div>
                        @error('bank_name')
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
                                   name="account_no" id="account_no" value="{{ old('account_no', $staff->account_no) }}" 
                                   placeholder="Enter Account No" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('account_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key text-muted me-1"></i>IFSC Code
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control @error('ifsc_code') is-invalid @enderror" 
                                   name="ifsc_code" id="ifsc_code" value="{{ old('ifsc_code', $staff->ifsc_code) }}" 
                                   maxlength="11" style="text-transform: uppercase;" placeholder="Enter IFSC">
                        </div>
                        @error('ifsc_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-code text-muted me-1"></i>Component Code
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control @error('component_code') is-invalid @enderror" 
                                   name="component_code" id="component_code" value="{{ old('component_code', $staff->component_code) }}" 
                                   placeholder="Enter Component Code" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^0-9A-Za-z]/g, '')">
                        </div>
                        @error('component_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-code text-muted me-1"></i>Vendor Code
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-barcode text-muted"></i></span>
                            <input type="text" class="form-control @error('vendor_code') is-invalid @enderror" 
                                   name="vendor_code" id="vendor_code" value="{{ old('vendor_code', $staff->vendor_code) }}" 
                                   placeholder="Enter Vendor Code" style="text-transform: uppercase;" 
                                   oninput="this.value = this.value.replace(/[^0-9A-Za-z]/g, '')">
                        </div>
                        @error('vendor_code')
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-id-card text-muted me-1"></i>Aadhaar No (12 digits)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-address-card text-muted"></i></span>
                            <input type="text" class="form-control @error('aadhaar_no') is-invalid @enderror" 
                                   name="aadhaar_no" id="aadhaar_no" value="{{ old('aadhaar_no', $staff->aadhaar_no) }}" 
                                   maxlength="12" placeholder="Enter 12 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        @error('aadhaar_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-vote-yea text-muted me-1"></i>Voter ID
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                            <input type="text" class="form-control @error('voter_id') is-invalid @enderror" 
                                   name="voter_id" id="voter_id" value="{{ old('voter_id', $staff->voter_id) }}" 
                                   style="text-transform: uppercase;" placeholder="ABC1234567">
                        </div>
                        @error('voter_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-file-invoice text-muted me-1"></i>PAN No
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-file-alt text-muted"></i></span>
                            <input type="text" class="form-control @error('pan_no') is-invalid @enderror" 
                                   name="pan_no" id="pan_no" value="{{ old('pan_no', $staff->pan_no) }}" 
                                   style="text-transform: uppercase;" placeholder="ABCDE1234F">
                        </div>
                        @error('pan_no')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button class="btn btn-primary btn-lg px-4" type="submit">
                        <i class="fas fa-save me-2"></i>Update Staff
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
    
    // Apply date formatting to date_of_joining
    const dateOfJoiningInput = document.getElementById('date_of_joining');
    if (dateOfJoiningInput) {
        dateOfJoiningInput.addEventListener('input', function() {
            formatDate(this);
        });
    }
    
    // DOB date formatting and age calculation
    const dobInput = document.getElementById('date_of_birth');
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
    
    // Function to update combined address field
    function updateCombinedAddress() {
        const village = document.getElementById('village')?.value.trim() || '';
        const po = document.getElementById('post_office')?.value.trim() || '';
        const subDiv = document.getElementById('sub_div')?.value.trim() || '';
        const panchayat = document.getElementById('panchayat')?.value.trim() || '';
        const dist = document.getElementById('dist')?.value.trim() || '';
        const state = document.getElementById('state')?.value.trim() || '';
        const pincode = document.getElementById('pincode')?.value.trim() || '';
        
        const addressParts = [village, po, subDiv, panchayat, dist, state, pincode].filter(part => part !== '');
        const combinedAddress = addressParts.join(', ');
        
        const addressField = document.getElementById('address');
        if (addressField) {
            addressField.value = combinedAddress;
        }
    }
    
    // Initialize combined address on page load
    updateCombinedAddress();
});
</script>
@endsection
