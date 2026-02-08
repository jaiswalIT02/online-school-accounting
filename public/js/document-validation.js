/**
 * Simple Document Validation Functions
 * Validates PAN, Aadhaar, and Voter ID on input
 */

// PAN Validation: ABCDE1234F (5 letters, 4 digits, 1 letter)
function validatePAN(value) {
    if (!value) return true; // Optional field
    return /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(value);
}

// Aadhaar Validation: 12 digits
function validateAadhaar(value) {
    if (!value) return true; // Optional field
    return /^[0-9]{12}$/.test(value);
}

// Voter ID Validation: ABC1234567 (3 letters, 7 digits)
function validateVoterID(value) {
    if (!value) return true; // Optional field
    return /^[A-Z]{3}[0-9]{7}$/.test(value);
}

// Setup validation for input field
function setupValidation(input, validationType) {
    if (!input) return;
    
    // Set maxlength based on validation type
    if (validationType === 'pan' || validationType === 'voter') {
        input.setAttribute('maxlength', '10');
    } else if (validationType === 'aadhaar') {
        input.setAttribute('maxlength', '12');
    }
    
    // Format and validate on input
    input.addEventListener('input', function() {
        let value = this.value;
        
        if (validationType === 'pan') {
            // PAN: 5 letters, 4 digits, 1 letter
            value = value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 10) value = value.substring(0, 10);
            
            // Format: First 5 letters, next 4 digits, last 1 letter
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i < 5) {
                    if (/[A-Z]/.test(value[i])) formatted += value[i];
                } else if (i < 9) {
                    if (/[0-9]/.test(value[i])) formatted += value[i];
                } else {
                    if (/[A-Z]/.test(value[i])) formatted += value[i];
                }
            }
            this.value = formatted;
        } else if (validationType === 'voter') {
            // Voter ID: 3 letters, 7 digits
            value = value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 10) value = value.substring(0, 10);
            
            // Format: First 3 letters, rest digits
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i < 3) {
                    if (/[A-Z]/.test(value[i])) formatted += value[i];
                } else {
                    if (/[0-9]/.test(value[i])) formatted += value[i];
                }
            }
            this.value = formatted;
        } else if (validationType === 'aadhaar') {
            // Aadhaar: Only numbers
            value = value.replace(/[^0-9]/g, '');
            if (value.length > 12) value = value.substring(0, 12);
            this.value = value;
        }
        
        // Validate
        let isValid = true;
        if (this.value) {
            switch(validationType) {
                case 'pan':
                    isValid = validatePAN(this.value);
                    break;
                case 'aadhaar':
                    isValid = validateAadhaar(this.value);
                    break;
                case 'voter':
                    isValid = validateVoterID(this.value);
                    break;
            }
        }
        
        // Update validation classes (green tick for valid, red border for invalid)
        this.classList.remove('is-valid', 'is-invalid');
        if (this.value) {
            this.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }
    });
    
    // Also validate on blur
    input.addEventListener('blur', function() {
        let isValid = true;
        if (this.value) {
            switch(validationType) {
                case 'pan':
                    isValid = validatePAN(this.value);
                    break;
                case 'aadhaar':
                    isValid = validateAadhaar(this.value);
                    break;
                case 'voter':
                    isValid = validateVoterID(this.value);
                    break;
            }
        }
        
        this.classList.remove('is-valid', 'is-invalid');
        if (this.value) {
            this.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }
    });
}

// Initialize validations when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // PAN fields - be specific to avoid matching "panchayat"
    document.querySelectorAll('input[name="pan_no"], input[name="father_pan_no"], input[name*="panNo"]').forEach(function(input) {
        // Exclude panchayat fields
        if (input.name && !input.name.includes('panchayat')) {
            setupValidation(input, 'pan');
        }
    });
    
    // Aadhaar fields
    document.querySelectorAll('input[name*="aadhaar"], input[name*="aadhar_number"], input[name*="aadhaar_no"], input[name*="father_aadhar_number"], input[name*="aapaar_id"]').forEach(function(input) {
        setupValidation(input, 'aadhaar');
    });
    
    // Voter ID fields
    document.querySelectorAll('input[name*="voter_id"], input[name*="voterId"], input[name*="voter"], input[name*="father_voter_id_no"]').forEach(function(input) {
        setupValidation(input, 'voter');
    });
});
