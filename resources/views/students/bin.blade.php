@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Deleted Students (Bin)</h4>
        <div>
            <a class="btn btn-outline-secondary" href="{{ route('students.index') }}">
                <i class="fas fa-arrow-left"></i> Back to Active Students
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Search Bar -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('students.bin') }}" class="d-flex gap-2">
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       placeholder="Search by student name, ID, admission no, father name, or mobile..." 
                       value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('students.bin', request()->except('search')) }}" class="btn btn-outline-secondary">
                        Clear Search
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('students.bin') }}" id="filterForm">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Admission Date From</label>
                        <input type="text" class="form-control datepicker" name="admission_date_from" 
                               value="{{ request('admission_date_from') }}" placeholder="dd-mm-yyyy" maxlength="10">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Admission Date To</label>
                        <input type="text" class="form-control datepicker" name="admission_date_to" 
                               value="{{ request('admission_date_to') }}" placeholder="dd-mm-yyyy" maxlength="10">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Caste</label>
                        <select class="form-select" name="caste">
                            <option value="">All</option>
                            <option value="SC" {{ request('caste') == 'SC' ? 'selected' : '' }}>SC</option>
                            <option value="ST" {{ request('caste') == 'ST' ? 'selected' : '' }}>ST</option>
                            <option value="OBC" {{ request('caste') == 'OBC' ? 'selected' : '' }}>OBC</option>
                            <option value="MOBC" {{ request('caste') == 'MOBC' ? 'selected' : '' }}>MOBC</option>
                            <option value="GEN" {{ request('caste') == 'GEN' ? 'selected' : '' }}>GEN</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Class</label>
                        <select class="form-select" name="class">
                            <option value="">All</option>
                            <option value="Six" {{ request('class') == 'Six' ? 'selected' : '' }}>Six</option>
                            <option value="Seven" {{ request('class') == 'Seven' ? 'selected' : '' }}>Seven</option>
                            <option value="Eight" {{ request('class') == 'Eight' ? 'selected' : '' }}>Eight</option>
                            <option value="Nine" {{ request('class') == 'Nine' ? 'selected' : '' }}>Nine</option>
                            <option value="Ten" {{ request('class') == 'Ten' ? 'selected' : '' }}>Ten</option>
                            <option value="Eleven" {{ request('class') == 'Eleven' ? 'selected' : '' }}>Eleven</option>
                            <option value="Twelve" {{ request('class') == 'Twelve' ? 'selected' : '' }}>Twelve</option>
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
                </div>
                <div class="row mt-2">
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
                    <div class="col-md-8 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <a href="{{ route('students.bin', request()->only('search')) }}" class="btn btn-outline-secondary me-2">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SL No</th>
                            <th>Admission Date</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Father Name</th>
                            <th>Mobile No</th>
                            <th>Caste</th>
                            <th>Reason</th>
                            <th>Deleted At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>{{ $student->sl_no ?? '-' }}</td>
                                <td>{{ $student->admission_date ?? '-' }}</td>
                                <td>{{ $student->admission_no ?? '-' }}</td>
                                <td>{{ $student->class ?? '-' }}</td>
                                <td>{{ $student->student_id ?? '-' }}</td>
                                <td>{{ $student->student_name }}</td>
                                <td>{{ $student->father_name ?? '-' }}</td>
                                <td>{{ $student->mobile_no ?? '-' }}</td>
                                <td>{{ $student->caste ?? '-' }}</td>
                                <td><small class="text-muted">{{ $student->reason ?? '-' }}</small></td>
                                <td><small>{{ $student->updated_at->format('d-m-Y H:i') }}</small></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('students.show', $student) }}">View</a>
                                    <form class="d-inline" method="POST" action="{{ route('students.restore', $student->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Restore this student?')">
                                            Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No deleted students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $students->appends(request()->query())->links() }}
    </div>
</div>


<script>
function exportExcel() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Build query string manually to ensure all values are included
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (value && value.trim() !== '') {
            params.append(key, value.trim());
        }
    }
    
    window.location.href = '{{ route("students.export.excel") }}?' + params.toString();
}

function exportPdf() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Build query string manually to ensure all values are included
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (value && value.trim() !== '') {
            params.append(key, value.trim());
        }
    }
    
    // Bin page doesn't export - redirect to index export
    window.location.href = '{{ route("students.export.pdf") }}?' + params.toString();
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
            const invalidInputs = [];
            
            // Format and validate all date inputs before submission
            dateInputs.forEach(input => {
                const value = input.value.trim();
                
                // If there's a value, ensure it's properly formatted
                if (value !== '') {
                    // Re-format to ensure proper format
                    formatDate(input);
                    
                    // Validate the formatted value
                    if (!validateDateInput(input)) {
                        isValid = false;
                        invalidInputs.push(input.getAttribute('name') || input.name);
                    } else {
                        // Ensure the value is properly set
                        input.value = input.value.trim();
                    }
                } else {
                    // Empty dates are allowed - clear any invalid class
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please enter valid dates in dd-mm-yyyy format (e.g., 01-01-2023) for: ' + invalidInputs.join(', '));
                return false;
            }
            
            // Ensure all form values are properly set before submission
            dateInputs.forEach(input => {
                // Make sure the input is enabled and has the correct value
                input.disabled = false;
            });
        });
    }
});
</script>
@endsection
