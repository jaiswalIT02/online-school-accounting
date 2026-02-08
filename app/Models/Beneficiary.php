<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'name',
        'acode',
        'vendor_code',
        'salary',
        'status',
    ];

    
}
