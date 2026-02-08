<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'serial_no',
        'employee_id',
        'national_id',
        'full_name',
        'gender',
        'date_of_birth',
        'age',
        'caste',
        'marital_status',
        'contact_no',
        'email',
        'designation',
        'date_of_joining',
        'bank_name',
        'account_no',
        'ifsc_code',
        'component_code',
        'vendor_code',
        'aadhaar_no',
        'voter_id',
        'pan_no',
        'qualification',
        'professional_qualification',
        'stream',
        'father_name',
        'mother_name',
        'blood_group',
        'height',
        'weight',
        'village',
        'post_office',
        'sub_div',
        'panchayat',
        'dist',
        'state',
        'pincode',
        'address',
        'honors_major_in',
        'status',
        'reason',
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    /**
     * Convert date_of_birth to dd-mm-yyyy format
     */
    public function getDateOfBirthAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        // If already in dd-mm-yyyy format, return as is
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            return $value;
        }
        
        // If in Y-m-d format, convert to d-m-Y
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('d-m-Y');
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        return $value;
    }

    /**
     * Convert date_of_joining to dd-mm-yyyy format
     */
    public function getDateOfJoiningAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }
        
        // If already in dd-mm-yyyy format, return as is
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
            return $value;
        }
        
        // If in Y-m-d format, convert to d-m-Y
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('d-m-Y');
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        return $value;
    }
}
