<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'sl_no',
        'admission_date',
        'admission_no',
        'class',
        'student_id',
        'student_name',
        'pen_number',
        'aapaar_id',
        'father_name',
        'mother_name',
        'caste',
        'dob',
        'age',
        'mobile_no',
        'village',
        'post_office',
        'subdiv',
        'aadhar_number',
        'father_aadhar_number',
        'father_voter_id_no',
        'father_pan_no',
        'panchayat',
        'dist',
        'state',
        'pincode',
        'address',
        'dropout_school',
        'dropout_date',
        'dropout_reason',
        'height',
        'weight',
        'blood_group',
        'vendor_code',
        'bank_name',
        'branch_name',
        'ifsc',
        'account_no',
        'status',
        'reason',
    ];

    protected $casts = [
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    /**
     * Convert admission_date to dd-mm-yyyy format
     */
    public function getAdmissionDateAttribute($value)
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
     * Convert dob to dd-mm-yyyy format
     */
    public function getDobAttribute($value)
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
     * Convert dropout_date to dd-mm-yyyy format
     */
    public function getDropoutDateAttribute($value)
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
