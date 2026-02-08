@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Deleted Staff (Bin)</h4>
        <div>
            <a class="btn btn-outline-secondary" href="{{ route('staff.index') }}">
                <i class="fas fa-arrow-left"></i> Back to Active Staff
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Filters & Search</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('staff.bin') }}" id="filterForm">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" placeholder="Search by name, employee ID, contact, or email">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">All</option>
                            <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Caste</label>
                        <select class="form-select" name="caste">
                            <option value="">All</option>
                            <option value="General" {{ request('caste') == 'General' ? 'selected' : '' }}>General</option>
                            <option value="SC" {{ request('caste') == 'SC' ? 'selected' : '' }}>SC</option>
                            <option value="ST" {{ request('caste') == 'ST' ? 'selected' : '' }}>ST</option>
                            <option value="OBC" {{ request('caste') == 'OBC' ? 'selected' : '' }}>OBC</option>
                            <option value="MOBC" {{ request('caste') == 'MOBC' ? 'selected' : '' }}>MOBC</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Marital Status</label>
                        <select class="form-select" name="marital_status">
                            <option value="">All</option>
                            <option value="Married" {{ request('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Unmarried" {{ request('marital_status') == 'Unmarried' ? 'selected' : '' }}>Unmarried</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Designation</label>
                        <select class="form-select" name="designation">
                            <option value="">All</option>
                            <option value="Warden Cum Superintendent" {{ request('designation') == 'Warden Cum Superintendent' ? 'selected' : '' }}>Warden Cum Superintendent</option>
                            <option value="Head Teacher" {{ request('designation') == 'Head Teacher' ? 'selected' : '' }}>Head Teacher</option>
                            <option value="Full Time Assistant Teacher" {{ request('designation') == 'Full Time Assistant Teacher' ? 'selected' : '' }}>Full Time Assistant Teacher</option>
                            <option value="Part Time Assistant Teacher" {{ request('designation') == 'Part Time Assistant Teacher' ? 'selected' : '' }}>Part Time Assistant Teacher</option>
                            <option value="Account Assistant Cum Caretaker" {{ request('designation') == 'Account Assistant Cum Caretaker' ? 'selected' : '' }}>Account Assistant Cum Caretaker</option>
                            <option value="Peon Cum Matron" {{ request('designation') == 'Peon Cum Matron' ? 'selected' : '' }}>Peon Cum Matron</option>
                            <option value="Chowkidar Cum Mali" {{ request('designation') == 'Chowkidar Cum Mali' ? 'selected' : '' }}>Chowkidar Cum Mali</option>
                            <option value="Head Cook" {{ request('designation') == 'Head Cook' ? 'selected' : '' }}>Head Cook</option>
                            <option value="Assistant Cook Cum Helper" {{ request('designation') == 'Assistant Cook Cum Helper' ? 'selected' : '' }}>Assistant Cook Cum Helper</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Stream</label>
                        <select class="form-select" name="stream">
                            <option value="">All</option>
                            <option value="Arts" {{ request('stream') == 'Arts' ? 'selected' : '' }}>Arts</option>
                            <option value="Science" {{ request('stream') == 'Science' ? 'selected' : '' }}>Science</option>
                            <option value="Commerce" {{ request('stream') == 'Commerce' ? 'selected' : '' }}>Commerce</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Qualification</label>
                        <select class="form-select" name="qualification">
                            <option value="">All</option>
                            <option value="Below HSLC" {{ request('qualification') == 'Below HSLC' ? 'selected' : '' }}>Below HSLC</option>
                            <option value="HSLC" {{ request('qualification') == 'HSLC' ? 'selected' : '' }}>HSLC</option>
                            <option value="HS" {{ request('qualification') == 'HS' ? 'selected' : '' }}>HS</option>
                            <option value="BA" {{ request('qualification') == 'BA' ? 'selected' : '' }}>BA</option>
                            <option value="Bsc" {{ request('qualification') == 'Bsc' ? 'selected' : '' }}>Bsc</option>
                            <option value="B.Com" {{ request('qualification') == 'B.Com' ? 'selected' : '' }}>B.Com</option>
                            <option value="MA" {{ request('qualification') == 'MA' ? 'selected' : '' }}>MA</option>
                            <option value="Msc" {{ request('qualification') == 'Msc' ? 'selected' : '' }}>Msc</option>
                            <option value="M.Com" {{ request('qualification') == 'M.Com' ? 'selected' : '' }}>M.Com</option>
                            <option value="Manual Enter" {{ request('qualification') == 'Manual Enter' ? 'selected' : '' }}>Manual Enter</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Blood Group</label>
                        <select class="form-select" name="blood_group">
                            <option value="">All</option>
                            <option value="A+" {{ request('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ request('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ request('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ request('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ request('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ request('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ request('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ request('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Age From (Years)</label>
                        <input type="number" class="form-control" name="age_from" 
                               value="{{ request('age_from') }}" min="0">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Age To (Years)</label>
                        <input type="number" class="form-control" name="age_to" 
                               value="{{ request('age_to') }}" min="0">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Date of Joining From</label>
                        <input type="text" class="form-control datepicker" name="date_of_joining_from" 
                               value="{{ request('date_of_joining_from') }}" placeholder="dd-mm-yyyy" maxlength="10">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Date of Joining To</label>
                        <input type="text" class="form-control datepicker" name="date_of_joining_to" 
                               value="{{ request('date_of_joining_to') }}" placeholder="dd-mm-yyyy" maxlength="10">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12 d-flex">
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <a href="{{ route('staff.bin', request()->only('search')) }}" class="btn btn-outline-secondary me-2">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Serial No</th>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Designation</th>
                            <th>Contact No</th>
                            <th>Email</th>
                            <th>Date of Joining</th>
                            <th>Reason</th>
                            <th>Deleted At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staff as $s)
                            <tr>
                                <td>{{ $s->serial_no ?? '-' }}</td>
                                <td>{{ $s->employee_id ?? '-' }}</td>
                                <td>{{ $s->full_name }}</td>
                                <td>{{ $s->gender ?? '-' }}</td>
                                <td>{{ $s->designation ?? '-' }}</td>
                                <td>{{ $s->contact_no ?? '-' }}</td>
                                <td>{{ $s->email ?? '-' }}</td>
                                <td>{{ $s->date_of_joining ?? '-' }}</td>
                                <td><small class="text-muted">{{ $s->reason ?? '-' }}</small></td>
                                <td><small>{{ $s->updated_at->format('d-m-Y H:i') }}</small></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('staff.show', $s) }}">View</a>
                                    <form class="d-inline" method="POST" action="{{ route('staff.restore', $s->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Restore this staff member?')">
                                            Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">No deleted staff found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $staff->appends(request()->query())->links() }}
    </div>
</div>


<script>
function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("staff.export.excel") }}?' + params.toString();
}

function exportPdf() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    window.location.href = '{{ route("staff.export.pdf") }}?' + params.toString();
}

// Date formatting with auto dashes (dd-mm-yyyy) and validation
function formatDate(input) {
    let value = input.value.replace(/\D/g, ''); // Remove all non-digits
    
    // Only format if we have digits
    if (value.length > 0) {
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        input.value = value;
        
        // Validate date format
        validateDateInput(input);
    } else {
        // Clear invalid input
        input.value = '';
        input.classList.remove('is-invalid');
    }
}

// Validate date input format (dd-mm-yyyy)
function validateDateInput(input) {
    const value = input.value.trim();
    const datePattern = /^(\d{2})-(\d{2})-(\d{4})$/;
    
    if (value === '') {
        input.classList.remove('is-invalid');
        return true;
    }
    
    if (!datePattern.test(value)) {
        input.classList.add('is-invalid');
        return false;
    }
    
    // Check if date is valid
    const parts = value.split('-');
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    // Basic validation
    if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > 2100) {
        input.classList.add('is-invalid');
        return false;
    }
    
    // Check if date is actually valid (e.g., not 32-13-2023)
    const date = new Date(year, month - 1, day);
    if (date.getDate() !== day || date.getMonth() !== month - 1 || date.getFullYear() !== year) {
        input.classList.add('is-invalid');
        return false;
    }
    
    input.classList.remove('is-invalid');
    return true;
}

// Apply date formatting to all datepicker inputs
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('.datepicker');
    dateInputs.forEach(input => {
        // Add input event listener for auto-formatting
        input.addEventListener('input', function() {
            formatDate(this);
        });
        
        // Validate on blur
        input.addEventListener('blur', function() {
            validateDateInput(this);
        });
        
        // Validate existing value on load
        if (input.value) {
            validateDateInput(input);
        }
        
        // Set maxlength to limit input
        input.setAttribute('maxlength', '10');
        
        // Set placeholder
        if (!input.value) {
            input.placeholder = 'dd-mm-yyyy';
        }
    });
    
    // Form validation before submit
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            let isValid = true;
            dateInputs.forEach(input => {
                if (!validateDateInput(input) && input.value.trim() !== '') {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please enter valid dates in dd-mm-yyyy format (e.g., 01-01-2023)');
                return false;
            }
        });
    }
});
</script>
@endsection
